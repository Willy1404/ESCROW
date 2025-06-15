<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Azania Bank Escrow')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Add this line in the head section -->
<link rel="stylesheet" href="{{ asset('css/button-styles.css') }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #3399BB;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
            width: 250px;
            z-index: 100;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            border-left-color: white;
            background-color: rgba(255,255,255,0.2);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .profile-img {
            width: 70px;
            height: 70px;
            margin: 0 auto;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 80px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .main-content-full {
            margin-left: 0;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .sidebar-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
            background: transparent;
            border: none;
            font-size: 18px;
            z-index: 101;
            display: none;
        }
        
        /* Admin submenu styling */
        #adminSubmenu {
            border-left: 1px solid rgba(255,255,255,0.2);
            margin-left: 25px;
        }

        #adminSubmenu .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        #adminSubmenu .nav-link i {
            width: 20px;
            text-align: center;
        }

        .nav-link .fa-chevron-down {
            transition: transform 0.3s;
            font-size: 0.8rem;
            margin-left: auto;
        }

        .nav-link[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.active {
                margin-left: 250px;
            }
            .sidebar-toggle {
                display: block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.4);
                z-index: 99;
                top: 0;
                left: 0;
            }
            .sidebar-overlay.active {
                display: block;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(auth()->check())
    <!-- Only show sidebar for authenticated users -->
    <div class="sidebar" id="sidebar">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="text-center mb-4" style="margin-top: 15px;">
            <img src="{{ asset('images/azania-logo.png') }}" alt="Azania Bank" class="img-fluid" style="max-height: 60px;">
        </div>
        
        <div class="text-center mb-4">
            <div class="profile-img">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="mt-3">
                <h6 class="mb-1">{{ auth()->user()->name }}</h6>
                <p class="small mb-0">{{ ucfirst(auth()->user()->role) }}</p>
                <p class="small mb-0">{{ auth()->user()->user_id }}</p>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <!-- Keep Dashboard only for buyer and seller roles -->
            @if(auth()->user()->role == 'buyer' || auth()->user()->role == 'seller')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            @endif
            
            @if(auth()->user()->role == 'buyer')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('escrow.create') ? 'active' : '' }}" href="{{ route('escrow.create') }}">
                    <i class="fas fa-plus-circle"></i> Create Escrow
                </a>
            </li>
            @endif

            @if(auth()->user()->role == 'seller')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('control-numbers.*') ? 'active' : '' }}" href="{{ route('control-numbers.index') }}">
                    <i class="fas fa-hashtag"></i> Control Numbers
                </a>
            </li>
            @endif
            
            @if(auth()->user()->role == 'buyer' || auth()->user()->role == 'seller')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('escrow.index') ? 'active' : '' }}" href="{{ route('escrow.index') }}">
                    <i class="fas fa-lock"></i> Bills
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                    <i class="fas fa-money-bill"></i> Payments
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('disputes.*') ? 'active' : '' }}" href="{{ route('disputes.index') }}">
                    <i class="fas fa-exclamation-triangle"></i> Disputes
                </a>
            </li>
            @endif
            
            @if(auth()->user()->role == 'maker')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin*') ? 'active' : '' }}" 
                href="#adminSubmenu" 
                data-bs-toggle="collapse" 
                aria-expanded="{{ request()->is('admin*') ? 'true' : 'false' }}">
                    <i class="fas fa-user-shield"></i> Admin Panel
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse {{ request()->is('admin*') ? 'show' : '' }} nav flex-column" id="adminSubmenu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}" href="{{ route('admin.transactions') }}">
                            <i class="fas fa-exchange-alt me-2"></i> Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.disputes*') ? 'active' : '' }}" href="{{ route('admin.disputes') }}">
                            <i class="fas fa-gavel me-2"></i> Disputes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.maker-checker.my-actions') ? 'active' : '' }}" href="{{ route('admin.maker-checker.my-actions') }}">
                            <i class="fas fa-tasks me-2"></i> My Pending Actions
                        </a>
                    </li>
                </ul>
            </li>
            @elseif(auth()->user()->role == 'checker')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin*') ? 'active' : '' }}" 
                href="#adminSubmenu" 
                data-bs-toggle="collapse" 
                aria-expanded="{{ request()->is('admin*') ? 'true' : 'false' }}">
                    <i class="fas fa-user-shield"></i> Admin Panel
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="collapse {{ request()->is('admin*') ? 'show' : '' }} nav flex-column" id="adminSubmenu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}" href="{{ route('admin.transactions') }}">
                            <i class="fas fa-exchange-alt me-2"></i> Transactions
                        </a>
                    </li>
                    <!-- Removed Disputes link for checkers -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.maker-checker.index') ? 'active' : '' }}" href="{{ route('admin.maker-checker.index') }}">
                            <i class="fas fa-check-double me-2"></i> Pending Approvals
                        </a>
                    </li>
                </ul>
            </li>
            @endif
            @if(auth()->user()->role === 'it_support')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('it-support*') ? 'active' : '' }}" 
                        href="#itSupportSubmenu" 
                        data-bs-toggle="collapse" 
                        aria-expanded="{{ request()->is('it-support*') ? 'true' : 'false' }}">
                            <i class="fas fa-shield-alt"></i> IT Support
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="collapse {{ request()->is('it-support*') ? 'show' : '' }} nav flex-column" id="itSupportSubmenu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('it_support.dashboard') ? 'active' : '' }}" href="{{ route('it_support.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('it_support.staff.*') ? 'active' : '' }}" href="{{ route('it_support.staff.index') }}">
                            <i class="fas fa-users-cog me-2"></i> Manage Staff
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('it_support.audit-log') ? 'active' : '' }}" href="{{ route('it_support.audit-log') }}">
                            <i class="fas fa-clipboard-list me-2"></i> Audit Log
                        </a>
                    </li>
                </ul>
            </li>
            @endif
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-cog"></i> Settings
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <form method="POST" action="{{ route('logout') }}" class="d-grid px-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <main class="main-content" id="mainContent">
    @else
    <!-- Guest User Layout - No Sidebar, Full Width Main Content -->
    <div class="container py-3 mb-4">
        <div class="d-flex justify-content-between">
            <a href="/" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('images/azania-logo.png') }}" alt="Azania Bank" class="img-fluid" style="max-height: 60px;">
            </a>
            <div>
                <!-- <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Register</a> -->
            </div>
        </div>
    </div>
    
    <main class="main-content-full" id="mainContent">
    @endif
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">@yield('header', 'Dashboard')</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @yield('header_buttons')
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    mainContent.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                });
            }
            
            // Admin submenu toggle
            const adminLink = document.querySelector('a[href="#adminSubmenu"]');
            if (adminLink) {
                adminLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submenu = document.getElementById('adminSubmenu');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    
                    this.setAttribute('aria-expanded', !isExpanded);
                    if (isExpanded) {
                        submenu.classList.remove('show');
                    } else {
                        submenu.classList.add('show');
                    }
                });
            }

            // IT Support submenu toggle
            const itSupportLink = document.querySelector('a[href="#itSupportSubmenu"]');
            if (itSupportLink) {
                itSupportLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submenu = document.getElementById('itSupportSubmenu');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    
                    this.setAttribute('aria-expanded', !isExpanded);
                    if (isExpanded) {
                        submenu.classList.remove('show');
                    } else {
                        submenu.classList.add('show');
                    }
                });
            }
        });
            // Session expiration handler
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('error', function(e) {
                if (e.target && e.target.status === 419) {
                    alert('Your session has expired. The page will now refresh.');
                    window.location.reload();
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html>