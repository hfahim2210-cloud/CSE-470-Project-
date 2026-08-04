<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gig - {{ $gig->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 700px;">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="mb-4">Edit Gig</h2>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('gigs.update', $gig->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Gig Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $gig->title) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="Graphics & Design" {{ $gig->category == 'Graphics & Design' ? 'selected' : '' }}>Graphics & Design</option>
                                <option value="Web Development" {{ $gig->category == 'Web Development' ? 'selected' : '' }}>Web Development</option>
                                <option value="Digital Marketing" {{ $gig->category == 'Digital Marketing' ? 'selected' : '' }}>Digital Marketing</option>
                                <option value="Writing & Translation" {{ $gig->category == 'Writing & Translation' ? 'selected' : '' }}>Writing & Translation</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Price ($)</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price', $gig->price) }}" step="0.01" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Delivery (Days)</label>
                            <input type="number" name="delivery_time" class="form-control" value="{{ old('delivery_time', $gig->delivery_time) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $gig->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cover Image</label>
                        @if($gig->image)
                            <div class="mb-2">
                                <p class="text-muted small mb-1">Current Image:</p>
                                <img src="{{ Storage::url($gig->image) }}" class="rounded img-thumbnail" style="height: 100px; object-fit: cover;" alt="Current Cover">
                            </div>
                        @endif
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image.</small>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="{{ route('gigs.show', $gig->id) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Gig</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>