<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Gigs - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Seller Gigs</h2>
        <a href="{{ route('gigs.create') }}" class="btn btn-primary">+ Create New Gig</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @forelse($gigs as $gig)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    @if($gig->image)
                        <img src="{{ asset('storage/' . $gig->image) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $gig->title }}">
                    @else
                        <div class="bg-secondary text-white text-center py-5">No Thumbnail</div>
                    @endif

                    <div class="card-body">
                        <span class="badge bg-info text-dark mb-2">{{ $gig->category }}</span>
                        <h5 class="card-title">{{ $gig->title }}</h5>
                        <p class="card-text text-muted">{{ Str::limit($gig->description, 80) }}</p>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between align-items-center border-top-0">
                        <strong class="text-success">${{ $gig->price }}</strong>
                        <small class="text-muted">{{ $gig->delivery_days }} Days Delivery</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No gigs created yet.</p>
                <a href="{{ route('gigs.create') }}" class="btn btn-outline-primary">Create Your First Gig</a>
            </div>
        @endforelse
    </div>
</div>

</body>
</html>