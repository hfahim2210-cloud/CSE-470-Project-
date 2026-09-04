<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GigEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5" style="max-width: 420px;">
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body p-4">
            
            <!-- Header Title -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary mb-1">GigEX</h2>
                <h5 class="text-secondary">Login</h5>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="student@g.bracu.ac.bd"
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Login</button>
            </form>

            <hr class="my-4">

            <p class="text-center mb-0 small text-muted">
                Don't have an account? <a href="{{ route('register') }}" class="text-decoration-none">Register here</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>