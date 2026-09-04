<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Hire Requests - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/gigex.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
@include('partials.navigation')

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Incoming Hire Requests</h1>
            <p class="text-muted mb-0">
                Review requests sent to {{ $seller->name }}'s gigs. Pending requests appear first.
            </p>
        </div>
        <a href="{{ route('gigs.index') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th>Buyer</th>
                        <th>Gig</th>
                        <th>Package & Add-ons</th>
                        <th>Quote</th>
                        <th>Message</th>
                        <th>Deadline</th>
                        <th>Received</th>
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
                            <td style="min-width: 190px;">
                                <strong>{{ data_get($hireRequest->selected_tier, 'title', 'Base Service') }}</strong>
                                @if(count($hireRequest->selected_addons ?? []))
                                    <div class="small text-muted mt-1">
                                        + {{ collect($hireRequest->selected_addons)->pluck('name')->implode(', ') }}
                                    </div>
                                @else
                                    <div class="small text-muted">No add-ons</div>
                                @endif
                            </td>
                            <td class="text-nowrap fw-bold text-success">
                                ${{ number_format($hireRequest->quoted_price ?? $hireRequest->gig->price, 2) }}
                            </td>
                            <td style="min-width: 260px;">{{ $hireRequest->message }}</td>
                            <td>{{ $hireRequest->proposed_deadline->format('d M Y') }}</td>
                            <td>{{ $hireRequest->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <span class="badge {{ $hireRequest->status === 'accepted' ? 'bg-success' : ($hireRequest->status === 'declined' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($hireRequest->status) }}
                                </span>
                            </td>
                            <td>
                                @if($hireRequest->status === 'pending')
                                    <div class="d-flex flex-column gap-2" style="min-width: 190px;">
                                        <form method="POST" action="{{ route('hire-requests.accept', $hireRequest) }}" onsubmit="return confirm('Accept this hire request and create an order?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm w-100">Accept Request</button>
                                        </form>

                                        <form method="POST" action="{{ route('hire-requests.decline', $hireRequest) }}" onsubmit="return confirm('Decline this hire request?');">
                                            @csrf
                                            @method('PATCH')
                                            <input
                                                type="text"
                                                name="decline_reason"
                                                class="form-control form-control-sm mb-2"
                                                maxlength="1000"
                                                placeholder="Optional decline reason"
                                            >
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Decline Request</button>
                                        </form>
                                    </div>
                                @elseif($hireRequest->order)
                                    <a href="{{ route('orders.show', $hireRequest->order) }}" class="btn btn-outline-primary btn-sm">Open Order</a>
                                @elseif($hireRequest->status === 'declined')
                                    <div class="small text-danger" style="min-width: 180px;">
                                        <strong>Declined</strong>
                                        @if($hireRequest->decline_reason)
                                            <div class="text-muted mt-1">{{ $hireRequest->decline_reason }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                No incoming hire requests have been submitted yet.
                            </td>
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
