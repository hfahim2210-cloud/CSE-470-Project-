@extends('layouts.app')

@section('title', 'My Seller Dashboard - GigEx')

@section('content')

{{-- Header Section --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Seller Dashboard</h2>
        <p class="text-muted mb-0">Manage, archive, and monitor your active listings.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('orders.index') }}" class="btn btn-outline-success d-flex align-items-center">
            <i class="bi bi-box-seam me-1"></i> View Orders
        </a>

        <a href="{{ route('gigs.create') }}" class="btn btn-primary d-flex align-items-center">
            <i class="bi bi-plus-lg me-1"></i> Create New Gig
        </a>
    </div>
</div>

{{-- Navigation Tabs --}}
<ul class="nav nav-tabs mb-4 border-bottom" id="gigTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold px-4" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-gigs" type="button" role="tab">
            <i class="bi bi-check-circle-fill text-success me-2"></i>Active Gigs ({{ $activeGigs->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-secondary fw-bold px-4" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived-gigs" type="button" role="tab">
            <i class="bi bi-archive-fill text-warning me-2"></i>Archived Gigs ({{ $archivedGigs->count() }})
        </button>
    </li>
</ul>

{{-- Tab Content Areas --}}
<div class="tab-content" id="gigTabsContent">

    {{-- ================= ACTIVE GIGS TAB ================= --}}
    <div class="tab-pane fade show active" id="active-gigs" role="tabpanel" aria-labelledby="active-tab">
        <div class="row">
            @forelse($activeGigs as $gig)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        @if($gig->image)
                            <img src="{{ asset('storage/' . $gig->image) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $gig->title }}">
                        @else
                            <div class="bg-secondary text-white text-center py-5">No Cover Image</div>
                        @endif

                        <div class="card-body">
                            <span class="badge bg-info text-dark mb-2">{{ $gig->category }}</span>
                            <h5 class="card-title fw-bold text-truncate">{{ $gig->title }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($gig->description, 80) }}</p>
                        </div>

                        <div class="card-footer bg-white border-top-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong class="text-success fs-5">${{ number_format($gig->price, 2) }}</strong>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $gig->delivery_time }} {{ Str::plural('Day', $gig->delivery_time) }}</small>
                            </div>
                            
                            <div class="d-flex gap-1 justify-content-between">
                                <a href="{{ route('gigs.show', $gig->id) }}" class="btn btn-outline-primary btn-sm flex-grow-1">View Details</a>
                                <a href="{{ route('gigs.edit', $gig->id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                
                                {{-- Archive Button --}}
                                <form action="{{ route('gigs.archive', $gig->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-warning btn-sm">Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                    </div>
                    <p class="text-muted fs-5">No active gigs found.</p>
                    <a href="{{ route('gigs.create') }}" class="btn btn-outline-primary">Create Your First Gig</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ================= ARCHIVED GIGS TAB ================= --}}
    <div class="tab-pane fade" id="archived-gigs" role="tabpanel" aria-labelledby="archived-tab">
        <div class="row">
            @forelse($archivedGigs as $gig)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 opacity-75">
                        @if($gig->image)
                            <img src="{{ asset('storage/' . $gig->image) }}" class="card-img-top" style="height: 180px; object-fit: cover; filter: grayscale(50%);" alt="{{ $gig->title }}">
                        @else
                            <div class="bg-secondary text-white text-center py-5">No Cover Image</div>
                        @endif

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary">{{ $gig->category }}</span>
                                <span class="badge bg-warning text-dark">Archived</span>
                            </div>
                            <h5 class="card-title fw-bold text-truncate">{{ $gig->title }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($gig->description, 80) }}</p>
                        </div>

                        <div class="card-footer bg-white border-top-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong class="text-secondary fs-5">${{ number_format($gig->price, 2) }}</strong>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $gig->delivery_time }} {{ Str::plural('Day', $gig->delivery_time) }}</small>
                            </div>
                            
                            <div class="d-flex gap-2 justify-content-between">
                                {{-- Restore Button --}}
                                <form action="{{ route('gigs.restore', $gig->id) }}" method="POST" class="d-inline flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm w-100">Restore</button>
                                </form>

                                {{-- Permanent Delete Button --}}
                                <form action="{{ route('gigs.destroy', $gig->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Permanently delete this gig? This cannot be undone.')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-archive fs-1 text-muted"></i>
                    </div>
                    <p class="text-muted fs-5">No archived gigs.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection