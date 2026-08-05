@extends('layouts.app')

@section('title', 'My Seller Dashboard - GigEx')

@section('content')
<div class="container py-4">
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Seller Dashboard</h2>
            <p class="text-muted mb-0">Manage, archive, and monitor your active listings.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-success">
                <i class="bi bi-box-seam me-1"></i> View Orders
            </a>

            <a href="{{ route('gigs.create') }}" class="btn btn-primary d-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i> Create New Gig
            </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        @forelse($gigs as $gig)
            @php
                $gigStatus = strtolower($gig->status ?? 'active');
                $imagePath = $gig->image ?? $gig->cover_image ?? null;
            @endphp

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    {{-- Gig Cover Image --}}
                    @if($imagePath)
                        <img
                            src="{{ asset('storage/' . $imagePath) }}"
                            class="card-img-top"
                            style="height: 190px; object-fit: cover;"
                            alt="{{ $gig->title }}"
                        >
                    @else
                        <div
                            class="bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center"
                            style="height: 190px;"
                        >
                            <div class="text-center">
                                <i class="bi bi-image fs-1"></i>
                                <div>No Cover Image</div>
                            </div>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge bg-info text-dark">
                                {{ $gig->category }}
                            </span>

                            @if($gigStatus === 'archived')
                                <span class="badge bg-secondary">Archived</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </div>

                        <h5 class="card-title fw-bold">{{ $gig->title }}</h5>
                        <p class="card-text text-muted">
                            {{ \Illuminate\Support\Str::limit($gig->description, 100) }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <strong class="text-success fs-5">
                                ${{ number_format($gig->price, 2) }}
                            </strong>

                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                {{ $gig->delivery_time }}
                                {{ \Illuminate\Support\Str::plural('day', $gig->delivery_time) }}
                            </small>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('gigs.show', $gig->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View Details
                            </a>

                            <div class="d-flex gap-2">
                                <a href="{{ route('gigs.edit', $gig->id) }}" class="btn btn-outline-warning flex-fill">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </a>

                                @if($gigStatus === 'archived')
                                    <form action="{{ route('gigs.restore', $gig->id) }}" method="POST" class="flex-fill">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success w-100">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                @else
                                    <form
                                        action="{{ route('gigs.archive', $gig->id) }}"
                                        method="POST"
                                        class="flex-fill"
                                        onsubmit="return confirm('Archive this gig? It will no longer appear in public searches.');"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-secondary w-100">
                                            <i class="bi bi-archive me-1"></i> Archive
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <form
                                action="{{ route('gigs.destroy', $gig->id) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this gig permanently?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-briefcase fs-1 text-muted"></i>
                        <h4 class="mt-3">No gigs created yet</h4>
                        <p class="text-muted">Create your first service listing to start receiving hire requests.</p>
                        <a href="{{ route('gigs.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Create Your First Gig
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination, when the controller uses paginate() --}}
    @if(method_exists($gigs, 'links'))
        <div class="mt-4">
            {{ $gigs->links() }}
        </div>
    @endif
</div>
@endsection
