<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - Seller Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <a href="{{ route('gigs.marketplace') }}" class="btn btn-outline-secondary btn-sm mb-4">&larr; Back to Gigs</a>

    <!-- Seller Header -->
    <div class="d-flex align-items-center bg-white rounded shadow-sm p-4 mb-4"
         style="border-left: 5px solid #6f42c1;">
        <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-3"
             style="width: 70px; height: 70px; font-size: 28px;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h2 class="mb-1">👤 {{ $user->name }}</h2>
            <p class="text-muted mb-1">📅 Member since {{ $user->created_at->format('F Y') }}</p>
            <span class="badge bg-success">🟢 {{ $gigs->count() }} active gig{{ $gigs->count() !== 1 ? 's' : '' }}</span>
        </div>
    </div>

    <h4 class="mb-3">🧰 Gigs by {{ $user->name }}</h4>

    <div class="row">
        @forelse ($gigs as $gig)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="transition: transform 0.15s;">
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2">🏷️ {{ $gig->category }}</span>
                        <h5>{{ $gig->title }}</h5>
                        <p class="text-muted small">{{ $gig->description }}</p>
                        <p class="mb-1">💰 <strong>${{ number_format($gig->price, 2) }}</strong></p>
                        @if(isset($gig->delivery_time))
                            <p class="mb-0 text-muted small">⏱️ Delivery: {{ $gig->delivery_time }} day{{ $gig->delivery_time > 1 ? 's' : '' }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="fs-1 mb-2">🕵️</p>
                <p class="text-muted fs-5">This seller has no active gigs right now.</p>
            </div>
        @endforelse
    </div>

</div>

</body>
</html>