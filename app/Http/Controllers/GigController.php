<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Http\Request;

class GigController extends Controller
{
    /**
     * Displayinggg public gig feed (Browse, Filter, Sort, Search). */
    public function index(Request $request)
    {
        $query = Gig::where('is_active', true);

        // Search by keyword (title or description)
        if ($request->filled('search')) {
            $keyword = $request->input('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Sort by price
        if ($request->input('sort') === 'low_high') {
            $query->orderBy('price', 'asc');
        } elseif ($request->input('sort') === 'high_low') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $gigs = $query->paginate(10);

        return view('gigs.index', compact('gigs'));
    }

    /**
     * seller er public profile
     */
    public function sellerProfile(User $user)
    {
        $gigs = $user->gigs()->where('is_active', true)->latest()->get();

        return view('gigs.seller-profile', compact('user', 'gigs'));
    }
}