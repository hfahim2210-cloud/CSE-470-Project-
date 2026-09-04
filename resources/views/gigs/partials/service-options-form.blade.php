@php
    $savedTiers = isset($gig) ? $gig->tiers->keyBy('name') : collect();
    $tierDefinitions = [
        'basic' => ['label' => 'Basic', 'hint' => 'A simple entry package'],
        'standard' => ['label' => 'Standard', 'hint' => 'Your most popular package'],
        'premium' => ['label' => 'Premium', 'hint' => 'The complete service'],
    ];
    $savedAddons = isset($gig)
        ? $gig->addons->map(fn ($addon) => $addon->only(['name', 'description', 'price', 'extra_days']))->all()
        : [];
    $addonRows = old('addons', $savedAddons);
    $addonRows = count($addonRows) ? $addonRows : [['name' => '', 'description' => '', 'price' => '', 'extra_days' => 0]];
@endphp

<section class="card border-primary-subtle bg-light p-3 p-md-4 mb-4" id="service-options-builder">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h5 class="mb-1"><i class="bi bi-layers me-2 text-primary"></i>Service Packages</h5>
            <p class="text-muted small mb-0">Optional: offer Basic, Standard, and Premium choices. Your regular gig price remains available when no package is enabled.</p>
        </div>
        <span class="badge text-bg-primary align-self-start">Up to 3 tiers</span>
    </div>

    <div class="row g-3">
        @foreach($tierDefinitions as $tierName => $definition)
            @php
                $savedTier = $savedTiers->get($tierName);
                $enabled = (string) old("tiers.$tierName.enabled", $savedTier ? '1' : '0') === '1';
            @endphp
            <div class="col-lg-4">
                <div class="card h-100 border {{ $tierName === 'standard' ? 'border-primary' : '' }}">
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="tiers[{{ $tierName }}][enabled]" value="0">
                            <input
                                class="form-check-input package-toggle"
                                type="checkbox"
                                role="switch"
                                name="tiers[{{ $tierName }}][enabled]"
                                id="tier-{{ $tierName }}-enabled"
                                value="1"
                                {{ $enabled ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-bold" for="tier-{{ $tierName }}-enabled">
                                {{ $definition['label'] }}
                            </label>
                            <div class="text-muted small">{{ $definition['hint'] }}</div>
                        </div>

                        <div class="package-fields {{ $enabled ? '' : 'opacity-50' }}">
                            <label class="form-label small fw-semibold">Package title</label>
                            <input type="text" class="form-control form-control-sm mb-2 package-required" name="tiers[{{ $tierName }}][title]" maxlength="100" value="{{ old("tiers.$tierName.title", $savedTier?->title ?? $definition['label']) }}">

                            <label class="form-label small fw-semibold">Description</label>
                            <textarea class="form-control form-control-sm mb-2" name="tiers[{{ $tierName }}][description]" rows="3" maxlength="1000">{{ old("tiers.$tierName.description", $savedTier?->description) }}</textarea>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Price ($)</label>
                                    <input type="number" class="form-control form-control-sm package-required" name="tiers[{{ $tierName }}][price]" min="1" max="999999.99" step="0.01" value="{{ old("tiers.$tierName.price", $savedTier?->price) }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Delivery days</label>
                                    <input type="number" class="form-control form-control-sm package-required" name="tiers[{{ $tierName }}][delivery_time]" min="1" max="365" value="{{ old("tiers.$tierName.delivery_time", $savedTier?->delivery_time) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Included revisions</label>
                                    <input type="number" class="form-control form-control-sm package-required" name="tiers[{{ $tierName }}][revisions]" min="0" max="100" value="{{ old("tiers.$tierName.revisions", $savedTier?->revisions ?? 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h5 class="mb-1"><i class="bi bi-plus-circle me-2 text-success"></i>Paid Add-ons</h5>
            <p class="text-muted small mb-0">Offer optional extras such as rush delivery or source files.</p>
        </div>
        <button type="button" class="btn btn-outline-success btn-sm" id="add-addon-row">
            <i class="bi bi-plus-lg me-1"></i>Add option
        </button>
    </div>

    <div id="addon-rows">
        @foreach($addonRows as $index => $addon)
            <div class="addon-row card border-0 shadow-sm mb-2">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Add-on name</label>
                            <input type="text" class="form-control form-control-sm" name="addons[{{ $index }}][name]" maxlength="100" value="{{ $addon['name'] ?? '' }}" placeholder="24-hour delivery">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" class="form-control form-control-sm" name="addons[{{ $index }}][description]" maxlength="1000" value="{{ $addon['description'] ?? '' }}" placeholder="What the buyer receives">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Price ($)</label>
                            <input type="number" class="form-control form-control-sm" name="addons[{{ $index }}][price]" min="0.01" max="999999.99" step="0.01" value="{{ $addon['price'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Extra days</label>
                            <input type="number" class="form-control form-control-sm" name="addons[{{ $index }}][extra_days]" min="0" max="365" value="{{ $addon['extra_days'] ?? 0 }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-addon" aria-label="Remove add-on"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <small class="text-muted">Maximum 10 add-ons. Leave the row empty if you do not want to offer one.</small>
</section>

<template id="addon-row-template">
    <div class="addon-row card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small fw-semibold">Add-on name</label><input type="text" class="form-control form-control-sm" name="addons[__INDEX__][name]" maxlength="100" placeholder="24-hour delivery"></div>
                <div class="col-md-4"><label class="form-label small fw-semibold">Description</label><input type="text" class="form-control form-control-sm" name="addons[__INDEX__][description]" maxlength="1000" placeholder="What the buyer receives"></div>
                <div class="col-md-2"><label class="form-label small fw-semibold">Price ($)</label><input type="number" class="form-control form-control-sm" name="addons[__INDEX__][price]" min="0.01" max="999999.99" step="0.01"></div>
                <div class="col-md-2"><label class="form-label small fw-semibold">Extra days</label><input type="number" class="form-control form-control-sm" name="addons[__INDEX__][extra_days]" min="0" max="365" value="0"></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm w-100 remove-addon" aria-label="Remove add-on"><i class="bi bi-trash"></i></button></div>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.package-toggle').forEach(function (toggle) {
        function updatePackage() {
            const fields = toggle.closest('.card-body').querySelector('.package-fields');
            fields.classList.toggle('opacity-50', !toggle.checked);
            fields.querySelectorAll('.package-required').forEach(function (input) {
                input.required = toggle.checked;
            });
        }
        toggle.addEventListener('change', updatePackage);
        updatePackage();
    });

    const rows = document.getElementById('addon-rows');
    const template = document.getElementById('addon-row-template');
    const addButton = document.getElementById('add-addon-row');
    let nextIndex = {{ count($addonRows) }};

    addButton.addEventListener('click', function () {
        if (rows.querySelectorAll('.addon-row').length >= 10) return;
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
    });

    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-addon');
        if (!button) return;
        button.closest('.addon-row').remove();
        if (!rows.querySelector('.addon-row')) addButton.click();
    });
});
</script>
