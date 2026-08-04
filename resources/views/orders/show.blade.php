<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }} - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">&larr; All Orders</a>
        <span class="badge bg-dark fs-6 text-uppercase">
            {{ str_replace('_', ' ', $order->status) }}
        </span>
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
            <small class="text-muted">Order #{{ $order->id }}</small>
            <h2 class="h4 mt-1">{{ $order->gig->title }}</h2>
            <div class="row mt-3">
                <div class="col-md-4"><strong>Seller:</strong> {{ $order->seller->name }}</div>
                <div class="col-md-4"><strong>Buyer:</strong> {{ $order->buyer->name }}</div>
                <div class="col-md-4"><strong>Price:</strong> ${{ number_format($order->agreed_price, 2) }}</div>
            </div>
        </div>
    </div>

    @if($order->deliverable)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h3 class="h5 mb-0">Submitted Deliverable</h3>
            </div>
            <div class="card-body p-4">
                <p>
                    <strong>Status:</strong>
                    <span class="badge {{ $order->deliverable->status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($order->deliverable->status) }}
                    </span>
                </p>

                @if($order->deliverable->file_path)
                    <p>
                        <strong>File:</strong>
                        <a href="{{ Storage::url($order->deliverable->file_path) }}" target="_blank">
                            {{ $order->deliverable->file_name ?? 'Open submitted file' }}
                        </a>
                    </p>
                @endif

                @if($order->deliverable->submission_link)
                    <p>
                        <strong>Link:</strong>
                        <a href="{{ $order->deliverable->submission_link }}" target="_blank" rel="noopener noreferrer">
                            Open submitted link
                        </a>
                    </p>
                @endif

                @if($order->deliverable->note)
                    <p class="mb-1"><strong>Seller note:</strong></p>
                    <div class="bg-light rounded p-3">{{ $order->deliverable->note }}</div>
                @endif

                <small class="text-muted d-block mt-3">
                    Submitted {{ optional($order->deliverable->submitted_at)->format('F j, Y \a\t g:i A') }}
                </small>
            </div>
        </div>
    @endif

    @if($order->status !== 'completed' && (!Auth::check() || Auth::id() === $order->seller_id))
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h3 class="h5 mb-0">Feature 1 — Submit Final Deliverable</h3>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('orders.deliverable.store', $order) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="deliverable_file" class="form-label fw-bold">Upload final file</label>
                        <input
                            type="file"
                            name="deliverable_file"
                            id="deliverable_file"
                            class="form-control"
                            accept=".pdf,.doc,.docx,.zip,.png,.jpg,.jpeg,.txt"
                        >
                        <small class="text-muted">Maximum file size: 10 MB.</small>
                    </div>

                    <div class="mb-3">
                        <label for="submission_link" class="form-label fw-bold">Or submit a link</label>
                        <input
                            type="url"
                            name="submission_link"
                            id="submission_link"
                            class="form-control"
                            value="{{ old('submission_link', $order->deliverable?->submission_link) }}"
                            placeholder="https://drive.google.com/..."
                        >
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label fw-bold">Message to buyer</label>
                        <textarea name="note" id="note" rows="4" class="form-control" placeholder="Describe the completed work...">{{ old('note', $order->deliverable?->note) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ $order->deliverable ? 'Resubmit Final Deliverable' : 'Submit Final Deliverable' }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if($order->status === 'under_review'
        && $order->deliverable?->status === 'submitted'
        && (!Auth::check() || Auth::id() === $order->buyer_id))
        <div class="card shadow-sm border-success mb-4">
            <div class="card-header bg-success text-white py-3">
                <h3 class="h5 mb-0">Feature 2 — Approve Final Deliverable</h3>
            </div>
            <div class="card-body p-4">
                <p>Review the submitted file or link above. Approval will mark this order as completed.</p>

                <form action="{{ route('orders.deliverable.approve', $order) }}" method="POST" onsubmit="return confirm('Approve this deliverable and complete the order?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-lg">
                        Approve Final Deliverable
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if($order->status === 'completed')
        <div class="alert alert-success shadow-sm">
            <h4 class="alert-heading">Order completed</h4>
            <p class="mb-0">
                The buyer approved the final deliverable
                {{ optional($order->completed_at)->diffForHumans() }}.
            </p>
        </div>
    @endif
</div>
</body>
</html>
