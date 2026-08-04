<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Hire Requests - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">Incoming Hire Requests</h2>
            <p class="text-muted mb-0">Temporary seller: {{ $seller->name }}</p>
        </div>

        <a href="{{ route('gigs.index') }}" class="btn btn-outline-secondary">Back to My Gigs</a>
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
                        <th>Submitted</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($hireRequests as $hireRequest)
                        <tr>
                            <td>
                                <strong>{{ $hireRequest->buyer->name }}</strong>
                                <div class="small text-muted">{{ $hireRequest->buyer->email }}</div>
                            </td>
                            <td>
                                <a href="{{ route('gigs.show', $hireRequest->gig) }}">
                                    {{ $hireRequest->gig->title }}
                                </a>
                            </td>
                            <td style="min-width: 300px; white-space: pre-line;">{{ $hireRequest->message }}</td>
                            <td>{{ $hireRequest->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                @if($hireRequest->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($hireRequest->status === 'accepted')
                                    <span class="badge bg-success">Accepted</span>
                                @else
                                    <span class="badge bg-danger">Declined</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No incoming hire requests yet.
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
