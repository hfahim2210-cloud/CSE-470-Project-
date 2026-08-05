<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Hire Request - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 760px;">
    <a href="{{ route('gigs.show', $gig) }}" class="btn btn-outline-secondary mb-4">
        &larr; Back to Gig
    </a>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">Submit Hire Request</h1>

            <div class="bg-light rounded p-3 mb-4">
                <h2 class="h5">{{ $gig->title }}</h2>
                <p class="mb-1"><strong>Seller:</strong> {{ $gig->user->name }}</p>
                <p class="mb-1"><strong>Price:</strong> ${{ number_format($gig->price, 2) }}</p>
                <p class="mb-0"><strong>Delivery time:</strong> {{ $gig->delivery_time }} day(s)</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('hire-requests.store', $gig) }}">
                @csrf

                <div class="mb-3">
                    <label for="message" class="form-label fw-bold">Message to seller</label>
                    <textarea
                        name="message"
                        id="message"
                        rows="6"
                        class="form-control"
                        minlength="10"
                        maxlength="1000"
                        required
                        placeholder="Explain the work you need from the seller."
                    >{{ old('message') }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="proposed_deadline" class="form-label fw-bold">Proposed deadline</label>
                    <input
                        type="date"
                        name="proposed_deadline"
                        id="proposed_deadline"
                        class="form-control"
                        min="{{ now()->format('Y-m-d') }}"
                        value="{{ old('proposed_deadline', now()->addDays($gig->delivery_time)->format('Y-m-d')) }}"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-success btn-lg">
                    Submit Hire Request
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
