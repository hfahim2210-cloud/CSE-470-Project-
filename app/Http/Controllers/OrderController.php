<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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