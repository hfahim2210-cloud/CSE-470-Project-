<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Gigs</title>
</head>
<body>

    <h1>Available Gigs</h1>

    <!-- Search, Filter, Sort Form -->
    <form method="GET" action="{{ route('gigs.index') }}">

        <input type="text" name="search" placeholder="Search gigs..."
               value="{{ request('search') }}">

        <select name="category">
            <option value="">All Categories</option>
            <option value="Tutoring" {{ request('category') == 'Tutoring' ? 'selected' : '' }}>Tutoring</option>
            <option value="Tech" {{ request('category') == 'Tech' ? 'selected' : '' }}>Tech</option>
            <option value="Creative" {{ request('category') == 'Creative' ? 'selected' : '' }}>Creative</option>
            <option value="Academics" {{ request('category') == 'Academics' ? 'selected' : '' }}>Academics</option>
        </select>

        <select name="sort">
            <option value="">Newest First</option>
            <option value="low_high" {{ request('sort') == 'low_high' ? 'selected' : '' }}>Price: Low to High</option>
            <option value="high_low" {{ request('sort') == 'high_low' ? 'selected' : '' }}>Price: High to Low</option>
        </select>

        <button type="submit">Apply</button>
    </form>

    <hr>

    <!-- Gig Listings -->
    @forelse ($gigs as $gig)
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <h3>{{ $gig->title }}</h3>
            <p>{{ $gig->description }}</p>
            <p><strong>Price:</strong> ${{ number_format($gig->price, 2) }}</p>
            <p><strong>Category:</strong> {{ $gig->category }}</p>
            <p><strong>Seller:</strong> <a href="{{ route('sellers.profile', $gig->user_id) }}">{{ $gig->user->name ?? 'Unknown' }}</a></p>
        </div>
    @empty
        <p>No gigs found.</p>
    @endforelse

    <!-- Pagination Links -->
    {{ $gigs->links() }}

</body>
</html>
