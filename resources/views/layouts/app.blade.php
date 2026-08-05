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
            --brand-primary: #4f46e5; /* Indigo accent */
            --brand-hover: #4338ca;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .btn-brand {
            background-color: var(--brand-primary);
            color: #ffffff;
            font-weight: 600;
            border: none;
        }

        .btn-brand:hover {
            background-color: var(--brand-hover);
            color: #ffffff;
        }

        .nav-link.active {
            color: var(--brand-primary) !important;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">

    <!-- ⚡ GigEx Main Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2">
        <div class="container">
            
            <!-- Logo / Brand -->
            <a class="navbar-brand d-flex align-items-center me-4" href="{{ route('gigs.marketplace') }}">
                <i class="bi bi-lightning-charge-fill text-warning me-2 fs-4"></i>
                <span class="fw-bold text-white">Gig<span class="text-primary">Ex</span></span>
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links & Search -->
            <div class="collapse navbar-collapse" id="navbarContent">
                
                <!-- Search Input -->
                <form class="d-flex my-2 my-lg-0 me-auto flex-grow-1 max-w-md px-lg-3" action="{{ route('gigs.marketplace') }}" method="GET">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
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
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm rounded-circle p-1 ms-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><h6 class="dropdown-header">Logged in as Student</h6></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('gigs.index') }}">
                                    <i class="bi bi-collection-fill me-2 text-muted"></i> My Active Gigs
                                </a>
                            </li>
                        </ul>
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