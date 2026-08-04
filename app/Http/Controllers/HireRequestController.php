<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\HireRequest;
use App\Support\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HireRequestController extends Controller
{
    /**
     * Feature 1: Show the buyer's hire-request form.
     */
    public function create(Gig $gig): View
    {
        $buyer = CurrentUser::buyer();

        abort_if(
            (int) $gig->user_id === (int) $buyer->id,
            403,
            'You cannot submit a request for your own gig.'
        );

        abort_unless(
            $gig->status === 'active',
            404,
            'This gig is not currently active.'
        );

        $gig->load('user');

        return view('hire_requests.create', compact('gig'));
    }

    /**
     * Feature 1: Validate and save a pending hire request.
     */
    public function store(Request $request, Gig $gig): RedirectResponse
    {
        $buyer = CurrentUser::buyer();

        abort_if(
            (int) $gig->user_id === (int) $buyer->id,
            403,
            'You cannot submit a request for your own gig.'
        );

        abort_unless(
            $gig->status === 'active',
            404,
            'This gig is not currently active.'
        );

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $alreadyPending = HireRequest::query()
            ->where('gig_id', $gig->id)
            ->where('buyer_id', $buyer->id)
            ->where('status', HireRequest::STATUS_PENDING)
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withErrors([
                    'message' => 'You already have a pending request for this gig.',
                ])
                ->withInput();
        }

        HireRequest::query()->create([
            'gig_id' => $gig->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $gig->user_id,
            'message' => $validated['message'],
            'status' => HireRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('gigs.show', $gig)
            ->with('success', 'Hire request submitted successfully.');
    }

    /**
     * Feature 2: Show all hire requests received by the temporary seller.
     */
    public function incoming(): View
    {
        $seller = CurrentUser::seller();

        $hireRequests = HireRequest::query()
            ->with(['gig', 'buyer'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        return view(
            'hire_requests.incoming',
            compact('hireRequests', 'seller')
        );
    }
}
