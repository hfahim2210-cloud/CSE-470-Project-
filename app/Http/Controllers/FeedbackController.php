<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    /**
     * Feature 1: buyer leaves a text review after the order is completed.
     */
    public function storeReview(Request $request, Order $order): RedirectResponse
    {
        $this->ensureBuyerMayLeaveFeedback($order);

        $validated = $request->validate([
            'review_text' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $order->review()->updateOrCreate(
            ['order_id' => $order->id],
            ['review_text' => $validated['review_text']]
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Text review saved successfully.');
    }

    /**
     * Feature 2: buyer leaves a 1-5 star rating after the order is completed.
     */
    public function storeRating(Request $request, Order $order): RedirectResponse
    {
        $this->ensureBuyerMayLeaveFeedback($order);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
        ]);

        $order->rating()->updateOrCreate(
            ['order_id' => $order->id],
            ['rating' => $validated['rating']]
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Star rating saved successfully.');
    }

    private function ensureBuyerMayLeaveFeedback(Order $order): void
    {
        if (! Auth::check()
            || Auth::user()->role !== 'buyer'
            || (int) Auth::id() !== (int) $order->buyer_id) {
            abort(403, 'Only the buyer assigned to this order can leave feedback.');
        }

        if ($order->status !== 'completed') {
            throw ValidationException::withMessages([
                'feedback' => 'You can only leave a review or rating after the order is completed.',
            ]);
        }
    }
}
