<nav class="gigex-topbar" aria-label="Primary navigation">
    <div class="container gigex-nav-shell">
        <a class="gigex-brand" href="{{ route('gigs.marketplace') }}" aria-label="GigEx marketplace home">
            <span class="gigex-brand-mark" aria-hidden="true">G</span>
            <span>GigEx</span>
        </a>

        <div class="gigex-nav-links">
            <a class="gigex-nav-link {{ request()->routeIs('gigs.marketplace') ? 'active' : '' }}" href="{{ route('gigs.marketplace') }}">Marketplace</a>
            @auth
                @if(Auth::user()->role === 'seller')
                    <a class="gigex-nav-link {{ request()->routeIs('gigs.index') ? 'active' : '' }}" href="{{ route('gigs.index') }}">Dashboard</a>
                    <a class="gigex-nav-link {{ request()->routeIs('hire-requests.incoming') ? 'active' : '' }}" href="{{ route('hire-requests.incoming') }}">Hire Requests</a>
                    <a class="gigex-nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">Orders</a>
                    <a class="gigex-nav-link {{ request()->routeIs('gigs.create') ? 'active' : '' }}" href="{{ route('gigs.create') }}">Post a Gig</a>
                @elseif(Auth::user()->role === 'buyer')
                    <a class="gigex-nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">Orders</a>
                    <a class="gigex-nav-link {{ request()->routeIs('wishlist.*') ? 'active' : '' }}" href="{{ route('wishlist.index') }}">Wishlist</a>
                @endif
            @endauth
        </div>

        <div class="gigex-account">
            @auth
                <span class="gigex-user" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-light btn-sm">Register</a>
            @endauth
        </div>
    </div>
</nav>
