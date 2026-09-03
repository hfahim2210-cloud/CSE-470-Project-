<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/gigex.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
@include('partials.navigation')

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Orders</h2>
            <p class="text-muted mb-0">Submit and approve final deliverables here.</p>
        </div>
        <a href="{{ Auth::user()->role === 'seller' ? route('gigs.index') : route('gigs.marketplace') }}" class="btn btn-outline-secondary">
            {{ Auth::user()->role === 'seller' ? 'Back to Dashboard' : 'Back to Marketplace' }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        @forelse($orders as $order)
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted">Order #{{ $order->id }}</small>
                                <h5 class="mt-1">{{ $order->gig->title }}</h5>
                            </div>
                            <span class="badge bg-secondary text-uppercase">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>

                        <p class="mb-1"><strong>Seller:</strong> {{ $order->seller->name }}</p>
                        <p class="mb-1"><strong>Buyer:</strong> {{ $order->buyer->name }}</p>
                        <p class="mb-3"><strong>Price:</strong> ${{ number_format($order->agreed_price, 2) }}</p>

                        <a href="{{ route('orders.show', $order) }}" class="btn btn-primary w-100">
                            Open Order
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    No orders exist yet. Accept a pending hire request to create the first order.
                </div>
            </div>
        @endforelse
    </div>
</div>
</body>
</html>
