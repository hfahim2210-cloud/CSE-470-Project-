<?php

namespace App\Http\Controllers;

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
        $userId = Auth::id();

        $orders = Order::with(['gig', 'seller', 'buyer', 'deliverable'])
            ->where(function ($query) use ($userId): void {
                $query->where('seller_id', $userId)
                    ->orWhere('buyer_id', $userId);
            })
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless(
            in_array((int) Auth::id(), [(int) $order->seller_id, (int) $order->buyer_id], true),
            403,
            'You may view only your own orders.'
        );

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

}
