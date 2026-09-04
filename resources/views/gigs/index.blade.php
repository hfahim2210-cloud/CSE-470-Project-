@extends('layouts.app')

@section('title', 'My Seller Dashboard - GigEx')

@section('content')

{{-- Header Section --}}
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Seller Dashboard</h2>
        <p class="text-muted mb-0">Manage, archive, and monitor your active listings.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('seller.analytics.export') }}" class="btn btn-outline-dark d-flex align-items-center">
            <i class="bi bi-download me-1"></i> Earnings CSV
        </a>

        <a href="{{ route('hire-requests.incoming') }}" class="btn btn-outline-primary d-flex align-items-center">
            <i class="bi bi-inbox-fill me-1"></i> Hire Requests
        </a>

        <a href="{{ route('orders.index') }}" class="btn btn-outline-success d-flex align-items-center">
            <i class="bi bi-box-seam me-1"></i> View Orders
        </a>

        <a href="{{ route('gigs.create') }}" class="btn btn-primary d-flex align-items-center">
            <i class="bi bi-plus-lg me-1"></i> Create New Gig
        </a>
    </div>
</div>

{{-- Financial and order analytics --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold">Active Orders</div>
                <div class="display-6 fw-bold text-primary">{{ $activeOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold">Completed</div>
                <div class="display-6 fw-bold text-success">{{ $completedOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold">Completion Rate</div>
                <div class="display-6 fw-bold">{{ number_format($completionRate, 1) }}%</div>
                <small class="text-muted">{{ $completedOrders }} of {{ $totalOrders }} orders</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold">This Month</div>
                <div class="h2 fw-bold text-success mb-0">${{ number_format($monthlyEarnings, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white">
            <div class="card-body">
                <div class="small text-white-50 text-uppercase fw-semibold">Total Earnings</div>
                <div class="h2 fw-bold mb-0">${{ number_format($totalEarnings, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Six-Month Earnings</h3>
                <p class="small text-muted mb-0">Revenue from completed orders only.</p>
            </div>
            <div class="card-body px-4">
                @php($highestMonthlyEarning = max(1, (float) $monthlyBreakdown->max('earnings')))
                @foreach($monthlyBreakdown as $month)
                    <div class="row align-items-center g-2 mb-3">
                        <div class="col-3 small fw-semibold">{{ $month['label'] }}</div>
                        <div class="col">
                            <div class="progress" role="progressbar" aria-label="{{ $month['label'] }} earnings" aria-valuenow="{{ $month['earnings'] }}" aria-valuemin="0" aria-valuemax="{{ $highestMonthlyEarning }}" style="height: 12px;">
                                <div class="progress-bar bg-success" style="width: {{ ($month['earnings'] / $highestMonthlyEarning) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="col-3 text-end">
                            <strong>${{ number_format($month['earnings'], 2) }}</strong>
                            <div class="small text-muted">{{ $month['orders'] }} {{ Str::plural('order', $month['orders']) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h3 class="h5 mb-1">Recent Payout Activity</h3>
                <p class="small text-muted mb-0">Your latest completed orders.</p>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentCompletedOrders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="list-group-item list-group-item-action px-4 py-3">
                        <div class="d-flex justify-content-between gap-3">
                            <div class="text-truncate">
                                <div class="fw-semibold text-truncate">{{ $order->gig?->title ?? 'Deleted gig' }}</div>
                                <small class="text-muted">{{ $order->buyer?->name ?? 'Buyer' }} · {{ optional($order->completed_at)->format('d M Y') }}</small>
                            </div>
                            <strong class="text-success">+${{ number_format($order->agreed_price, 2) }}</strong>
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted px-4 py-5">Completed-order earnings will appear here.</div>
                @endforelse
            </div>
        </div>
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
                            <img src="{{ route('media.show', ['path' => $gig->image]) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $gig->title }}">
                        @else
                            <div class="bg-secondary text-white text-center py-5">No Cover Image</div>
                        @endif

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-info text-dark">{{ $gig->category }}</span>
                                
                                {{-- Availability Status Badges --}}
                                @if(!$gig->is_accepting_orders)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-pause-circle-fill me-1"></i> Paused (Exam Mode)
                                    </span>
                                @elseif($gig->activeOrdersCount() >= $gig->max_weekly_orders)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-slash-circle-fill me-1"></i> Fully Booked
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill me-1"></i> Accepting Orders
                                    </span>
                                @endif
                            </div>

                            <h5 class="card-title fw-bold text-truncate mb-1">{{ $gig->title }}</h5>
                            
                            {{-- Workload Counter --}}
                            <div class="text-muted small mb-2">
                                <i class="bi bi-speedometer2 me-1"></i> {{ $gig->activeOrdersCount() }} / {{ $gig->max_weekly_orders }} active slots filled
                            </div>

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
                            <img src="{{ route('media.show', ['path' => $gig->image]) }}" class="card-img-top" style="height: 180px; object-fit: cover; filter: grayscale(50%);" alt="{{ $gig->title }}">
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
