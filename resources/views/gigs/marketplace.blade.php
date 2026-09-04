<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Gigs - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/gigex.css') }}" rel="stylesheet">
</head>
<body class="bg-light">

@include('partials.navigation')

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Available Gigs</h1>
        <div>
            @auth
                @if(Auth::user()->role === 'buyer')
                    <a href="{{ route('wishlist.index') }}" class="btn btn-outline-danger">❤️ Wishlist</a>
                @elseif(Auth::user()->role === 'seller')
                    <a href="{{ route('gigs.index') }}" class="btn btn-outline-primary">My Dashboard</a>
                @endif
            @endauth
        </div>
    </div>

    <!-- Search, Filter, Sort Form -->
    <form method="GET" action="{{ route('gigs.marketplace') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search gigs..." value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <option value="Graphics & Design" {{ request('category') == 'Graphics & Design' ? 'selected' : '' }}>Graphics & Design</option>
                <option value="Programming & Tech" {{ request('category') == 'Programming & Tech' ? 'selected' : '' }}>Programming & Tech</option>
                <option value="Web Development" {{ request('category') == 'Web Development' ? 'selected' : '' }}>Web Development</option>
                <option value="Digital Marketing" {{ request('category') == 'Digital Marketing' ? 'selected' : '' }}>Digital Marketing</option>
                <option value="Writing & Translation" {{ request('category') == 'Writing & Translation' ? 'selected' : '' }}>Writing & Translation</option>
                <option value="Video & Animation" {{ request('category') == 'Video & Animation' ? 'selected' : '' }}>Video & Animation</option>
                <option value="Tutoring" {{ request('category') == 'Tutoring' ? 'selected' : '' }}>Tutoring</option>
                <option value="Creative" {{ request('category') == 'Creative' ? 'selected' : '' }}>Creative</option>
                <option value="Academics" {{ request('category') == 'Academics' ? 'selected' : '' }}>Academics</option>
                <option value="Tech" {{ request('category') == 'Tech' ? 'selected' : '' }}>Tech</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="">Newest First</option>
                <option value="low_high" {{ request('sort') == 'low_high' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="high_low" {{ request('sort') == 'high_low' ? 'selected' : '' }}>Price: High to Low</option>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Gig Listings -->
    <div class="row">
        @forelse ($gigs as $gig)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    @if($gig->image)
                        <img src="{{ route('media.show', ['path' => $gig->image]) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $gig->title }}">
                    @else
                        <div class="bg-secondary text-white text-center py-5">No Image</div>
                    @endif
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2">{{ $gig->category }}</span>
                        <h5>{{ $gig->title }}</h5>
                        <p class="text-muted small">{{ Str::limit($gig->description, 90) }}</p>
                        <p class="mb-1"><strong>Price:</strong> ${{ number_format($gig->price, 2) }}</p>
                        <p class="mb-0"><small>Seller: <a href="{{ route('sellers.profile', $gig->user_id) }}">{{ $gig->user->name ?? 'Unknown' }}</a></small></p>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex gap-2">
                        <a href="{{ route('gigs.show', $gig->id) }}" class="btn btn-outline-primary btn-sm flex-grow-1">View Details</a>
                        @auth
                            @if(Auth::user()->role === 'buyer')
                                <form action="{{ route('wishlist.store', $gig->id) }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Save to Wishlist">❤️</button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-4">
                <p class="text-muted fs-5">No gigs found matching your criteria.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-center mt-4">
        {{ $gigs->links() }}
    </div>
</div>

</body>
</html>
