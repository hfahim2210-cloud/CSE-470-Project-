<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a New Gig</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>Create a New Gig</h4>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the errors below:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('gigs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- General Overview Header -->
                        <h5 class="text-primary border-bottom pb-2 mb-3 fw-bold">
                            <i class="bi bi-info-circle me-2"></i>General Overview
                        </h5>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Gig Title</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. I will design a modern logo for your startup" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category & Base Price Row -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="" selected disabled>Select Category</option>
                                    <option value="Graphics & Design" {{ old('category') == 'Graphics & Design' ? 'selected' : '' }}>Graphics & Design</option>
                                    <option value="Programming & Tech" {{ old('category') == 'Programming & Tech' ? 'selected' : '' }}>Programming & Tech</option>
                                    <option value="Digital Marketing" {{ old('category') == 'Digital Marketing' ? 'selected' : '' }}>Digital Marketing</option>
                                    <option value="Writing & Translation" {{ old('category') == 'Writing & Translation' ? 'selected' : '' }}>Writing & Translation</option>
                                    <option value="Video & Animation" {{ old('category') == 'Video & Animation' ? 'selected' : '' }}>Video & Animation</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label fw-bold">Starting Base Price ($)</label>
                                <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" placeholder="15.00" min="1" required>
                                <small class="text-muted">Displayed as "Starting at" price in search results.</small>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Base Delivery Days -->
                        <div class="mb-3">
                            <label for="delivery_time" class="form-label fw-bold">Base Delivery Time (Days)</label>
                            <input type="number" name="delivery_time" id="delivery_time" class="form-control @error('delivery_time') is-invalid @enderror" value="{{ old('delivery_time') }}" placeholder="3" min="1" required>
                            @error('delivery_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Gig Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Describe what you offer in detail..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Service Tiers Section -->
                        <h5 class="text-primary border-bottom pb-2 mb-3 fw-bold mt-4">
                            <i class="bi bi-layers me-2"></i>Service Packages & Pricing Tiers
                        </h5>
                        <p class="text-muted small mb-3">Offer buyers 3 custom options (Basic, Standard, Premium) to fit different budgets and needs.</p>

                        <div class="row g-3 mb-4">
                            @php
                                $tierConfigs = [
                                    'basic' => ['label' => 'Basic Tier', 'badge' => 'bg-secondary', 'placeholder' => 'Starter Package'],
                                    'standard' => ['label' => 'Standard Tier', 'badge' => 'bg-primary', 'placeholder' => 'Pro Package'],
                                    'premium' => ['label' => 'Premium Tier', 'badge' => 'bg-dark', 'placeholder' => 'Enterprise / VIP Package']
                                ];
                            @endphp

                            @foreach($tierConfigs as $type => $config)
                                <div class="col-lg-4 col-md-12">
                                    <div class="card h-100 border shadow-sm">
                                        <div class="card-header {{ $config['badge'] }} text-white fw-bold text-center">
                                            {{ $config['label'] }}
                                        </div>
                                        <div class="card-body bg-white">
                                            <input type="hidden" name="tiers[{{ $type }}][tier_type]" value="{{ $type }}">

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold mb-1">Package Title</label>
                                                <input type="text" 
                                                       name="tiers[{{ $type }}][title]" 
                                                       class="form-control form-control-sm @error("tiers.$type.title") is-invalid @enderror" 
                                                       value="{{ old("tiers.$type.title") }}" 
                                                       placeholder="{{ $config['placeholder'] }}" 
                                                       required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold mb-1">Description</label>
                                                <textarea name="tiers[{{ $type }}][description]" 
                                                          class="form-control form-control-sm @error("tiers.$type.description") is-invalid @enderror" 
                                                          rows="3" 
                                                          placeholder="What's included in this tier..." 
                                                          required>{{ old("tiers.$type.description") }}</textarea>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold mb-1">Price ($)</label>
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="tiers[{{ $type }}][price]" 
                                                       class="form-control form-control-sm @error("tiers.$type.price") is-invalid @enderror" 
                                                       value="{{ old("tiers.$type.price") }}" 
                                                       placeholder="25.00" 
                                                       min="0" 
                                                       required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold mb-1">Delivery Time (Days)</label>
                                                <input type="number" 
                                                       name="tiers[{{ $type }}][delivery_days]" 
                                                       class="form-control form-control-sm @error("tiers.$type.delivery_days") is-invalid @enderror" 
                                                       value="{{ old("tiers.$type.delivery_days") }}" 
                                                       placeholder="2" 
                                                       min="1" 
                                                       required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold mb-1">Revisions Allowed</label>
                                                <input type="number" 
                                                       name="tiers[{{ $type }}][revisions]" 
                                                       class="form-control form-control-sm @error("tiers.$type.revisions") is-invalid @enderror" 
                                                       value="{{ old("tiers.$type.revisions", 1) }}" 
                                                       min="0" 
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Extra Paid Add-ons Section -->
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 mt-4">
                            <h5 class="text-primary mb-0 fw-bold">
                                <i class="bi bi-plus-square me-2"></i>Extra Paid Add-ons (Optional)
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-addon-btn">
                                <i class="bi bi-plus-lg me-1"></i>Add Extra Option
                            </button>
                        </div>
                        <p class="text-muted small mb-3">Offer extra services buyers can add to their order during checkout.</p>

                        <div id="addons-container" class="mb-4">
                            @if(old('addons'))
                                @foreach(old('addons') as $index => $addon)
                                    <div class="card p-3 mb-2 bg-light border addon-row">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold mb-1">Add-on Title</label>
                                                <input type="text" name="addons[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $addon['title'] ?? '' }}" placeholder="e.g. 24-Hour Express Delivery">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold mb-1">Price ($)</label>
                                                <input type="number" step="0.01" name="addons[{{ $index }}][price]" class="form-control form-control-sm" value="{{ $addon['price'] ?? '' }}" placeholder="10.00" min="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold mb-1">Extra Days</label>
                                                <input type="number" name="addons[{{ $index }}][extra_delivery_days]" class="form-control form-control-sm" value="{{ $addon['extra_delivery_days'] ?? 0 }}" min="0">
                                            </div>
                                            <div class="col-md-1 text-end pt-3">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-addon-btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Media Attachments Header -->
                        <h5 class="text-primary border-bottom pb-2 mb-3 fw-bold mt-4">
                            <i class="bi bi-images me-2"></i>Media & Portfolio
                        </h5>

                        <!-- Cover Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label fw-bold">Cover Image (Main Display Thumbnail)</label>
                            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Portfolio Attachments -->
                        <div class="mb-4">
                            <label for="portfolio_files" class="form-label fw-bold">Portfolio Work Samples (Multiple allowed - Images/PDFs)</label>
                            <input type="file" name="portfolio_files[]" id="portfolio_files" class="form-control @error('portfolio_files.*') is-invalid @enderror" multiple accept="image/*,.pdf">
                            <small class="text-muted">Showcase previous work to attract more buyers!</small>
                            @error('portfolio_files.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Workload & Availability Settings Card -->
                        <div class="card p-3 mb-4 border bg-light">
                            <h5 class="text-dark mb-3 fw-bold">
                                <i class="bi bi-calendar-check me-2 text-primary"></i> Workload & Availability Settings
                            </h5>
                            
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="max_weekly_orders" class="form-label fw-bold">Max Orders Per Week</label>
                                    <input type="number" 
                                           name="max_weekly_orders" 
                                           id="max_weekly_orders" 
                                           class="form-control @error('max_weekly_orders') is-invalid @enderror" 
                                           value="{{ old('max_weekly_orders', 5) }}" 
                                           min="1" 
                                           max="50" 
                                           required>
                                    <small class="text-muted d-block mt-1">
                                        Locks hiring automatically when this weekly order limit is reached.
                                    </small>
                                    @error('max_weekly_orders')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch pt-2">
                                        <input type="hidden" name="is_accepting_orders" value="0">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="is_accepting_orders" 
                                               id="is_accepting_orders" 
                                               value="1" 
                                               {{ old('is_accepting_orders', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_accepting_orders">
                                            Accepting New Orders
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Uncheck to pause incoming orders during exams or holidays.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">Publish Gig</button>
                            <a href="{{ route('gigs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addonsContainer = document.getElementById('addons-container');
        const addAddonBtn = document.getElementById('add-addon-btn');
        let addonIndex = addonsContainer.querySelectorAll('.addon-row').length;

        function createAddonRow(index) {
            const newRow = document.createElement('div');
            newRow.className = 'card p-3 mb-2 bg-light border addon-row';
            newRow.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold mb-1">Add-on Title</label>
                        <input type="text" name="addons[${index}][title]" class="form-control form-control-sm" placeholder="e.g. Source File Inclusion">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="addons[${index}][price]" class="form-control form-control-sm" placeholder="15.00" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Extra Days</label>
                        <input type="number" name="addons[${index}][extra_delivery_days]" class="form-control form-control-sm" value="0" min="0">
                    </div>
                    <div class="col-md-1 text-end pt-3">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-addon-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            return newRow;
        }

        // Add extra add-on input row dynamically
        addAddonBtn.addEventListener('click', function () {
            addonsContainer.appendChild(createAddonRow(addonIndex));
            addonIndex++;
        });

        // Remove add-on row
        addonsContainer.addEventListener('click', function (e) {
            if (e.target.closest('.remove-addon-btn')) {
                e.target.closest('.addon-row').remove();
            }
        });
    });
</script>
</body>
</html>