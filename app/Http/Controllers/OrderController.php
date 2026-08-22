<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Support\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['gig', 'seller', 'buyer', 'deliverable'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['gig', 'seller', 'buyer', 'deliverable', 'review', 'rating', 'revisionRequests']);

        return view('orders.show', compact('order'));
    }

    /**
     * Feature 8 (Module 3): show the seller the supported next status for an active order.
     */
    public function status(Order $order): View
    {
        $seller = CurrentUser::seller();

        abort_unless(
            (int) $order->seller_id === (int) $seller->id,
            403,
            'Only the seller assigned to this order can update its status.'
        );

        $order->load(['gig', 'buyer', 'seller']);
        $nextStatuses = $this->sellerAllowedNextStatuses($order);
        $statusHistory = OrderStatusHistory::query()
            ->with('changedBy')
            ->where('order_id', $order->id)
            ->latest('changed_at')
            ->get();

        return view('orders.status', compact('order', 'nextStatuses', 'statusHistory'));
    }

    /**
     * Feature 8 (Module 3): update an active order through valid seller-controlled states.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $seller = CurrentUser::seller();

        abort_unless(
            (int) $order->seller_id === (int) $seller->id,
            403,
            'Only the seller assigned to this order can update its status.'
        );

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! in_array($validated['status'], $this->sellerAllowedNextStatuses($order), true)) {
            throw ValidationException::withMessages([
                'status' => 'That status change is not allowed from the current order state.',
            ]);
        }

        DB::transaction(function () use ($order, $validated, $seller): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($validated['status'], $this->sellerAllowedNextStatuses($lockedOrder), true)) {
                throw ValidationException::withMessages([
                    'status' => 'The order status changed before your request was processed. Please try again.',
                ]);
            }

            $previousStatus = $lockedOrder->status;
            $lockedOrder->status = $validated['status'];
            $lockedOrder->completed_at = null;
            $lockedOrder->save();

            OrderStatusHistory::query()->create([
                'order_id' => $lockedOrder->id,
                'previous_status' => $previousStatus,
                'new_status' => $validated['status'],
                'changed_by_user_id' => $seller->id,
                'note' => $validated['note'] ?? null,
                'changed_at' => now(),
            ]);
        });

        return redirect()
            ->route('orders.status', $order)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Seller-controlled lifecycle transitions from the project specification.
     */
    private function sellerAllowedNextStatuses(Order $order): array
    {
        return match ($order->status) {
            'not_started' => ['in_progress'],
            'in_progress' => ['under_review'],
            'revision_requested' => ['in_progress'],
            default => [],
        };
    }

    /**
     * TEMPORARY PLACEHOLDER.
     * Remove this method when the teammate's "Accept Hire Request" feature
     * creates real orders. It exists only so the deliverable features can
     * be demonstrated independently before that module is merged.
     */
    public function createDemo(Gig $gig): RedirectResponse
    {
        // 🛡️ Automated Capacity & Availability Guard Check
        if (!$gig->isAvailable()) {
            if (!$gig->is_accepting_orders) {
                return back()->with('error', 'This seller has temporarily paused orders (Exam Mode / Taking a break).');
            }

            return back()->with('error', 'This gig has reached its maximum order capacity for the week!');
        }

        $demoUserId = Auth::id() ?? $gig->user_id;

        $order = Order::firstOrCreate(
            [
                'gig_id' => $gig->id,
                'seller_id' => $gig->user_id,
                'buyer_id' => $demoUserId,
            ],
            [
                'agreed_price' => $gig->price,
                'status' => 'in_progress',
                'due_date' => now()->addDays($gig->delivery_time),
            ]
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Temporary demo order is ready.');
    }
}
