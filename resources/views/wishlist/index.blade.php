<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <a href="{{ route('gigs.marketplace') }}" class="btn btn-outline-secondary btn-sm mb-4">&larr; Back to Gigs</a>

    <h2 class="mb-4">❤️ My Wishlist</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse ($wishlists as $item)
            @if($item->gig)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2">🏷️ {{ $item->gig->category }}</span>
                            <h5>{{ $item->gig->title }}</h5>
                            <p class="text-muted small">{{ $item->gig->description }}</p>
                            <p class="mb-1">💰 <strong>${{ number_format($item->gig->price, 2) }}</strong></p>
                            <p class="mb-0"><small>Seller: <a href="{{ route('sellers.profile', $item->gig->user_id) }}">{{ $item->gig->user->name ?? 'Unknown' }}</a></small></p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <form action="{{ route('wishlist.destroy', $item->gig->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">🗑️ Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12 text-center py-5">
                <p class="fs-1 mb-2">💔</p>
                <p class="text-muted fs-5">Your wishlist is empty. Browse gigs and save your favorites!</p>
            </div>
        @endforelse
    </div>

</div>

</body>
</html>