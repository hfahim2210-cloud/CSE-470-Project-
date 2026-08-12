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
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Create a New Gig</h4>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('gigs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label font-weight-bold">Gig Title</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. I will design a modern logo for your startup" required>
                        </div>

                        <!-- Category & Price Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label font-weight-bold">Category</label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="" selected disabled>Select Category</option>
                                    <option value="Graphics & Design" {{ old('category') == 'Graphics & Design' ? 'selected' : '' }}>Graphics & Design</option>
                                    <option value="Programming & Tech" {{ old('category') == 'Programming & Tech' ? 'selected' : '' }}>Programming & Tech</option>
                                    <option value="Digital Marketing" {{ old('category') == 'Digital Marketing' ? 'selected' : '' }}>Digital Marketing</option>
                                    <option value="Writing & Translation" {{ old('category') == 'Writing & Translation' ? 'selected' : '' }}>Writing & Translation</option>
                                    <option value="Video & Animation" {{ old('category') == 'Video & Animation' ? 'selected' : '' }}>Video & Animation</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label font-weight-bold">Price ($)</label>
                                <input type="number" step="0.01" name="price" id="price" class="form-control" value="{{ old('price') }}" placeholder="15.00" min="1" required>
                            </div>
                        </div>

                        <!-- Delivery Days -->
                        <div class="mb-3">
                            <label for="delivery_time" class="form-label font-weight-bold">Delivery Time (Days)</label>
                            <input type="number" name="delivery_time" id="delivery_time" class="form-control" value="{{ old('delivery_time') }}" placeholder="3" min="1" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label font-weight-bold">Gig Description</label>
                            <textarea name="description" id="description" rows="5" class="form-control" placeholder="Describe what you offer in detail..." required>{{ old('description') }}</textarea>
                        </div>

                        <!-- Cover Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label font-weight-bold">Cover Image (Main Image)</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        </div>

                        <!-- Portfolio Attachments -->
                        <div class="mb-4">
                            <label for="portfolio_files" class="form-label font-weight-bold">Portfolio Work Samples (Multiple allowed - Images/PDFs)</label>
                            <input type="file" name="portfolio_files[]" id="portfolio_files" class="form-control" multiple accept="image/*,.pdf">
                            <small class="text-muted">Showcase previous work to attract more buyers!</small>
                        </div>

                        <!-- ⚡ NEW: Workload & Availability Settings Card -->
                        <div class="card p-3 mb-4 border bg-light">
                            <h5 class="text-dark mb-3">
                                <i class="bi bi-calendar-check me-2 text-primary"></i> Workload & Availability Settings
                            </h5>
                            
                            <div class="row align-items-center">
                                <!-- Max Weekly Orders Input -->
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="max_weekly_orders" class="form-label font-weight-bold">Max Orders Per Week</label>
                                    <input type="number" 
                                           name="max_weekly_orders" 
                                           id="max_weekly_orders" 
                                           class="form-control" 
                                           value="{{ old('max_weekly_orders', 5) }}" 
                                           min="1" 
                                           max="20" 
                                           required>
                                    <small class="text-muted d-block mt-1">
                                        Locks hiring automatically when this weekly order limit is reached.
                                    </small>
                                </div>

                                <!-- Exam Mode / Pause Orders Switch -->
                                <div class="col-md-6">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="is_accepting_orders" 
                                               id="is_accepting_orders" 
                                               value="1" 
                                               {{ old('is_accepting_orders', true) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold" for="is_accepting_orders">
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
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Publish Gig</button>
                            <a href="{{ route('gigs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>