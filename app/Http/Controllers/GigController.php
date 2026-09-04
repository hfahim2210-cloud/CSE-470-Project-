<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\User;
use App\Models\GigTier;
use App\Models\GigAddon;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GigController extends Controller
{
    /**
     * Display a listing of the seller's gigs.
     */
    public function index()
    {
        $userId = Auth::id() ?? 1;

        // Active Gigs with eager-loaded active order counts
        $activeGigs = Gig::where('user_id', $userId)
            ->where('status', '!=', 'archived')
            ->withCount(['orders as active_orders_count' => function ($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            }])
            ->latest()
            ->get();

        // Archived Gigs
        $archivedGigs = Gig::where('user_id', $userId)
            ->where('status', 'archived')
            ->latest()
            ->get();

        return view('gigs.index', compact('activeGigs', 'archivedGigs'));
    }

    /**
     * Quick toggle for Exam Mode / accepting orders.
     */
    public function toggleAcceptingOrders($id)
    {
        $gig = Gig::findOrFail($id);

        if ($gig->user_id !== (Auth::id() ?? 1)) {
            abort(403, 'Unauthorized action.');
        }

        $gig->update([
            'is_accepting_orders' => !$gig->is_accepting_orders
        ]);

        $statusMessage = $gig->is_accepting_orders 
            ? 'Gig is now accepting orders.' 
            : 'Gig paused (Exam Mode enabled).';

        return redirect()->route('gigs.index')->with('success', $statusMessage);
    }

    /**
     * Archive an active gig.
     */
    public function archive($id)
    {
        $gig = Gig::findOrFail($id);

        if ($gig->user_id !== (Auth::id() ?? 1)) {
            abort(403, 'Unauthorized action.');
        }

        $gig->update(['status' => 'archived']);

        return redirect()->route('gigs.index')->with('success', 'Gig archived successfully!');
    }

    /**
     * Restore an archived gig back to active status.
     */
    public function restore($id)
    {
        $gig = Gig::findOrFail($id);

        if ($gig->user_id !== (Auth::id() ?? 1)) {
            abort(403, 'Unauthorized action.');
        }

        $gig->update(['status' => 'active']);

        return redirect()->route('gigs.index')->with('success', 'Gig restored successfully!');
    }

    /**
     * Display the public marketplace feed.
     */
    public function marketplace(Request $request)
    {
        $query = Gig::where('status', '!=', 'archived')->with('tiers');

        // Search by keyword
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
        $gigs = $user->gigs()->where('status', '!=', 'archived')->latest()->get();

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
     * Store a newly created gig along with tiers, addons, and portfolio items.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:1',
            'delivery_time' => 'required|integer|min:1',
            'max_weekly_orders' => 'required|integer|min:1|max:50',
            'is_accepting_orders' => 'nullable|boolean',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'portfolio_files.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',

            // Service Tiers validation
            'tiers' => 'nullable|array',
            'tiers.*.tier_type' => 'required_with:tiers|in:basic,standard,premium',
            'tiers.*.title' => 'required_with:tiers|string|max:255',
            'tiers.*.description' => 'required_with:tiers|string',
            'tiers.*.price' => 'required_with:tiers|numeric|min:0',
            'tiers.*.delivery_days' => 'required_with:tiers|integer|min:1',
            'tiers.*.revisions' => 'required_with:tiers|integer|min:0',

            // Add-ons validation
            'addons' => 'nullable|array',
            'addons.*.title' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'required_with:addons|numeric|min:0',
            'addons.*.extra_delivery_days' => 'nullable|integer',
        ]);

        $validated['is_accepting_orders'] = $request->has('is_accepting_orders');
        $validated['user_id'] = Auth::id() ?? 1;
        $validated['status'] = 'active';

        // Extract files and relationship data out of $validated array
        $portfolioFiles = $request->file('portfolio_files');
        $tiersData = $validated['tiers'] ?? [];
        $addonsData = $validated['addons'] ?? [];

        unset($validated['portfolio_files'], $validated['tiers'], $validated['addons']);

        DB::transaction(function () use ($request, $validated, $portfolioFiles, $tiersData, $addonsData) {
            // 1. Upload main gig thumbnail
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('gigs', 'public');
            }

            // 2. Create Base Gig
            $gig = Gig::create($validated);

            // 3. Save Tiers if provided
            foreach ($tiersData as $tier) {
                if (!empty($tier['title'])) {
                    $gig->tiers()->create($tier);
                }
            }

            // 4. Save Add-ons if provided
            foreach ($addonsData as $addon) {
                if (!empty($addon['title'])) {
                    $gig->addons()->create([
                        'title' => $addon['title'],
                        'price' => $addon['price'],
                        'extra_delivery_days' => $addon['extra_delivery_days'] ?? 0,
                    ]);
                }
            }

            // 5. Upload & Save Portfolio Items
            if ($portfolioFiles) {
                foreach ($portfolioFiles as $file) {
                    $filePath = $file->store('portfolio', 'public');
                    $gig->portfolioItems()->create([
                        'file_path' => $filePath,
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }
        });

        return redirect()->route('gigs.index')->with('success', 'Gig created successfully!');
    }

    /**
     * Display the specified gig with its portfolio items, tiers, and add-ons.
     */
    public function show($id)
    {
        $gig = Gig::with([
            'tiers',
            'addons',
            'portfolioItems',
            'orders.buyer',
            'orders.review',
            'orders.rating',
        ])->findOrFail($id);

        return view('gigs.show', compact('gig'));
    }

    /**
     * Show the form for editing the specified gig.
     */
    public function edit($id)
    {
        $gig = Gig::with(['tiers', 'addons'])->findOrFail($id);

        if ($gig->user_id !== (Auth::id() ?? 1)) {
            abort(403, 'Unauthorized action.');
        }

        return view('gigs.edit', compact('gig'));
    }

    /**
     * Update the specified gig in storage.
     */
    public function update(Request $request, $id)
    {
        $gig = Gig::findOrFail($id);

        if ($gig->user_id !== (Auth::id() ?? 1)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:1',
            'delivery_time' => 'required|integer|min:1',
            'max_weekly_orders' => 'required|integer|min:1|max:50',
            'is_accepting_orders' => 'nullable|boolean',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'portfolio_files.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',

            // Service Tiers validation
            'tiers' => 'nullable|array',
            'tiers.*.tier_type' => 'required_with:tiers|in:basic,standard,premium',
            'tiers.*.title' => 'required_with:tiers|string|max:255',
            'tiers.*.description' => 'required_with:tiers|string',
            'tiers.*.price' => 'required_with:tiers|numeric|min:0',
            'tiers.*.delivery_days' => 'required_with:tiers|integer|min:1',
            'tiers.*.revisions' => 'required_with:tiers|integer|min:0',

            // Add-ons validation
            'addons' => 'nullable|array',
            'addons.*.title' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'required_with:addons|numeric|min:0',
            'addons.*.extra_delivery_days' => 'nullable|integer',
        ]);

        $validated['is_accepting_orders'] = $request->has('is_accepting_orders');

        $portfolioFiles = $request->file('portfolio_files');
        $tiersData = $validated['tiers'] ?? [];
        $addonsData = $validated['addons'] ?? [];

        unset($validated['portfolio_files'], $validated['tiers'], $validated['addons']);

        DB::transaction(function () use ($gig, $request, $validated, $portfolioFiles, $tiersData, $addonsData) {
            // Handle image replacement
            if ($request->hasFile('image')) {
                if ($gig->image && Storage::disk('public')->exists($gig->image)) {
                    Storage::disk('public')->delete($gig->image);
                }
                $validated['image'] = $request->file('image')->store('gigs', 'public');
            }

            // Update main gig fields
            $gig->update($validated);

            // Update or recreate tiers
            if (!empty($tiersData)) {
                $gig->tiers()->delete(); // Clear old tiers
                foreach ($tiersData as $tier) {
                    if (!empty($tier['title'])) {
                        $gig->tiers()->create($tier);
                    }
                }
            }

            // Update or recreate add-ons
            if (!empty($addonsData)) {
                $gig->addons()->delete(); // Clear old addons
                foreach ($addonsData as $addon) {
                    if (!empty($addon['title'])) {
                        $gig->addons()->create([
                            'title' => $addon['title'],
                            'price' => $addon['price'],
                            'extra_delivery_days' => $addon['extra_delivery_days'] ?? 0,
                        ]);
                    }
                }
            }

            // Upload additional portfolio items
            if ($portfolioFiles) {
                foreach ($portfolioFiles as $file) {
                    $filePath = $file->store('portfolio', 'public');
                    $gig->portfolioItems()->create([
                        'file_path' => $filePath,
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }
        });

        return redirect()->route('gigs.show', $gig->id)->with('success', 'Gig updated successfully!');
    }

    /**
     * Remove the specified gig and its media files from storage.
     */
    public function destroy($id)
    {
        $gig = Gig::with([
            'portfolioItems',
            'orders.buyer',
            'orders.review',
            'orders.rating',
        ])->findOrFail($id);

        if ($gig->user_id !== (Auth::id() ?? 1)) {
            abort(403, 'Unauthorized action.');
        }

        if ($gig->image && Storage::disk('public')->exists($gig->image)) {
            Storage::disk('public')->delete($gig->image);
        }

        foreach ($gig->portfolioItems as $item) {
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
        }

        $gig->delete();

        return redirect()->route('gigs.index')->with('success', 'Gig deleted permanently!');
    }
}