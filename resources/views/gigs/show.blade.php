<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $gig->title }} - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    <div class="container">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <a href="{{ route('gigs.index') }}" class="btn btn-outline-secondary mb-4">&leftarrow; Back to All Gigs</a>

        <div class="row">
            {{-- Left Column: Gig Details & Media --}}
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 overflow-hidden">
                    {{-- Uploaded Cover Image Display --}}
                    @if($gig->image)
                        <img src="{{ Storage::url($gig->image) }}" class="card-img-top" alt="{{ $gig->title }}" style="max-height: 420px; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white text-center py-5">
                            <p class="mb-0 fs-5">📷 No Cover Image Uploaded</p>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-info text-dark">{{ $gig->category }}</span>
                            {{-- Relative Time (e.g. 2 hours ago) --}}
                            <small class="text-muted">📅 Posted {{ $gig->created_at->diffForHumans() }}</small>
                        </div>

                        <h2 class="card-title h3 mb-2">{{ $gig->title }}</h2>
                        
                        {{-- Exact Date & Time Published --}}
                        <p class="text-muted small border-bottom pb-3 mb-3">
                            Published on <strong>{{ $gig->created_at->format('F j, Y \a\t g:i A') }}</strong>
                        </p>

                        <h5 class="fw-bold">About This Gig</h5>
                        <p class="card-text text-secondary mb-4" style="white-space: pre-line; line-height: 1.7;">{{ $gig->description }}</p>

                        {{-- Portfolio Work Samples (if any exist) --}}
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
                                                    <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View PDF Sample</a>
                                                </div>
                                            @else
                                                <a href="{{ Storage::url($item->file_path) }}" target="_blank">
                                                    <img src="{{ Storage::url($item->file_path) }}" class="img-fluid rounded" style="height: 140px; width: 100%; object-fit: cover;" alt="Portfolio Sample">
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

            {{-- Right Column: Pricing, Order Action, & Seller Controls --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Price</span>
                            <span class="fs-2 fw-bold text-success">${{ number_format($gig->price, 2) }}</span>
                        </div>

                        <div class="mb-3">
                            <strong>⏱️ Delivery Time:</strong>
                            <span>{{ $gig->delivery_time }} {{ Str::plural('Day', $gig->delivery_time) }}</span>
                        </div>

                        <hr>

                        <a href="{{ route('hire-requests.create', $gig) }}" class="btn btn-success btn-lg w-100 mb-2">Request to Hire</a>
                        <button class="btn btn-outline-primary w-100 mb-4">Contact Seller</button>

                        {{-- Seller Actions: Edit & Delete --}}
                        <div class="border-top pt-3">
                            <h6 class="fw-bold text-muted mb-3">Manage Listing</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('hire-requests.incoming') }}" class="btn btn-outline-success">
                                    📥 View Incoming Hire Requests
                                </a>

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

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>