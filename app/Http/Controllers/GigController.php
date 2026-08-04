<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\User;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GigController extends Controller
{
    /**
     * Display a listing of the seller's gigs (Seller Management - Module 1).
     */
    public function index()
    {
        // Get all gigs created by the logged-in seller (or fallback ID 1)
        $gigs = Gig::where('user_id', Auth::id() ?? 1)
                   ->latest()
                   ->get();

        return view('gigs.index', compact('gigs'));
    }

    /**
     * Display the public marketplace feed (Browse, Filter, Sort, Search - Module 2).
     */
    public function marketplace(Request $request)
    {
        $query = Gig::where('is_archived', false);

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

        return view('gigs.marketplace', compact('gigs'));
    }

    /**
     * Display a seller's public profile.
     */
    public function sellerProfile(User $user)
    {
        $gigs = $user->gigs()->where('is_archived', false)->latest()->get();

        return view('gigs.seller-profile', compact('user', 'gigs'));
    }

    /**
     * Show the form for creating a new gig.
     */
    public function create()
    {
        return view('gigs.create');
    }

    /**
     * Store a newly created gig and its portfolio items in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:1',
            'delivery_time' => 'required|integer|min:1',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'portfolio_files.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        // 1. Store uploaded cover image path into $validated['image']
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('gigs', 'public');
        }

        // 2. Assign default user_id
        $validated['user_id'] = Auth::id() ?? 1;

        // 3. Keep $portfolioFiles separate so it doesn't break Gig::create()
        $portfolioFiles = $request->file('portfolio_files');

        // Unset portfolio_files array key because it belongs in portfolio_items table
        unset($validated['portfolio_files']);

        // 4. Create the Gig record (includes saved 'image' path)
        $gig = Gig::create($validated);

        // 5. Store related portfolio items if uploaded
        if ($portfolioFiles) {
            foreach ($portfolioFiles as $file) {
                $filePath = $file->store('portfolio', 'public');
                $gig->portfolioItems()->create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        return redirect()->route('gigs.index')->with('success', 'Gig created successfully!');
    }

    /**
     * Display the specified gig with its portfolio items.
     */
    public function show($id)
    {
        $gig = Gig::with('portfolioItems')->findOrFail($id);
        return view('gigs.show', compact('gig'));
    }

    /**
     * Show the form for editing the specified gig.
     */
    public function edit($id)
    {
        $gig = Gig::findOrFail($id);
        return view('gigs.edit', compact('gig'));
    }

    /**
     * Update the specified gig in storage.
     */
    public function update(Request $request, $id)
    {
        $gig = Gig::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:1',
            'delivery_time' => 'required|integer|min:1',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'portfolio_files.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        // 1. Handle replacement cover image upload
        if ($request->hasFile('image')) {
            // Delete old cover image from disk if it exists
            if ($gig->image && Storage::disk('public')->exists($gig->image)) {
                Storage::disk('public')->delete($gig->image);
            }

            // Save new cover image
            $validated['image'] = $request->file('image')->store('gigs', 'public');
        }

        // 2. Extract new portfolio files if present
        $portfolioFiles = $request->file('portfolio_files');
        unset($validated['portfolio_files']);

        // 3. Update gig details
        $gig->update($validated);

        // 4. Add additional portfolio files if uploaded
        if ($portfolioFiles) {
            foreach ($portfolioFiles as $file) {
                $filePath = $file->store('portfolio', 'public');
                $gig->portfolioItems()->create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        return redirect()->route('gigs.show', $gig->id)->with('success', 'Gig updated successfully!');
    }

    /**
     * Remove the specified gig and its media files from storage.
     */
    public function destroy($id)
    {
        $gig = Gig::with('portfolioItems')->findOrFail($id);

        // 1. Delete associated cover image from storage
        if ($gig->image && Storage::disk('public')->exists($gig->image)) {
            Storage::disk('public')->delete($gig->image);
        }

        // 2. Delete associated portfolio files from storage
        foreach ($gig->portfolioItems as $item) {
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
        }

        // 3. Delete database record
        $gig->delete();

        return redirect()->route('gigs.index')->with('success', 'Gig deleted successfully!');
    }
}