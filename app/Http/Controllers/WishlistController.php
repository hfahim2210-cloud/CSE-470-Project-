<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Gig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Show the buyer's wishlist.
     */
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id() ?? 1)
                        ->with('gig.user')
                        ->latest()
                        ->get();

        return view('wishlist.index', compact('wishlists'));
    }

    /**
     * Add a gig to the buyer's wishlist.
     */
    public function store($gigId)
    {
        Wishlist::firstOrCreate([
            'user_id' => Auth::id() ?? 1,
            'gig_id' => $gigId,
        ]);

        return back()->with('success', 'Gig added to your wishlist!');
    }

    /**
     * Remove a gig from the buyer's wishlist.
     */
    public function destroy($gigId)
    {
        Wishlist::where('user_id', Auth::id() ?? 1)
                ->where('gig_id', $gigId)
                ->delete();

        return back()->with('success', 'Gig removed from your wishlist.');
    }
}