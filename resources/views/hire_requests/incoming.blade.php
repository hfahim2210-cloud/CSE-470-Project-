<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire Requests - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Hire Requests</h1>
            <p class="text-muted mb-0">Seller: {{ $seller->name }}</p>
        </div>
        <a href="{{ route('gigs.index') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th>Buyer</th>
                        <th>Gig</th>
                        <th>Message</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($hireRequests as $hireRequest)
                        <tr>
                            <td>
                                {{ $hireRequest->buyer->name }}
                                <div class="small text-muted">{{ $hireRequest->buyer->email }}</div>
                            </td>
                            <td>{{ $hireRequest->gig->title }}</td>
                            <td style="min-width: 260px;">{{ $hireRequest->message }}</td>
                            <td>{{ $hireRequest->proposed_deadline->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $hireRequest->status === 'accepted' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($hireRequest->status) }}
                                </span>
                            </td>
                            <td>
                                @if($hireRequest->status === 'pending')
                                    <form method="POST" action="{{ route('hire-requests.accept', $hireRequest) }}" onsubmit="return confirm('Accept this hire request and create an order?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">Accept Request</button>
                                    </form>
                                @elseif($hireRequest->order)
                                    <a href="{{ route('orders.show', $hireRequest->order) }}" class="btn btn-outline-primary btn-sm">Open Order</a>
                                @else
                                    <span class="text-muted">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No hire requests have been submitted yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
