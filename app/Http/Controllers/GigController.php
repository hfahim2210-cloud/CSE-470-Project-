<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GigController extends Controller
{
    /**
     * Display a listing of the seller's gigs.
     */
    public function index()
    {
        // Get all gigs created by the logged-in seller
        $gigs = Gig::where('user_id', Auth::id() ?? 1)
                   ->latest()
                   ->get();

        return view('gigs.index', compact('gigs'));
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
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:1',
            'delivery_days' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'portfolio_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5000',
        ]);

        // Handle cover image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('gigs', 'public');
        }

        // Create the Gig
        $gig = Gig::create([
            'user_id' => Auth::id() ?? 1, // Defaulting to user 1 for now until auth is installed
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'delivery_time' => $validated['delivery_days'],
            'image' => $imagePath,
            'status' => 'active',
        ]);

        // Handle Multiple Portfolio Uploads
        if ($request->hasFile('portfolio_files')) {
            foreach ($request->file('portfolio_files') as $file) {
                $filePath = $file->store('portfolios', 'public');
                $extension = strtolower($file->getClientOriginalExtension());
                $fileType = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf';

                PortfolioItem::create([
                    'gig_id' => $gig->id,
                    'title' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                ]);
            }
        }

        return redirect()->route('gigs.index')->with('success', 'Gig and portfolio items uploaded successfully!');
    }
}