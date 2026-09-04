<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Hire Request - Student Gig Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/gigex.css') }}" rel="stylesheet">
</head>
<body class="bg-light py-5">
@include('partials.navigation')

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
                <p class="mb-1"><strong>Starting price:</strong> ${{ number_format($gig->price, 2) }}</p>
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

                @if($gig->tiers->isNotEmpty())
                    @php($selectedTierId = (int) old('tier_id', $gig->tiers->first()?->id))
                    <fieldset class="mb-4">
                        <legend class="h5 fw-bold">Choose a service package</legend>
                        <div class="row g-3">
                            @foreach($gig->tiers as $tier)
                                <div class="col-md-4">
                                    <input
                                        type="radio"
                                        class="btn-check price-option tier-option"
                                        name="tier_id"
                                        id="tier-{{ $tier->id }}"
                                        value="{{ $tier->id }}"
                                        data-price="{{ $tier->price }}"
                                        {{ $selectedTierId === $tier->id ? 'checked' : '' }}
                                        required
                                    >
                                    <label class="card h-100 p-3 package-choice" for="tier-{{ $tier->id }}">
                                        <span class="text-uppercase small text-primary fw-bold">{{ $tier->name }}</span>
                                        <strong class="h5 mt-1">{{ $tier->title }}</strong>
                                        <span class="h4 text-success">${{ number_format($tier->price, 2) }}</span>
                                        <span class="small text-muted mb-2">{{ $tier->delivery_time }} {{ Str::plural('day', $tier->delivery_time) }} · {{ $tier->revisions }} {{ Str::plural('revision', $tier->revisions) }}</span>
                                        <span class="small">{{ $tier->description ?: 'Service package' }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @else
                    <input type="hidden" id="base-price" value="{{ $gig->price }}">
                @endif

                @if($gig->addons->isNotEmpty())
                    <fieldset class="mb-4">
                        <legend class="h5 fw-bold">Add optional extras</legend>
                        <div class="list-group">
                            @foreach($gig->addons as $addon)
                                <label class="list-group-item d-flex align-items-start gap-3 py-3">
                                    <input
                                        class="form-check-input mt-1 price-option addon-option"
                                        type="checkbox"
                                        name="addon_ids[]"
                                        value="{{ $addon->id }}"
                                        data-price="{{ $addon->price }}"
                                        {{ in_array($addon->id, array_map('intval', old('addon_ids', [])), true) ? 'checked' : '' }}
                                    >
                                    <span class="flex-grow-1">
                                        <span class="d-flex justify-content-between gap-3">
                                            <strong>{{ $addon->name }}</strong>
                                            <strong class="text-success">+${{ number_format($addon->price, 2) }}</strong>
                                        </span>
                                        <span class="small text-muted">{{ $addon->description ?: 'Optional service extra' }}{{ $addon->extra_days ? ' · +'.$addon->extra_days.' day(s)' : '' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <div class="alert alert-success d-flex justify-content-between align-items-center mb-4">
                    <span>Estimated order total</span>
                    <strong class="h4 mb-0" id="estimated-total">${{ number_format($gig->tiers->first()?->price ?? $gig->price, 2) }}</strong>
                </div>

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
<style>
    .package-choice { cursor: pointer; transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease; }
    .package-choice:hover { transform: translateY(-2px); border-color: #86b7fe; }
    .btn-check:checked + .package-choice { border-color: #0d6efd; box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15); background: #f5f9ff; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const output = document.getElementById('estimated-total');

    function updateTotal() {
        const selectedTier = document.querySelector('.tier-option:checked');
        const basePrice = document.getElementById('base-price');
        let total = Number(selectedTier ? selectedTier.dataset.price : (basePrice ? basePrice.value : 0));

        document.querySelectorAll('.addon-option:checked').forEach(function (addon) {
            total += Number(addon.dataset.price);
        });

        output.textContent = '$' + total.toFixed(2);
    }

    document.querySelectorAll('.price-option').forEach(function (option) {
        option.addEventListener('change', updateTotal);
    });
    updateTotal();
});
</script>
</body>
</html>
