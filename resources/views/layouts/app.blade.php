<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Setup & Fullscreen -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#10b981">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <title>Al Grow Capital</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #071f11; /* Global Emerald Dark */
            color: #ffffff;
        }
        
        /* Dashboard Layout */
        .sidebar {
            width: 250px;
            background-color: #05160c; /* Slightly darker for depth */
            height: 100vh;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
            border-right: 1px solid rgba(16, 185, 129, 0.1);
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .sidebar::-webkit-scrollbar {
            display: none;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s;
            background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
        }

        .text-gray-800 { color: #ffffff !important; }
        .text-muted { color: rgba(255, 255, 255, 0.6) !important; }

        /* Mobile Navbar */
        .mobile-header {
            display: none;
            background-color: #05160c;
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1001;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Sidebar Styling */
        .sidebar-brand {
            padding: 1.5rem;
            font-size: 1.25rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
        }

        /* Sidebar Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                left: -250px;
                padding-top: 70px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 1.25rem;
            }
            .mobile-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            }
            .desktop-title {
                display: none;
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 999;
        }
        .sidebar-overlay.active {
            display: block;
        }
        .nav-link {
            color: rgba(255,255,255,0.6);
            padding: 1rem 1.5rem;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .nav-link:hover, .nav-link.active {
            color: #34d399;
            background-color: rgba(16, 185, 129, 0.05);
            border-left: 4px solid #10b981;
        }
        
        .card-header {
            background-color: rgba(6, 78, 59, 0.4) !important; /* Dark Emerald 900 */
            border-bottom: 1px solid rgba(16, 185, 129, 0.2) !important;
            color: #ffffff !important;
        }

        .card {
            background: rgba(5, 22, 12, 0.7) !important; /* Very dark emerald */
            backdrop-filter: blur(15px);
            border: 1px solid rgba(16, 185, 129, 0.15) !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4) !important;
        }

        .table {
            color: #ffffff !important;
            border-color: rgba(16, 185, 129, 0.1) !important;
            --bs-table-bg: transparent !important;
            --bs-table-color: #ffffff !important;
        }
        
        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #ffffff !important;
            background-color: rgba(16, 185, 129, 0.1) !important;
            border-bottom: 2px solid rgba(16, 185, 129, 0.2) !important;
            padding: 1.25rem 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid rgba(16, 185, 129, 0.05) !important;
        }

        .btn-primary-custom {
            background-color: #10b981;
            border-color: #10b981;
            color: #05160c;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            background-color: #34d399;
            border-color: #34d399;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-outline-primary-custom {
            color: #10b981;
            border-color: #10b981;
            font-weight: 500;
        }

        .btn-outline-primary-custom:hover {
            background-color: #10b981;
            color: #05160c;
        }

        .text-success-custom { color: #10b981 !important; }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 0.5rem;
        }
        
        .bg-success { background-color: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.2); }
        .bg-danger { background-color: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; border: 1px solid rgba(239, 68, 68, 0.2); }
        .bg-info { background-color: rgba(59, 130, 246, 0.1) !important; color: #60a5fa !important; border: 1px solid rgba(59, 130, 246, 0.2); }

        .ticker-font { font-family: 'JetBrains Mono', monospace; }

        /* Animation nodes */
        .stat-node {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .stat-node:hover {
            transform: translateY(-5px);
        }
        /* Pagination Styling */
        .pagination {
            gap: 5px;
        }
        .page-link {
            background-color: rgba(16, 185, 129, 0.05) !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
            color: #10b981 !important;
            border-radius: 8px !important;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        .page-link:hover {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            transform: translateY(-2px);
        }
        .page-item.active .page-link {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #05160c !important;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .page-item.disabled .page-link {
            background-color: transparent !important;
            border-color: rgba(16, 185, 129, 0.05) !important;
            color: rgba(16, 185, 129, 0.3) !important;
        }
        /* Input & Placeholder Visibility */
        input::placeholder, textarea::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        .form-control, .form-select {
            background-color: #062817 !important; /* Hijau gelap */
            color: #ffffff !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        .form-control:focus, .form-select:focus {
            background-color: #05160c !important;
            color: #ffffff !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;
        }
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        textarea:-webkit-autofill:hover,
        textarea:-webkit-autofill:focus,
        select:-webkit-autofill,
        select:-webkit-autofill:hover,
        select:-webkit-autofill:focus {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0px 1000px #062817 inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        .alert-success {
            background-color: #10b981 !important; /* Hijau terang */
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4) !important;
        }
        .alert-success .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .dropdown-menu {
            background-color: #062817 !important;
            border: 1px solid rgba(16, 185, 129, 0.3) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
        }
        .dropdown-item {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.2s;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #10b981 !important;
        }
    </style>
</head>
<body>

    @php
        $appName = Auth::check() && Auth::user()->tenant ? strtoupper(Auth::user()->tenant->name) : 'ALGROW CAPITAL';
        $showLogo = Auth::check() && Auth::user()->tenant ? false : true;
    @endphp

    <!-- Mobile Header -->
    <div class="mobile-header">
        <div class="fw-bold d-flex align-items-center">
            @if($showLogo)
                <img src="{{ asset('images/logo.png') }}" alt="{{ $appName }}" height="30" onerror="this.outerHTML='<i class=\'fa-solid fa-chart-simple text-success-custom me-2\'></i> {{ $appName }}'">
            @else
                <div style="font-size: 1.1rem; letter-spacing: 1px;"><i class="fa-solid fa-building text-emerald-500 me-2"></i> {{ $appName }}</div>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn text-emerald-400 p-0 fs-5 d-lg-none" id="fullscreenToggle" onclick="toggleFullScreen()">
                <i class="fa-solid fa-expand" id="fullscreenIcon"></i>
            </button>
            <button class="btn text-white p-0 fs-4" id="sidebarToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Overlay for Sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Logo Desktop Saja -->
        <div class="sidebar-brand text-center pt-4 pb-2 d-none d-lg-block">
            @if($showLogo)
                <img src="{{ asset('images/logo.png') }}" alt="{{ $appName }}" class="img-fluid" style="max-width: 180px; width: 80%;" onerror="this.outerHTML='<i class=\'fa-solid fa-chart-simple text-success-custom me-2\'></i> {{ $appName }}'">
            @else
                <div style="font-size: 1.1rem; letter-spacing: 1px;"><i class="fa-solid fa-building text-emerald-500 me-2"></i> {{ $appName }}</div>
            @endif
        </div>
        
        @auth
        <div class="px-4 py-3 border-bottom border-white border-opacity-10 mt-3 mt-lg-0">
            <div class="d-flex align-items-center">
                <div class="bg-primary-custom text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px; border: 2px solid rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="fw-bold small text-truncate" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                    <div class="text-white-50 text-truncate mb-1" style="font-size: 0.7rem;">{{ '@' . Auth::user()->username }}</div>
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50" style="font-size: 0.6rem; text-transform: uppercase;">{{ Auth::user()->role }}</span>
                    @if(Auth::user()->tenant)
                        <div class="text-emerald-400 text-truncate mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-building me-1"></i> {{ Auth::user()->tenant->name }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endauth

        <div class="nav flex-column mt-3">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge me-2"></i> Dashboard
            </a>

            <a href="{{ route('my-profile.edit') }}" class="nav-link {{ request()->routeIs('my-profile.edit') ? 'active' : '' }}">
                <i class="fa-solid fa-user-pen me-2"></i> Profil Saya
            </a>

            @if(Auth::user()->role === 'user')
            <a href="{{ route('user-tasks.index') }}" class="nav-link {{ request()->routeIs('user-tasks.index') || request()->routeIs('user-tasks.allotment') || request()->routeIs('user-tasks.sale') ? 'active' : '' }}">
                <i class="fa-solid fa-bolt me-2"></i> Eksekusi IPO
            </a>
            <a href="{{ route('user-tasks.profit') }}" class="nav-link {{ request()->routeIs('user-tasks.profit') ? 'active' : '' }}">
                <i class="fa-solid fa-hand-holding-dollar me-2"></i> Pembagian Profit
            </a>
            @endif

            {{-- Fitur Khusus Investor --}}
            @if(Auth::user()->role === 'investor')
                @if(Auth::user()->investor)
                <a href="{{ route('investors.show', Auth::user()->investor->id) }}" class="nav-link {{ request()->routeIs('investors.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-tie me-2"></i> Profil Investor
                </a>
                <a href="{{ route('investors.portfolio', Auth::user()->investor->id) }}" class="nav-link {{ request()->routeIs('investors.portfolio') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group me-2"></i> Partisipasi Emiten
                </a>
                @endif
            @endif

            {{-- Fitur Administratif: Untuk Admin, Owner, dan Developer --}}
            @if(in_array(Auth::user()->role, ['admin', 'owner', 'developer']))
            @if(in_array(Auth::user()->role, ['owner', 'developer']))
            <a href="{{ route('investors.index') }}" class="nav-link {{ request()->routeIs('investors.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users me-2"></i> Investor
            </a>
            @endif
            <a href="{{ route('mitra-accounts.index') }}" class="nav-link {{ request()->routeIs('mitra-accounts.index') ? 'active' : '' }}">
                <i class="fa-solid fa-id-card me-2"></i> Daftar Akun
            </a>
            {{--
            <a href="{{ route('mitra-accounts.grid') }}" class="nav-link {{ request()->routeIs('mitra-accounts.grid') ? 'active' : '' }}">
                <i class="fa-solid fa-th-large me-2"></i> Katalog Mitra
            </a>
            --}}
            <a href="{{ route('mitra-groups.index') }}" class="nav-link {{ request()->routeIs('mitra-groups.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-viewfinder me-2"></i> Distribusi Handler
            </a>
            <a href="{{ route('ipos.index') }}" class="nav-link {{ request()->routeIs('ipos.*') && !request()->routeIs('ipos.report') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-trend-up me-2"></i> Manajemen IPO
            </a>
            @if(in_array(Auth::user()->role, ['owner', 'developer']))
            <a href="{{ route('ipos.report') }}" class="nav-link {{ request()->routeIs('ipos.report') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i> Laporan Arus Dana
            </a>
            <a href="{{ route('profit-distribution.index') }}" class="nav-link {{ request()->routeIs('profit-distribution.*') ? 'active' : '' }}">
                <i class="fa-solid fa-hand-holding-dollar me-2"></i> Pembagian Profit
            </a>
            @endif
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                <i class="fa-solid fa-fw fa-users-gear me-2"></i> Manajemen User
            </a>
            @endif

            {{-- Fitur Master: Hanya untuk Developer --}}
            @if(Auth::user()->role === 'developer')
            <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                <i class="fa-solid fa-fw fa-building me-2"></i> Daftar Klien
            </a>
            @endif
            
            <hr class="border-white border-opacity-25 mx-3 my-2">
            
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link text-danger mt-auto">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 desktop-title">
            <h2 class="fw-bold text-gray-800">@yield('title')</h2>
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <div class="fw-bold small">{{ Auth::user()->name }}</div>
                    <div class="text-muted mb-1" style="font-size: 0.7rem;">{{ ucfirst(Auth::user()->role) }}</div>
                    @if(Auth::user()->tenant)
                        <span class="badge bg-emerald-900 border border-emerald-500 text-emerald-100 shadow-sm" style="font-size: 0.65rem;">
                            <i class="fa-solid fa-building me-1"></i> {{ Auth::user()->tenant->name }}
                        </span>
                    @endif
                </div>
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile Title -->
        <h3 class="fw-bold text-gray-800 d-lg-none mb-4">@yield('title')</h3>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <ul class="mb-0 ps-3" style="list-style-type: disc;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar toggle logic
            $('#sidebarToggle, #sidebarOverlay').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#sidebarOverlay').toggleClass('active');
            });

            // Close sidebar when clicking a link on mobile
            $('.sidebar .nav-link').on('click', function() {
                if ($(window).width() < 992) {
                    $('#sidebar').removeClass('active');
                    $('#sidebarOverlay').removeClass('active');
                }
            });
        });

        // Global SweetAlert2 Delete Confirmation
        function confirmDelete(e, message) {
            e.preventDefault();
            const form = e.target.closest('form');
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#374151',
                confirmButtonText: '<i class="fa-solid fa-trash-can me-2"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#05160c',
                color: '#ffffff',
                customClass: {
                    popup: 'border border-emerald-900 border-opacity-50 rounded-4 shadow-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered: ', registration);
                }).catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement &&    // alternative standard method
                !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement ) {  // current working methods
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
                document.getElementById('fullscreenIcon').classList.remove('fa-expand');
                document.getElementById('fullscreenIcon').classList.add('fa-compress');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                document.getElementById('fullscreenIcon').classList.remove('fa-compress');
                document.getElementById('fullscreenIcon').classList.add('fa-expand');
            }
        }
    </script>
    @if(Auth::check() && !in_array(Auth::user()->role, ['developer', 'owner']))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let lastBroadcastId = localStorage.getItem('last_broadcast_id') || 0;
            
            setInterval(() => {
                fetch('/api/check-broadcast')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.message && data.id > lastBroadcastId) {
                            lastBroadcastId = data.id;
                            localStorage.setItem('last_broadcast_id', data.id);
                            
                            Swal.fire({
                                title: 'Sapaan dari Pusat 👑',
                                text: data.message,
                                icon: 'info',
                                toast: true,
                                position: 'top',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                background: '#0f172a',
                                color: '#38bdf8'
                            });
                        }
                    })
                    .catch(e => {}); // Silent catch
            }, 3000); // Check every 3 seconds
        });
    </script>
    @endif
    @yield('scripts')
</body>
</html>
