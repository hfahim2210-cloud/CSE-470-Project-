<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 850px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Update Order Status</h1>
            <p class="text-muted mb-0">Order #{{ $order->id }} — {{ $order->gig->title }}</p>
        </div>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">Back to Order</a>
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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-4"><strong>Seller:</strong><br>{{ $order->seller->name }}</div>
                <div class="col-md-4"><strong>Buyer:</strong><br>{{ $order->buyer->name }}</div>
                <div class="col-md-4">
                    <strong>Current status:</strong><br>
                    <span class="badge bg-dark text-uppercase mt-1">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>
            </div>

            @if(count($nextStatuses) > 0)
                <form method="POST" action="{{ route('orders.status.update', $order) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Next status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="">Select the next status</option>
                            @foreach($nextStatuses as $status)
                                <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label fw-bold">Status note (optional)</label>
                        <textarea
                            name="note"
                            id="note"
                            rows="3"
                            maxlength="500"
                            class="form-control"
                            placeholder="Add a short progress note for this status change."
                        >{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" onclick="return confirm('Update this order status?');">
                        Update Status
                    </button>
                </form>
            @else
                <div class="alert alert-info mb-0">
                    There is no seller-controlled status change available from
                    <strong>{{ ucwords(str_replace('_', ' ', $order->status)) }}</strong>.
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Status History</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Previous</th>
                        <th>New</th>
                        <th>Changed by</th>
                        <th>Note</th>
                        <th>Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($statusHistory as $history)
                        <tr>
                            <td>{{ $history->previous_status ? ucwords(str_replace('_', ' ', $history->previous_status)) : '—' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $history->new_status)) }}</td>
                            <td>{{ $history->changedBy?->name ?? 'System' }}</td>
                            <td>{{ $history->note ?: '—' }}</td>
                            <td>{{ optional($history->changed_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No status history has been recorded yet.</td>
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
