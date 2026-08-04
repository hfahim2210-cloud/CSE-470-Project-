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
            <h2 class="mb-3">Submit Hire Request</h2>

            <div class="bg-light border rounded p-3 mb-4">
                <h5 class="mb-2">{{ $gig->title }}</h5>
                <p class="mb-1"><strong>Seller:</strong> {{ $gig->user->name }}</p>
                <p class="mb-1"><strong>Price:</strong> ${{ number_format($gig->price, 2) }}</p>
                <p class="mb-0"><strong>Delivery:</strong> {{ $gig->delivery_time }} day(s)</p>
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
                    <label for="message" class="form-label fw-bold">Message to Seller</label>
                    <textarea
                        id="message"
                        name="message"
                        class="form-control"
                        rows="6"
                        minlength="10"
                        maxlength="1000"
                        required
                        placeholder="Explain what you need from the seller."
                    >{{ old('message') }}</textarea>
                    <div class="form-text">10–1000 characters.</div>
                </div>

                <button type="submit" class="btn btn-success">Submit Hire Request</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
