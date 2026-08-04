<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Gigs - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Available Gigs</h1>
        <a href="{{ route('gigs.index') }}" class="btn btn-outline-primary">My Dashboard</a>
    </div>

    <!-- Search, Filter, Sort Form -->
    <form method="GET" action="{{ route('gigs.marketplace') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search gigs..." value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <option value="Tutoring" {{ request('category') == 'Tutoring' ? 'selected' : '' }}>Tutoring</option>
                <option value="Tech" {{ request('category') == 'Tech' ? 'selected' : '' }}>Tech</option>
                <option value="Creative" {{ request('category') == 'Creative' ? 'selected' : '' }}>Creative</option>
                <option value="Academics" {{ request('category') == 'Academics' ? 'selected' : '' }}>Academics</option>
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
                        <img src="{{ asset('storage/' . $gig->image) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $gig->title }}">
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
                    <div class="card-footer bg-white border-top-0">
                        <a href="{{ route('gigs.show', $gig->id) }}" class="btn btn-outline-primary btn-sm w-100">View Details</a>
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