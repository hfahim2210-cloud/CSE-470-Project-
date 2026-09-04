<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }} - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/gigex.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
@include('partials.navigation')

<div class="container py-5" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">&larr; All Orders</a>
        <div class="d-flex align-items-center gap-2">
            @if(Auth::user()->role === 'seller' && (int) Auth::id() === (int) $order->seller_id)
                <a href="{{ route('orders.status', $order) }}" class="btn btn-primary btn-sm">
                    Update Order Status
                </a>
            @endif
            <span class="badge bg-dark fs-6 text-uppercase">
                {{ str_replace('_', ' ', $order->status) }}
            </span>
        </div>
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
            <div class="border-top mt-3 pt-3">
                <div><strong>Package:</strong> {{ data_get($order->selected_tier, 'title', 'Base Service') }}</div>
                @if(count($order->selected_addons ?? []))
                    <div class="mt-2">
                        <strong>Add-ons:</strong>
                        @foreach($order->selected_addons as $addon)
                            <span class="badge bg-light text-dark border me-1">
                                {{ data_get($addon, 'name') }} (+${{ number_format((float) data_get($addon, 'price'), 2) }})
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="small text-muted mt-1">No paid add-ons selected.</div>
                @endif
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
                        <a href="{{ route('media.show', ['path' => $order->deliverable->file_path]) }}" target="_blank">
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


    @if($order->revisionRequests->isNotEmpty())
        <div class="card shadow-sm border-warning mb-4">
            <div class="card-header bg-warning-subtle py-3">
                <h3 class="h5 mb-0">Revision Requests</h3>
            </div>

            <div class="card-body p-4">
                @foreach($order->revisionRequests as $revision)
                    <div class="{{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <strong>Revision request #{{ $revision->id }}</strong>

                            <span class="badge {{ $revision->status === 'open' ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ ucfirst($revision->status) }}
                            </span>
                        </div>

                        <div class="bg-light rounded p-3">
                            {{ $revision->request_text }}
                        </div>

                        <small class="text-muted d-block mt-2">
                            Requested {{ optional($revision->requested_at)->format('F j, Y \a\t g:i A') }}

                            @if($revision->resolved_at)
                                &middot; Resolved {{ $revision->resolved_at->format('F j, Y \a\t g:i A') }}
                            @endif
                        </small>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($order->status !== 'completed'
        && Auth::user()->role === 'seller'
        && (int) Auth::id() === (int) $order->seller_id)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h3 class="h5 mb-0">Submit Final Deliverable</h3>
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
        && Auth::user()->role === 'buyer'
        && (int) Auth::id() === (int) $order->buyer_id)
        <div class="card shadow-sm border-success mb-4">
            <div class="card-header bg-success text-white py-3">
                <h3 class="h5 mb-0">Approve Final Deliverable</h3>
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

    @if($order->status === 'under_review'
        && $order->deliverable?->status === 'submitted'
        && Auth::user()->role === 'buyer'
        && (int) Auth::id() === (int) $order->buyer_id)
        <div class="card shadow-sm border-warning mb-4">
            <div class="card-header bg-warning py-3">
                <h3 class="h5 mb-0">Request Revisions</h3>
            </div>

            <div class="card-body p-4">
                <p class="text-muted">
                    If the submitted work needs changes, describe exactly what the seller should revise.
                    The order will return to <strong>Revision Requested</strong> instead of being completed.
                </p>

                <form
                    action="{{ route('orders.deliverable.request-revision', $order) }}"
                    method="POST"
                    onsubmit="return confirm('Send this revision request to the seller?');"
                >
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="revision_request" class="form-label fw-bold">
                            Changes needed
                        </label>

                        <textarea
                            name="revision_request"
                            id="revision_request"
                            rows="5"
                            class="form-control"
                            minlength="5"
                            maxlength="2000"
                            required
                            placeholder="Example: Please correct the final two pages and replace the low-resolution image."
                        >{{ old('revision_request') }}</textarea>

                        <small class="text-muted">
                            The seller will see this message and can resubmit the corrected deliverable.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        Request Revisions
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if($order->status === 'revision_requested'
        && Auth::user()->role === 'seller'
        && (int) Auth::id() === (int) $order->seller_id)
        <div class="alert alert-warning shadow-sm">
            <h4 class="alert-heading">Revision requested</h4>
            <p class="mb-0">
                Review the buyer's requested changes above, then use
                <strong>Resubmit Final Deliverable</strong> to send the updated work.
            </p>
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

        {{-- Existing Feedback --}}
        @if($order->review || $order->rating)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h3 class="h5 mb-0">Buyer Feedback</h3>
                </div>
                <div class="card-body p-4">
                    @if($order->rating)
                        <p class="mb-3">
                            <strong>Rating:</strong>
                            <span class="text-warning fs-5">
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= $order->rating->rating ? '★' : '☆' }}
                                @endfor
                            </span>
                            ({{ $order->rating->rating }}/5)
                        </p>
                    @endif

                    @if($order->review)
                        <p class="mb-1"><strong>Text Review:</strong></p>
                        <div class="bg-light rounded p-3">
                            {{ $order->review->review_text }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Only the buyer can leave/update feedback once the order is completed. --}}
        @if(Auth::user()->role === 'buyer' && (int) Auth::id() === (int) $order->buyer_id)
            <div class="row g-4">

                {{-- Leave Text Review --}}
                <div class="col-md-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="h5 mb-0">Leave Text Review</h3>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('orders.review.store', $order) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="review_text" class="form-label fw-bold">
                                        Write your review
                                    </label>

                                    <textarea
                                        name="review_text"
                                        id="review_text"
                                        rows="5"
                                        class="form-control"
                                        maxlength="2000"
                                        required
                                        placeholder="Describe your experience with this service..."
                                    >{{ old('review_text', $order->review?->review_text) }}</textarea>

                                    <small class="text-muted">
                                        Review can only be submitted after the order is completed.
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    {{ $order->review ? 'Update Text Review' : 'Submit Text Review' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Leave Star Rating --}}
                <div class="col-md-5">
                    <div class="card shadow-sm border-warning h-100">
                        <div class="card-header bg-warning py-3">
                            <h3 class="h5 mb-0">Leave Star Rating</h3>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('orders.rating.store', $order) }}" method="POST">
                                @csrf

                                <span class="form-label fw-bold d-block">
                                    Rate this service
                                </span>

                                @php($selectedRating = (int) old('rating', $order->rating?->rating))
                                <fieldset class="mb-3">
                                    <legend class="visually-hidden">Choose a rating from 1 to 5 stars</legend>
                                    <div class="star-rating" role="radiogroup" aria-label="Service rating">
                                        @for($star = 5; $star >= 1; $star--)
                                            <input
                                                type="radio"
                                                name="rating"
                                                id="rating-{{ $order->id }}-{{ $star }}"
                                                value="{{ $star }}"
                                                {{ $selectedRating === $star ? 'checked' : '' }}
                                                required
                                            >
                                            <label
                                                for="rating-{{ $order->id }}-{{ $star }}"
                                                title="{{ $star }} {{ Str::plural('star', $star) }}"
                                                aria-label="{{ $star }} {{ Str::plural('star', $star) }}"
                                            >★</label>
                                        @endfor
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <span id="rating-value">{{ $selectedRating ?: 0 }}</span> of 5 selected
                                    </div>
                                </fieldset>

                                <button type="submit" class="btn btn-warning">
                                    {{ $order->rating ? 'Update Star Rating' : 'Submit Star Rating' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    @endif
</div>
<style>
    .star-rating {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: .2rem;
        padding: .35rem .6rem;
        border: 1px solid #dee2e6;
        border-radius: 999px;
        background: #fff;
    }
    .star-rating input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .star-rating label {
        color: #cbd0d6;
        cursor: pointer;
        font-size: 2.25rem;
        line-height: 1;
        transition: color .15s ease, transform .15s ease;
    }
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        color: #ffc107;
    }
    .star-rating label:hover { transform: scale(1.12); }
    .star-rating input:focus-visible + label {
        outline: 3px solid rgba(13, 110, 253, .35);
        outline-offset: 2px;
        border-radius: .2rem;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const output = document.getElementById('rating-value');
    document.querySelectorAll('.star-rating input[name="rating"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            output.textContent = radio.value;
        });
    });
});
</script>
</body>
</html>
