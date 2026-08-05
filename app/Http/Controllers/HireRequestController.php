<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\HireRequest;
use App\Models\Order;
use App\Support\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $gig->load('user');

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
        ]);

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
            'status' => HireRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('gigs.show', $gig)
            ->with('success', 'Hire request submitted successfully.');
    }

    /**
     * Small dependency page required for a seller to trigger Feature 2.
     */
    public function incoming(): View
    {
        $seller = CurrentUser::seller();

        $hireRequests = HireRequest::query()
            ->with(['gig', 'buyer', 'order'])
            ->where('seller_id', $seller->id)
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

        $order = DB::transaction(function () use ($hireRequest): Order {
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
            $order->agreed_price = $lockedRequest->gig->price;
            $order->status = 'in_progress';
            $order->due_date = $lockedRequest->proposed_deadline
                ?? now()->addDays($lockedRequest->gig->delivery_time);
            $order->save();

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
}
