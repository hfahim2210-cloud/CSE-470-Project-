<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\HireRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Support\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HireRequestController extends Controller
{
    /**
     * Feature 1: show the buyer's hire-request form.
     */
    public function create(Gig $gig): View
    {
        abort_if($gig->status === 'archived', 404, 'This gig is not available.');

        $buyer = CurrentUser::buyerFor($gig);

        abort_if(
            (int) $buyer->id === (int) $gig->user_id,
            403,
            'You cannot submit a hire request for your own gig.'
        );

        $gig->load(['user', 'tiers', 'addons']);

        return view('hire_requests.create', compact('gig', 'buyer'));
    }

    /**
     * Feature 1: validate and save a pending hire request.
     */
    public function store(Request $request, Gig $gig): RedirectResponse
    {
        abort_if($gig->status === 'archived', 404, 'This gig is not available.');

        $buyer = CurrentUser::buyerFor($gig);

        abort_if(
            (int) $buyer->id === (int) $gig->user_id,
            403,
            'You cannot submit a hire request for your own gig.'
        );

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:1000'],
            'proposed_deadline' => ['required', 'date', 'after_or_equal:today'],
            'tier_id' => ['nullable', 'integer'],
            'addon_ids' => ['nullable', 'array', 'max:10'],
            'addon_ids.*' => ['integer', 'distinct'],
        ]);

        $gig->load(['tiers', 'addons']);
        $tier = null;

        if (! empty($validated['tier_id'])) {
            $tier = $gig->tiers->firstWhere('id', (int) $validated['tier_id']);

            if (! $tier) {
                throw ValidationException::withMessages([
                    'tier_id' => 'The selected service package is not available for this gig.',
                ]);
            }
        } elseif ($gig->tiers->isNotEmpty()) {
            throw ValidationException::withMessages([
                'tier_id' => 'Please select a service package.',
            ]);
        }

        $addonIds = collect($validated['addon_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();
        $addons = $gig->addons->whereIn('id', $addonIds)->values();

        if ($addons->count() !== $addonIds->count()) {
            throw ValidationException::withMessages([
                'addon_ids' => 'One or more selected add-ons are not available for this gig.',
            ]);
        }

        $tierSnapshot = $tier ? [
            'id' => $tier->id,
            'name' => $tier->name,
            'title' => $tier->title,
            'description' => $tier->description,
            'price' => (float) $tier->price,
            'delivery_time' => $tier->delivery_time,
            'revisions' => $tier->revisions,
        ] : null;
        $addonSnapshots = $addons->map(fn ($addon): array => [
            'id' => $addon->id,
            'name' => $addon->name,
            'description' => $addon->description,
            'price' => (float) $addon->price,
            'extra_days' => $addon->extra_days,
        ])->all();
        $quotedPrice = (float) ($tier?->price ?? $gig->price)
            + (float) $addons->sum('price');

        $alreadyPending = HireRequest::query()
            ->where('gig_id', $gig->id)
            ->where('buyer_id', $buyer->id)
            ->where('status', HireRequest::STATUS_PENDING)
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withErrors([
                    'message' => 'You already have a pending hire request for this gig.',
                ])
                ->withInput();
        }

        HireRequest::query()->create([
            'gig_id' => $gig->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $gig->user_id,
            'message' => $validated['message'],
            'proposed_deadline' => $validated['proposed_deadline'],
            'selected_tier' => $tierSnapshot,
            'selected_addons' => $addonSnapshots,
            'quoted_price' => $quotedPrice,
            'status' => HireRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('gigs.show', $gig)
            ->with('success', 'Hire request submitted successfully.');
    }

    /**
     * Feature 3: show the seller all incoming hire requests for their gigs.
     */
    public function incoming(): View
    {
        $seller = CurrentUser::seller();

        $hireRequests = HireRequest::query()
            ->with(['gig', 'buyer', 'order'])
            ->where('seller_id', $seller->id)
            ->orderByRaw(
                'CASE WHEN status = ? THEN 0 ELSE 1 END',
                [HireRequest::STATUS_PENDING]
            )
            ->latest()
            ->get();

        return view('hire_requests.incoming', compact('hireRequests', 'seller'));
    }

    /**
     * Feature 2: accept a pending request and create exactly one order.
     */
    public function accept(HireRequest $hireRequest): RedirectResponse
    {
        $seller = CurrentUser::seller();

        abort_unless(
            (int) $hireRequest->seller_id === (int) $seller->id,
            403,
            'You cannot accept another seller\'s hire request.'
        );

        $order = DB::transaction(function () use ($hireRequest, $seller): Order {
            $lockedRequest = HireRequest::query()
                ->with('gig')
                ->lockForUpdate()
                ->findOrFail($hireRequest->id);

            $existingOrder = Order::query()
                ->where('hire_request_id', $lockedRequest->id)
                ->first();

            if ($existingOrder) {
                return $existingOrder;
            }

            abort_unless(
                $lockedRequest->status === HireRequest::STATUS_PENDING,
                422,
                'Only pending hire requests can be accepted.'
            );

            $order = new Order();
            $order->hire_request_id = $lockedRequest->id;
            $order->gig_id = $lockedRequest->gig_id;
            $order->seller_id = $lockedRequest->seller_id;
            $order->buyer_id = $lockedRequest->buyer_id;
            $order->agreed_price = $lockedRequest->quoted_price ?? $lockedRequest->gig->price;
            $order->selected_tier = $lockedRequest->selected_tier;
            $order->selected_addons = $lockedRequest->selected_addons;
            $order->status = 'not_started';
            $order->due_date = $lockedRequest->proposed_deadline
                ?? now()->addDays($lockedRequest->gig->delivery_time);
            $order->save();

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'previous_status' => null,
                'new_status' => 'not_started',
                'changed_by_user_id' => $seller->id,
                'note' => 'Order created from accepted hire request.',
                'changed_at' => now(),
            ]);

            $lockedRequest->update([
                'status' => HireRequest::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            return $order;
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Hire request accepted and order created successfully.');
    }

    /**
     * Feature 4: decline a pending request with an optional reason.
     */
    public function decline(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        $seller = CurrentUser::seller();

        abort_unless(
            (int) $hireRequest->seller_id === (int) $seller->id,
            403,
            'You cannot decline another seller\'s hire request.'
        );

        $validated = $request->validate([
            'decline_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($hireRequest, $validated): void {
            $lockedRequest = HireRequest::query()
                ->lockForUpdate()
                ->findOrFail($hireRequest->id);

            abort_unless(
                $lockedRequest->status === HireRequest::STATUS_PENDING,
                422,
                'Only pending hire requests can be declined.'
            );

            abort_if(
                Order::query()->where('hire_request_id', $lockedRequest->id)->exists(),
                422,
                'A request that already created an order cannot be declined.'
            );

            $lockedRequest->update([
                'status' => 'declined',
            ]);

            $lockedRequest->decline_reason = $validated['decline_reason'] ?? null;
            $lockedRequest->declined_at = now();
            $lockedRequest->accepted_at = null;
            $lockedRequest->save();
        });

        return redirect()
            ->route('hire-requests.incoming')
            ->with('success', 'Hire request declined successfully.');
    }
}
