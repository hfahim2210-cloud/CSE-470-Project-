<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GigController extends Controller
{
    /**
     * Display a listing of the seller's gigs.
     */
    public function index()
    {
        $userId = Auth::id();

        // Active Gigs with eager-loaded active order counts to optimize performance
        $activeGigs = Gig::where('user_id', $userId)
            ->where('status', '!=', 'archived')
            ->withCount(['orders as active_orders_count' => function ($query) {
                $query->whereIn('status', ['not_started', 'in_progress', 'under_review', 'revision_requested']);
            }])
            ->latest()
            ->get();

        // Archived Gigs
        $archivedGigs = Gig::where('user_id', $userId)
            ->where('status', 'archived')
            ->latest()
            ->get();

        $sellerOrders = Order::query()->where('seller_id', $userId);
        $totalOrders = (clone $sellerOrders)->count();
        $activeOrders = (clone $sellerOrders)
            ->whereIn('status', ['not_started', 'in_progress', 'under_review', 'revision_requested'])
            ->count();
        $completedOrders = (clone $sellerOrders)->where('status', 'completed')->count();
        $completionRate = $totalOrders > 0
            ? round(($completedOrders / $totalOrders) * 100, 1)
            : 0;
        $totalEarnings = (float) (clone $sellerOrders)
            ->where('status', 'completed')
            ->sum('agreed_price');
        $monthlyEarnings = (float) (clone $sellerOrders)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('agreed_price');

        $monthlyBreakdown = collect(range(5, 0))->map(function (int $monthsBack) use ($sellerOrders): array {
            $month = now()->subMonthsNoOverflow($monthsBack);
            $monthOrders = (clone $sellerOrders)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);

            return [
                'label' => $month->format('M Y'),
                'orders' => (clone $monthOrders)->count(),
                'earnings' => (float) $monthOrders->sum('agreed_price'),
            ];
        });

        $recentCompletedOrders = (clone $sellerOrders)
            ->with(['gig', 'buyer'])
            ->where('status', 'completed')
            ->latest('completed_at')
            ->limit(5)
            ->get();

        return view('gigs.index', compact(
            'activeGigs',
            'archivedGigs',
            'totalOrders',
            'activeOrders',
            'completedOrders',
            'completionRate',
            'totalEarnings',
            'monthlyEarnings',
            'monthlyBreakdown',
            'recentCompletedOrders'
        ));
    }

    /**
     * Quick toggle for Exam Mode / accepting orders.
     */
    public function toggleAcceptingOrders($id)
    {
        $gig = Gig::findOrFail($id);

        $this->ensureSellerOwns($gig);

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

        $this->ensureSellerOwns($gig);

        $gig->update(['status' => 'archived']);

        return redirect()->route('gigs.index')->with('success', 'Gig archived successfully!');
    }

    /**
     * Restore an archived gig back to active status.
     */
    public function restore($id)
    {
        $gig = Gig::findOrFail($id);

        $this->ensureSellerOwns($gig);

        $gig->update(['status' => 'active']);

        return redirect()->route('gigs.index')->with('success', 'Gig restored successfully!');
    }

    /**
     * Display the public marketplace feed.
     */
    public function marketplace(Request $request)
    {
        $query = Gig::where('status', '!=', 'archived');

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
     * Store a newly created gig and its portfolio items in storage.
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
            'tiers' => 'nullable|array',
            'addons' => 'nullable|array|max:10',
        ]);

        $serviceOptions = $this->validatedServiceOptions($validated);

        $validated['is_accepting_orders'] = $request->has('is_accepting_orders');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('gigs', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'active';

        $portfolioFiles = $request->file('portfolio_files');
        unset($validated['portfolio_files'], $validated['tiers'], $validated['addons']);

        $gig = Gig::create($validated);

        if ($portfolioFiles) {
            foreach ($portfolioFiles as $file) {
                $filePath = $file->store('portfolio', 'public');
                $gig->portfolioItems()->create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        $this->syncServiceOptions($gig, $serviceOptions);

        return redirect()->route('gigs.index')->with('success', 'Gig created successfully!');
    }

    /**
     * Display the specified gig with its portfolio items.
     */
    public function show($id)
    {
        $gig = Gig::with([
            'portfolioItems',
            'tiers',
            'addons',
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
        $this->ensureSellerOwns($gig);

        return view('gigs.edit', compact('gig'));
    }

    /**
     * Update the specified gig in storage.
     */
    public function update(Request $request, $id)
    {
        $gig = Gig::findOrFail($id);

        $this->ensureSellerOwns($gig);

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
            'tiers' => 'nullable|array',
            'addons' => 'nullable|array|max:10',
        ]);

        $serviceOptions = $this->validatedServiceOptions($validated);

        $validated['is_accepting_orders'] = $request->has('is_accepting_orders');

        if ($request->hasFile('image')) {
            if ($gig->image && Storage::disk('public')->exists($gig->image)) {
                Storage::disk('public')->delete($gig->image);
            }
            $validated['image'] = $request->file('image')->store('gigs', 'public');
        }

        $portfolioFiles = $request->file('portfolio_files');
        unset($validated['portfolio_files'], $validated['tiers'], $validated['addons']);

        $gig->update($validated);

        if ($portfolioFiles) {
            foreach ($portfolioFiles as $file) {
                $filePath = $file->store('portfolio', 'public');
                $gig->portfolioItems()->create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        $this->syncServiceOptions($gig, $serviceOptions);

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

        $this->ensureSellerOwns($gig);

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

    /**
     * Download the seller's completed-order earnings as a spreadsheet-friendly CSV file.
     */
    public function exportEarnings(): StreamedResponse
    {
        $sellerId = Auth::id();
        $orders = Order::query()
            ->with(['gig', 'buyer'])
            ->where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->get();

        $fileName = 'gigex-earnings-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, ['GigEx Seller Earnings Summary']);
            fputcsv($output, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($output, ['Completed Orders', $orders->count()]);
            fputcsv($output, ['Total Earnings', number_format((float) $orders->sum('agreed_price'), 2, '.', '')]);
            fputcsv($output, []);
            fputcsv($output, ['Order ID', 'Gig', 'Buyer', 'Package', 'Add-ons', 'Completed At', 'Earnings']);

            foreach ($orders as $order) {
                $addonNames = collect($order->selected_addons ?? [])->pluck('name')->implode(', ');

                fputcsv($output, [
                    $order->id,
                    $order->gig?->title,
                    $order->buyer?->name,
                    data_get($order->selected_tier, 'title', 'Base Service'),
                    $addonNames ?: 'None',
                    optional($order->completed_at)->format('Y-m-d H:i:s'),
                    number_format((float) $order->agreed_price, 2, '.', ''),
                ]);
            }

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedServiceOptions(array $validated): array
    {
        $tierOrder = ['basic' => 1, 'standard' => 2, 'premium' => 3];
        $tiers = [];

        foreach (($validated['tiers'] ?? []) as $name => $tierData) {
            if (! array_key_exists($name, $tierOrder) || empty($tierData['enabled'])) {
                continue;
            }

            $tier = Validator::make($tierData, [
                'title' => ['required', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:1000'],
                'price' => ['required', 'numeric', 'min:1', 'max:999999.99'],
                'delivery_time' => ['required', 'integer', 'min:1', 'max:365'],
                'revisions' => ['required', 'integer', 'min:0', 'max:100'],
            ])->validate();

            $tiers[$name] = $tier + ['sort_order' => $tierOrder[$name]];
        }

        $addons = [];

        foreach (($validated['addons'] ?? []) as $addonData) {
            $hasContent = filled($addonData['name'] ?? null)
                || filled($addonData['description'] ?? null)
                || filled($addonData['price'] ?? null);

            if (! $hasContent) {
                continue;
            }

            $addon = Validator::make($addonData, [
                'name' => ['required', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:1000'],
                'price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
                'extra_days' => ['required', 'integer', 'min:0', 'max:365'],
            ])->validate();

            $addons[] = $addon + ['sort_order' => count($addons) + 1];
        }

        return compact('tiers', 'addons');
    }

    private function syncServiceOptions(Gig $gig, array $serviceOptions): void
    {
        $savedTierNames = [];

        foreach ($serviceOptions['tiers'] as $name => $tier) {
            $gig->tiers()->updateOrCreate(['name' => $name], $tier);
            $savedTierNames[] = $name;
        }

        if ($savedTierNames === []) {
            $gig->tiers()->delete();
        } else {
            $gig->tiers()->whereNotIn('name', $savedTierNames)->delete();
        }

        $gig->addons()->delete();

        foreach ($serviceOptions['addons'] as $addon) {
            $gig->addons()->create($addon);
        }
    }

    private function ensureSellerOwns(Gig $gig): void
    {
        abort_unless(
            Auth::check()
                && Auth::user()->role === 'seller'
                && (int) $gig->user_id === (int) Auth::id(),
            403,
            'You may manage only your own gigs.'
        );
    }
}
