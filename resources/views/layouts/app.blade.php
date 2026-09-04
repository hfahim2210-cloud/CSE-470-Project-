<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GigEx - Student Marketplace')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-light: #EBF1F2;         /* Soft Light Mint/Ice Background */
            --card-bg: #501F3A;          /* Deep Plum Containers */
            --text-dark: #1C2526;        /* Crisp Dark Text for Body */
            --text-muted: #627275;       /* Muted Text for Light BG */
            --accent-primary: #CB2D6F;   /* Electric Rose CTA */
            --accent-hover: #aa205a;     /* Darker Rose Hover */
            --accent-secondary: #14A098; /* Vibrant Teal Accent */
            --border-color: rgba(20, 160, 152, 0.25);
        }

        body {
            background-color: var(--bg-light) !important;
            color: var(--text-dark) !important;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        /* Dark Header Navbar for Dual-Tone Look */
        .navbar-custom {
            background-color: #0F292F !important;
            border-bottom: 2px solid var(--accent-secondary);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .brand-accent {
            color: var(--accent-primary) !important;
        }

        /* Search Bar Styling */
        .search-input-group .input-group-text {
            background-color: #1A3E46 !important;
            border-color: var(--border-color) !important;
            color: var(--accent-secondary) !important;
        }

        .search-input-group .form-control {
            background-color: #1A3E46 !important;
            border-color: var(--border-color) !important;
            color: #ffffff !important;
        }

        .search-input-group .form-control::placeholder {
            color: #A0B2B5 !important;
        }

        /* Navigation Links */
        .nav-link {
            color: #D1E0E2 !important;
            transition: color 0.2s ease;
        }

        .nav-link:hover, 
        .nav-link.active {
            color: var(--accent-primary) !important;
            font-weight: 600;
        }

        /* Primary Action Buttons */
        .btn-brand {
            background-color: var(--accent-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            border: none;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .btn-brand:hover {
            background-color: var(--accent-hover) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* Dropdown Customization */
        .dropdown-menu-dark-custom {
            background-color: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 10px;
            min-width: 220px;
        }

        .dropdown-menu-dark-custom .dropdown-item {
            color: #ffffff !important;
            padding: 8px 16px;
        }

        .dropdown-menu-dark-custom .dropdown-item:hover {
            background-color: rgba(203, 45, 111, 0.2) !important;
            color: var(--accent-primary) !important;
        }

        .dropdown-menu-dark-custom .dropdown-header {
            color: #A0B2B5 !important;
        }

        /* Cards - Rich Dark Plum on Light Background */
        .card {
            background-color: var(--card-bg) !important;
            color: #ffffff !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(15, 41, 47, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(15, 41, 47, 0.18) !important;
        }

        .card-header, 
        .card-footer {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Alert Callouts */
        .alert-success {
            background-color: rgba(20, 160, 152, 0.15) !important;
            border-color: var(--accent-secondary) !important;
            color: #0F292F !important;
            font-weight: 500;
        }

        /* Global Headings on Light Background */
        h1, h2, h3, h4, h5, h6 {
            color: #0F292F;
        }

        .card h1, 
        .card h2, 
        .card h3, 
        .card h4, 
        .card h5, 
        .card h6 {
            color: #ffffff !important;
        }
    </style>
</head>
<body>

    <!-- ⚡ GigEx Main Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top shadow-sm py-2">
        <div class="container">
            
            <!-- Logo / Brand -->
            <a class="navbar-brand d-flex align-items-center me-4" href="{{ route('gigs.marketplace') }}">
                <i class="bi bi-lightning-charge-fill me-2 fs-4" style="color: var(--accent-secondary);"></i>
                <span class="fw-bold text-white">Gig<span class="brand-accent">Ex</span></span>
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links & Search -->
            <div class="collapse navbar-collapse" id="navbarContent">
                
                <!-- Search Input -->
                <form class="d-flex my-2 my-lg-0 me-auto flex-grow-1 max-w-md px-lg-3" action="{{ route('gigs.marketplace') }}" method="GET">
                    <div class="input-group search-input-group">
                        <span class="input-group-text border-end-0"><i class="bi bi-search"></i></span>
                        <input class="form-control border-start-0 ps-0 shadow-none" type="search" name="search" placeholder="Find student services (design, coding, writing...)" value="{{ request('search') }}">
                    </div>
                </form>

                <!-- Navigation Links -->
                <ul class="navbar-nav me-3 mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('gigs.marketplace') ? 'active' : '' }}" href="{{ route('gigs.marketplace') }}">
                            <i class="bi bi-grid-fill me-1"></i> Marketplace
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('gigs.index') ? 'active' : '' }}" href="{{ route('gigs.index') }}">
                            <i class="bi bi-briefcase-fill me-1"></i> Dashboard
                        </a>
                    </li>
                </ul>

                <!-- Right Actions & User Menu -->
                <div class="d-flex align-items-center gap-2">
                    <!-- CTA Button -->
                    <a href="{{ route('gigs.create') }}" class="btn btn-brand btn-sm px-3 rounded-pill d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-1"></i> Post a Gig
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="dropdown ms-2">
                        @auth
                            <button class="btn btn-outline-light btn-sm rounded-pill px-2 py-1 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" style="border-color: var(--border-color);">
                                <i class="bi bi-person-circle fs-5" style="color: var(--accent-secondary);"></i>
                                <span class="d-none d-md-inline text-white fw-semibold small">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down small text-muted"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom shadow border-0 mt-2">
                                <!-- Dynamic User Name & Role Header -->
                                <li class="px-3 py-2 border-bottom border-secondary border-opacity-25 mb-1">
                                    <div class="fw-bold text-white mb-0">{{ Auth::user()->name }}</div>
                                    <small class="text-capitalize" style="color: var(--accent-secondary);">
                                        Role: {{ Auth::user()->role ?? 'Student' }}
                                    </small>
                                </li>

                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('gigs.index') }}">
                                        <i class="bi bi-collection-fill me-2" style="color: var(--accent-secondary);"></i> My Active Gigs
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider border-secondary opacity-25"></li>

                                <!-- Secure Logout Form -->
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item d-flex align-items-center text-danger w-100 bg-transparent border-0">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        @else
                            <!-- Guest Links (If not logged in) -->
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light me-1">Log In</a>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-brand">Sign Up</a>
                        @endauth
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <!-- Page Content Container -->
    <main class="py-4">
        <div class="container">
            <!-- Flash Success Notification -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>