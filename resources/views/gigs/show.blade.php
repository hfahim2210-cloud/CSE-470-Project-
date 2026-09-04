<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $gig->title }} - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="{{ asset('css/gigex.css') }}" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.navigation')

    <div class="container">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <a href="{{ route('gigs.marketplace') }}" class="btn btn-outline-secondary mb-4">&leftarrow; Back to Marketplace</a>

        <div class="row">
            {{-- Left Column: Gig Details & Media --}}
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 overflow-hidden">
                    {{-- Uploaded Cover Image Display --}}
                    @if($gig->image)
                        <img src="{{ route('media.show', ['path' => $gig->image]) }}" class="card-img-top" alt="{{ $gig->title }}" style="max-height: 420px; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white text-center py-5">
                            <p class="mb-0 fs-5">📷 No Cover Image Uploaded</p>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-info text-dark">{{ $gig->category }}</span>
                            {{-- Relative Time --}}
                            <small class="text-muted">📅 Posted {{ $gig->created_at->diffForHumans() }}</small>
                        </div>

                        <h2 class="card-title h3 mb-2">{{ $gig->title }}</h2>
                        
                        {{-- Exact Date & Time Published --}}
                        <p class="text-muted small border-bottom pb-3 mb-3">
                            Published on <strong>{{ $gig->created_at->format('F j, Y \a\t g:i A') }}</strong>
                        </p>

                        <h5 class="fw-bold">About This Gig</h5>
                        <p class="card-text text-secondary mb-4" style="white-space: pre-line; line-height: 1.7;">{{ $gig->description }}</p>

                        @if($gig->tiers->isNotEmpty())
                            <hr class="my-4">
                            <h5 class="fw-bold mb-3">Service Packages</h5>
                            <div class="row g-3">
                                @foreach($gig->tiers as $tier)
                                    <div class="col-md-4">
                                        <div class="card h-100 {{ $tier->name === 'standard' ? 'border-primary shadow-sm' : '' }}">
                                            <div class="card-body">
                                                <span class="badge {{ $tier->name === 'standard' ? 'bg-primary' : 'bg-secondary' }} text-uppercase mb-2">{{ $tier->name }}</span>
                                                <h6 class="fw-bold">{{ $tier->title }}</h6>
                                                <div class="h4 text-success">${{ number_format($tier->price, 2) }}</div>
                                                <div class="small text-muted mb-2">{{ $tier->delivery_time }} {{ Str::plural('day', $tier->delivery_time) }} · {{ $tier->revisions }} {{ Str::plural('revision', $tier->revisions) }}</div>
                                                <p class="small mb-0">{{ $tier->description ?: 'Service package' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($gig->addons->isNotEmpty())
                            <div class="mt-4">
                                <h5 class="fw-bold mb-3">Available Add-ons</h5>
                                <div class="list-group">
                                    @foreach($gig->addons as $addon)
                                        <div class="list-group-item d-flex justify-content-between gap-3">
                                            <div>
                                                <strong>{{ $addon->name }}</strong>
                                                <div class="small text-muted">{{ $addon->description ?: 'Optional service extra' }}{{ $addon->extra_days ? ' · +'.$addon->extra_days.' day(s)' : '' }}</div>
                                            </div>
                                            <strong class="text-success text-nowrap">+${{ number_format($addon->price, 2) }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Portfolio Work Samples --}}
                        @if($gig->portfolioItems && $gig->portfolioItems->count() > 0)
                            <hr class="my-4">
                            <h5 class="fw-bold mb-3">Portfolio & Work Samples</h5>
                            <div class="row g-3">
                                @foreach($gig->portfolioItems as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            @if(Str::endsWith($item->file_path, ['.pdf']))
                                                <div class="card-body text-center d-flex flex-column justify-content-center bg-light rounded">
                                                    <p class="fs-1 mb-1">📄</p>
                                                    <a href="{{ route('media.show', ['path' => $item->file_path]) }}" target="_blank" class="btn btn-sm btn-outline-primary">View PDF Sample</a>
                                                </div>
                                            @else
                                                <a href="{{ route('media.show', ['path' => $item->file_path]) }}" target="_blank">
                                                    <img src="{{ route('media.show', ['path' => $item->file_path]) }}" class="img-fluid rounded" style="height: 140px; width: 100%; object-fit: cover;" alt="Portfolio Sample">
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Right Column: Pricing, Capacity Check, Order Action, & Seller Controls --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">{{ $gig->tiers->isNotEmpty() ? 'Starting at' : 'Price' }}</span>
                            <span class="fs-2 fw-bold text-success">${{ number_format($gig->tiers->min('price') ?? $gig->price, 2) }}</span>
                        </div>

                        <div class="mb-3">
                            <strong>⏱️ Delivery Time:</strong>
                            <span>{{ $gig->delivery_time }} {{ Str::plural('Day', $gig->delivery_time) }}</span>
                        </div>

                        <hr>

                        @php
                            $viewerOrder = Auth::check()
                                ? $gig->orders->first(fn ($order) =>
                                    (int) $order->buyer_id === (int) Auth::id()
                                    || (int) $order->seller_id === (int) Auth::id()
                                )
                                : null;
                        @endphp

                        {{-- Dynamic action based on guest, buyer and seller permissions. --}}
                        <div class="mb-3">
                            @if(!Auth::check())
                                <a href="{{ route('login') }}" class="btn btn-success btn-lg w-100 mb-2">
                                    Log In to Hire
                                </a>
                                <div class="text-center">
                                    <small class="text-muted">Guests may browse gigs. A buyer account is required to hire.</small>
                                </div>
                            @elseif(Auth::user()->role === 'seller' && (int) Auth::id() === (int) $gig->user_id)
                                <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                    <i class="bi bi-person-x-fill me-1"></i> This Is Your Own Gig
                                </button>
                                <div class="text-center">
                                    <small class="text-muted">Sellers cannot submit hire requests for their own gigs.</small>
                                </div>
                            @elseif(Auth::user()->role === 'seller')
                                <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                    Buyer Account Required
                                </button>
                                <div class="text-center">
                                    <small class="text-muted">Seller accounts cannot submit hire requests.</small>
                                </div>
                            @elseif($viewerOrder)
                                <a href="{{ route('orders.show', $viewerOrder) }}" class="btn btn-success btn-lg w-100 mb-2">
                                    Open Order & Deliverable
                                </a>
                            @elseif($gig->isAvailable())
                                <!-- ACTIVE: Hiring Available -->
                                <a href="{{ route('hire-requests.create', $gig) }}" class="btn btn-success btn-lg w-100 mb-2">
                                    <i class="bi bi-send-fill me-1"></i> Submit Hire Request
                                </a>
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-speedometer2 me-1"></i> {{ $gig->activeOrdersCount() }} / {{ $gig->max_weekly_orders }} slots filled this week
                                    </small>
                                </div>
                            @else
                                <!-- LOCKED: Exam Mode or Capacity Reached -->
                                <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                    <i class="bi bi-lock-fill me-1"></i> 
                                    @if(!$gig->is_accepting_orders)
                                        Paused (Exam Mode / Holiday)
                                    @else
                                        Fully Booked This Week
                                    @endif
                                </button>
                                <div class="text-center">
                                    <span class="badge bg-warning text-dark px-3 py-1">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                                        @if(!$gig->is_accepting_orders)
                                            Orders currently paused by seller
                                        @else
                                            Capacity Reached ({{ $gig->activeOrdersCount() }}/{{ $gig->max_weekly_orders }})
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>

                        @auth
                            @if(Auth::user()->role === 'seller' && (int) Auth::id() === (int) $gig->user_id)
                                <a href="{{ route('hire-requests.incoming') }}" class="btn btn-outline-success w-100 mb-2">
                                    Review Hire Requests
                                </a>

                                <a href="{{ route('orders.index') }}" class="btn btn-outline-primary w-100 mb-4">View All Orders</a>

                                <div class="border-top pt-3">
                                    <h6 class="fw-bold text-muted mb-3">Manage Listing</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('gigs.edit', $gig->id) }}" class="btn btn-warning">
                                            ✏️ Edit Gig
                                        </a>

                                        <form action="{{ route('gigs.destroy', $gig->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this gig? This action will permanently remove all files and cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger w-100">
                                                🗑️ Delete Gig
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endauth

                    </div>
                </div>
            </div>
        </div>

        {{-- Completed-order Reviews and Ratings --}}
        @php
            $feedbackOrders = $gig->orders
                ->where('status', 'completed')
                ->filter(fn ($order) => $order->review || $order->rating);
        @endphp

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0">Customer Reviews</h4>
            </div>

            <div class="card-body p-4">
                @forelse($feedbackOrders as $feedbackOrder)
                    <div class="{{ !$loop->last ? 'border-bottom pb-3 mb-3' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $feedbackOrder->buyer->name ?? 'Buyer' }}</strong>

                                @if($feedbackOrder->rating)
                                    <div class="text-warning fs-5">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= $feedbackOrder->rating->rating ? '★' : '☆' }}
                                        @endfor

                                        <small class="text-muted fs-6">
                                            ({{ $feedbackOrder->rating->rating }}/5)
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($feedbackOrder->review)
                            <p class="mt-2 mb-0">
                                {{ $feedbackOrder->review->review_text }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">
                        No reviews or ratings have been submitted for this gig yet.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
