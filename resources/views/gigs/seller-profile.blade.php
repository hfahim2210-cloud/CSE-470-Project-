<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $user->name }} - Seller Profile</title>
</head>
<body>

    <p><a href="{{ route('gigs.index') }}">&larr; Back to Gigs</a></p>

    <h1>{{ $user->name }}</h1>
    <p>Member since {{ $user->created_at->format('F Y') }}</p>

    <hr>

    <h2>Gigs by {{ $user->name }}</h2>

    @forelse ($gigs as $gig)
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <h3>{{ $gig->title }}</h3>
            <p>{{ $gig->description }}</p>
            <p><strong>Price:</strong> ${{ number_format($gig->price, 2) }}</p>
            <p><strong>Category:</strong> {{ $gig->category }}</p>
        </div>
    @empty
        <p>This seller has no active gigs right now.</p>
    @endforelse

</body>
</html>