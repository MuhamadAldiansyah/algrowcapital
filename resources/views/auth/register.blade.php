<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#021a14">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <title>Create Account - Algrow Capital</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        base: '#021a14',
                        surface: '#042f22',
                        accent: '#34d399',
                        accentDim: '#065f46',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        html, body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            width: 100%;
            touch-action: manipulation;
        }

        /* ── Animated rotating border (Hardware Accelerated) ── */
        .card-border {
            position: relative;
            overflow: hidden;
            background: rgba(4, 47, 34, 0.5);
        }
        .card-border::before {
            content: "";
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: conic-gradient(transparent, rgba(52, 211, 153, 0.8), transparent 25%);
            animation: rotateBorder 4s linear infinite;
            pointer-events: none;
        }
        @keyframes rotateBorder {
            100% { transform: rotate(360deg); }
        }

        /* ── Floating label ── */
        .float-group { position: relative; }
        .float-group input, .float-group select {
            transition: border-color .3s, box-shadow .3s, background .3s;
        }
        .float-group label {
            position: absolute;
            left: 48px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(148,163,184,.7);
            font-size: .95rem;
            font-weight: 500;
            pointer-events: none;
            transition: all .25s cubic-bezier(.4,0,.2,1);
            background: transparent;
            padding: 0 4px;
        }
        .float-group input:focus ~ label,
        .float-group input:not(:placeholder-shown) ~ label,
        .float-group select:focus ~ label,
        .float-group select:valid ~ label {
            top: 0;
            left: 16px;
            font-size: .65rem;
            font-weight: 700;
            color: #34d399;
            background: linear-gradient(to bottom, rgba(4,47,34,.95) 50%, rgba(4,47,34,.6) 100%);
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        /* Error state override */
        .float-group.has-error input:focus ~ label,
        .float-group.has-error input:not(:placeholder-shown) ~ label,
        .float-group.has-error select:focus ~ label,
        .float-group.has-error select:valid ~ label {
            color: #f87171;
            background: linear-gradient(to bottom, rgba(4,47,34,.95) 50%, rgba(4,47,34,.6) 100%);
        }
        .float-group.has-error input, .float-group.has-error select {
            border-color: rgba(248,113,113,.4);
        }
        .float-group.has-error input:focus, .float-group.has-error select:focus {
            border-color: rgba(248,113,113,.6);
            box-shadow: 0 0 0 4px rgba(248,113,113,.08);
        }

        /* ── Shimmer button ── */
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%; height: 100%;
            background: linear-gradient(
                105deg,
                transparent 20%,
                rgba(255,255,255,.15) 50%,
                transparent 80%
            );
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%,100% { left: -100%; }
            50% { left: 150%; }
        }

        /* ── Particles (Hardware Accelerated) ── */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            will-change: transform, opacity;
            animation: float linear infinite;
        }
        @keyframes float {
            0%   { transform: translate3d(0, 0, 0) scale(1); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translate3d(0, -100vh, 0) scale(0.3); opacity: 0; }
        }

        /* ── Mesh blobs (Static for performance) ── */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px); 
        }

        /* ── Entrance stagger ── */
        .entrance {
            opacity: 0;
            transform: translateY(24px);
            animation: enterUp .7s cubic-bezier(.16,1,.3,1) forwards;
        }
        @keyframes enterUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Pulse ring on logo ── */
        .logo-ring {
            animation: pulseRing 3s ease-in-out infinite;
        }
        @keyframes pulseRing {
            0%,100% { box-shadow: 0 0 0 0 rgba(52,211,153,.4); }
            50%     { box-shadow: 0 0 0 16px rgba(52,211,153,0); }
        }

        /* ── Subtle grid ── */
        .grid-bg {
            background-image:
                linear-gradient(rgba(52,211,153,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(52,211,153,.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(52,211,153,.2); border-radius: 3px; }

        /* ── Password strength bar ── */
        .strength-segment {
            height: 3px;
            border-radius: 2px;
            background: rgba(255,255,255,.08);
            transition: background .3s;
            flex: 1;
        }
        
        /* ── Search Dropdown ── */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 8px;
            background: #042f22;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            overflow: hidden;
            z-index: 50;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: none;
        }
        .search-results-dropdown.active {
            display: block;
        }
        .search-result-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .search-result-item:hover {
            background: rgba(52,211,153,0.15);
        }
        .search-result-item .icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #34d399;
        }
    </style>
</head>

<body class="min-h-[100dvh] bg-base grid-bg relative overflow-x-hidden overflow-y-auto">
    <!-- Mesh blobs -->
    <div class="blob w-[500px] h-[500px] bg-emerald-500/[.07] -top-48 -left-48" style="animation-delay:0s"></div>
    <div class="blob w-[400px] h-[400px] bg-teal-400/[.06] top-1/2 -right-40" style="animation-delay:-4s"></div>
    <div class="blob w-[350px] h-[350px] bg-emerald-300/[.05] -bottom-32 left-1/3" style="animation-delay:-8s"></div>

    <!-- Particles container -->
    <div id="particles" class="fixed inset-0 pointer-events-none z-0"></div>

    <!-- Main -->
    <div class="relative z-10 min-h-[100dvh] flex flex-col justify-center px-4 py-6 sm:p-8">
        <div class="w-full max-w-[520px] mx-auto my-auto">

            <!-- Animated border wrapper -->
            <div class="card-border rounded-[28px] sm:rounded-[34px] p-[2px] entrance" style="animation-delay:.1s">
                <!-- Inner card -->
                <div class="relative z-10 rounded-[26px] sm:rounded-[32px] bg-surface/90 backdrop-blur-xl px-5 py-8 sm:px-9 sm:py-10 shadow-[0_32px_80px_rgba(0,0,0,.5)]">

                    <!-- Logo -->
                    <div class="text-center mb-8 entrance" style="animation-delay:.25s">
                        <div class="w-[72px] h-[72px] mx-auto rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 flex items-center justify-center logo-ring mb-5 rotate-3 hover:rotate-0 transition-transform duration-500">
                            <span class="text-[28px] font-extrabold text-white tracking-tight">A</span>
                        </div>

                        <h1 class="text-[28px] font-extrabold tracking-tight">
                            <span class="bg-gradient-to-r from-emerald-200 via-accent to-teal-400 bg-clip-text text-transparent">
                                Algrow Capital
                            </span>
                        </h1>

                        <p class="text-slate-400 mt-2.5 text-[13px] font-medium tracking-wide" id="step-subtitle">
                            Create your new account
                        </p>

                        <!-- Progress Dots (Dynamic) -->
                        <div class="flex justify-center items-center gap-2 mt-5 mb-2 entrance" style="animation-delay:.3s" id="progress-dots">
                            <!-- Filled dynamically by JS -->
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('register') }}" method="POST" id="registerForm" onkeydown="return handleEnter(event)">
                        @csrf
                        <input type="hidden" name="role_type" id="role_type" value="{{ old('role_type') }}">
                        <input type="hidden" name="mitra_account_id" id="mitra_account_id" value="{{ old('mitra_account_id') }}">
                        
                        <!-- STEP 0: Role Selection -->
                        <div id="step-0" class="step-section space-y-4">
                            <h2 class="text-white text-lg font-bold text-center mb-4">Select Your Role</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" onclick="selectRole('owner')" class="h-[120px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex flex-col items-center justify-center hover:bg-white/[.08] hover:border-accent/50 hover:-translate-y-1 transition-all gap-2 entrance" style="animation-delay:.3s">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="font-bold text-[14px]">Asset manager</span>
                                </button>
                                <button type="button" onclick="window.location.href='https://api.whatsapp.com/send?phone=6287822421207&text=Halo%20Tim%20Algrow%20Capital%2C%20saya%20tertarik%20untuk%20bergabung%20dan%20bekerja%20sama%20sebagai%20Mitra%20penyedia%20akun%20sekuritas.%20Mohon%20informasi%20lebih%20lanjut%20mengenai%20syarat%20dan%20prosedur%20pendaftarannya.%20Terima%20kasih.'" class="h-[120px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex flex-col items-center justify-center hover:bg-white/[.08] hover:border-accent/50 hover:-translate-y-1 transition-all gap-2 entrance" style="animation-delay:.35s">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span class="font-bold text-[14px]">Mitra</span>
                                </button>
                            </div>
                        </div>

                        <!-- STEP: Company Name (Fund Manager Only) -->
                        <div id="step-company" class="step-section hidden space-y-4">
                            <h2 class="text-white text-lg font-bold text-center mb-4">Informasi Grup Anda</h2>
                            <div class="float-group @error('company_name') has-error @enderror entrance" style="animation-delay:.1s">
                                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder=" " class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-4 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>
                                <label>Nama Grup / Perusahaan Anda</label>
                                @error('company_name') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-3 mt-4 entrance" style="animation-delay:.2s">
                                <button type="button" onclick="prevStep()" class="w-[60px] h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex items-center justify-center hover:bg-white/[.08] transition-all"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></button>
                                <button type="button" onclick="nextStep()" class="flex-1 btn-shimmer h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200 flex items-center justify-center gap-2.5">
                                    Lanjut <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP: Tenant Selection (Mitra Only) -->
                        <div id="step-tenant" class="step-section hidden space-y-4">
                            <h2 class="text-white text-lg font-bold text-center mb-4">Pilih Perusahaan Induk</h2>
                            <div class="float-group @error('tenant_id') has-error @enderror entrance" style="animation-delay:.1s">
                                <select id="tenant_id" name="tenant_id" class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-4 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all appearance-none cursor-pointer">
                                    <option value="" class="bg-surface text-white">-- Pilih Perusahaan --</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                <label style="top:0;left:16px;font-size:.65rem;font-weight:700;color:#34d399;background:linear-gradient(to bottom, rgba(4,47,34,.95) 50%, rgba(4,47,34,.6) 100%);letter-spacing:.06em;text-transform:uppercase;">Pilih Perusahaan</label>
                                @error('tenant_id') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-3 mt-4 entrance" style="animation-delay:.2s">
                                <button type="button" onclick="prevStep()" class="w-[60px] h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex items-center justify-center hover:bg-white/[.08] transition-all"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></button>
                                <button type="button" onclick="nextStep()" class="flex-1 btn-shimmer h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200 flex items-center justify-center gap-2.5">
                                    Lanjut <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP: Mitra Account Selection (Mitra Only) -->
                        <div id="step-mitra" class="step-section hidden space-y-4">
                            <h2 class="text-white text-lg font-bold text-center mb-4">Cari Akun Mitra Anda</h2>
                            <div class="relative z-20">
                                <div class="float-group @error('mitra_account_id') has-error @enderror entrance" style="animation-delay:.1s">
                                    <input type="text" id="mitra_search_input" placeholder=" " autocomplete="off" oninput="searchMitraDebounce(this.value)" onfocus="document.getElementById('search_mitra_dropdown').classList.add('active')" class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                    
                                    <svg id="mitra_selected_icon" xmlns="http://www.w3.org/2000/svg" class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-accent hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    
                                    <label>Ketik Nama Mitra Anda</label>
                                    @error('mitra_account_id') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                                </div>
                                <!-- Dropdown Results -->
                                <div id="search_mitra_dropdown" class="search-results-dropdown">
                                    <div class="p-3 text-sm text-slate-400 text-center" id="search_mitra_info">Ketik nama untuk mencari...</div>
                                    <div id="search_mitra_list" class="flex flex-col"></div>
                                </div>
                            </div>
                            
                            <div class="flex gap-3 mt-4 entrance" style="animation-delay:.2s">
                                <button type="button" onclick="prevStep()" class="w-[60px] h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex items-center justify-center hover:bg-white/[.08] transition-all"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></button>
                                <button type="button" onclick="nextStep()" class="flex-1 btn-shimmer h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200 flex items-center justify-center gap-2.5">
                                    Lanjut <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP: Email -->
                        <div id="step-email" class="step-section hidden space-y-4">
                            <h2 class="text-white text-lg font-bold text-center mb-4">Verifikasi Email Anda</h2>
                            <div class="float-group @error('email') has-error @enderror entrance" style="animation-delay:.1s">
                                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" " class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-4 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                <label>Email Address</label>
                                @error('email') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div class="flex gap-3 mt-4 entrance" style="animation-delay:.2s">
                                <button type="button" onclick="prevStep()" class="w-[60px] h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex items-center justify-center hover:bg-white/[.08] transition-all"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></button>
                                <button type="button" onclick="nextStep()" class="flex-1 btn-shimmer h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200 flex items-center justify-center gap-2.5">
                                    Lanjut <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP: Password & Submit -->
                        <div id="step-password" class="step-section hidden space-y-4">
                            <h2 class="text-white text-lg font-bold text-center mb-4">Atur Kata Sandi</h2>
                            <div class="float-group @error('password') has-error @enderror entrance" style="animation-delay:.1s">
                                <input type="password" id="password" name="password" placeholder=" " class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                <label>Password</label>
                                <button type="button" onclick="togglePassword('password','pw-icon-1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-accent transition-colors p-1"><svg id="pw-icon-1" xmlns="http://www.w3.org/2000/svg" class="h-[16px] w-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                                @error('password') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="float-group entrance" style="animation-delay:.2s">
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder=" " class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                <label>Confirm Password</label>
                                <button type="button" onclick="togglePassword('password_confirmation','pw-icon-2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-accent transition-colors p-1"><svg id="pw-icon-2" xmlns="http://www.w3.org/2000/svg" class="h-[16px] w-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                            </div>

                            <div class="entrance" style="animation-delay:.3s">
                                <div class="flex gap-1.5 px-1" id="strength-bar">
                                    <div class="strength-segment" data-i="0"></div><div class="strength-segment" data-i="1"></div><div class="strength-segment" data-i="2"></div><div class="strength-segment" data-i="3"></div>
                                </div>
                                <p class="text-[11px] font-medium mt-1.5 px-1 transition-colors" id="strength-text" style="color:rgba(148,163,184,.5)"></p>
                            </div>

                            <div class="flex gap-3 mt-4 entrance" style="animation-delay:.4s">
                                <button type="button" onclick="prevStep()" class="w-[60px] h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] text-white flex items-center justify-center hover:bg-white/[.08] transition-all"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></button>
                                <button type="submit" class="flex-1 btn-shimmer h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all flex items-center justify-center gap-2.5">
                                    Buat Akun <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="flex items-center gap-4 my-6 entrance" style="animation-delay:.70s">
                        <div class="flex-1 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                        <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-widest">or</span>
                        <div class="flex-1 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                    </div>

                    <!-- Login link -->
                    <p class="text-center text-slate-400 text-[13px] font-medium entrance" style="animation-delay:.76s">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-accent hover:text-emerald-200 font-bold transition-colors">
                            Sign In
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-slate-600 text-[11px] mt-6 font-medium tracking-wide entrance" style="animation-delay:.82s">
                &copy; 2025 Algrow Capital. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // ── Navigation Flow Logic ──
        const flows = {
            owner: ['step-0', 'step-company', 'step-email', 'step-password'],
            user: ['step-0', 'step-tenant', 'step-mitra', 'step-email', 'step-password']
        };
        
        let currentFlow = [];
        let currentStepIndex = 0;
        
        // Check for old validation errors to restore state
        const oldRole = "{{ old('role_type') }}";
        const hasPasswordError = {{ $errors->has('password') ? 'true' : 'false' }};
        const hasEmailError = {{ $errors->has('email') ? 'true' : 'false' }};
        
        if (oldRole) {
            document.getElementById('role_type').value = oldRole;
            currentFlow = flows[oldRole];
            
            // Determine step based on errors
            if (hasPasswordError) {
                currentStepIndex = currentFlow.indexOf('step-password');
            } else if (hasEmailError) {
                currentStepIndex = currentFlow.indexOf('step-email');
            } else {
                currentStepIndex = 1; // Default to first input step
            }
            
            if (oldRole === 'user') {
                fetchCompanies(); // Ensure companies are loaded
                if ('{{ old("mitra_account_id") }}') {
                    // Pre-fill mitra selected state (dummy display)
                    document.getElementById('mitra_search_input').value = "Mitra ID: {{ old('mitra_account_id') }}";
                    document.getElementById('mitra_selected_icon').classList.remove('hidden');
                }
            }
            updateView();
        } else {
            currentFlow = flows.owner; // Fallback
            currentStepIndex = 0;
            updateView();
        }

        function selectRole(role) {
            document.getElementById('role_type').value = role;
            currentFlow = flows[role];
            currentStepIndex = 1;
            
            if (role === 'user') {
                fetchCompanies();
            }
            updateView();
        }

        function nextStep() {
            if (!validateCurrentStep()) return;
            if (currentStepIndex < currentFlow.length - 1) {
                currentStepIndex++;
                updateView();
            }
        }

        function prevStep() {
            if (currentStepIndex > 0) {
                currentStepIndex--;
                updateView();
            }
        }

        function updateView() {
            // Hide all steps
            document.querySelectorAll('.step-section').forEach(el => el.classList.add('hidden'));
            
            // Show current step
            const currentStepId = currentFlow[currentStepIndex];
            const currentStepEl = document.getElementById(currentStepId);
            currentStepEl.classList.remove('hidden');
            
            // Retrigger animations
            const entrances = currentStepEl.querySelectorAll('.entrance');
            entrances.forEach(el => {
                el.style.animation = 'none';
                el.offsetHeight; // trigger reflow
                el.style.animation = null;
            });
            
            // Update dots
            const totalDots = currentFlow.length - 1; // Step 0 doesn't count as a step in progress
            const dotsContainer = document.getElementById('progress-dots');
            
            if (currentStepIndex === 0) {
                dotsContainer.innerHTML = '';
            } else {
                let html = '';
                for (let i = 1; i <= totalDots; i++) {
                    if (i === currentStepIndex) {
                        html += '<div class="w-8 h-1.5 rounded-full bg-accent transition-all duration-300"></div>';
                    } else if (i < currentStepIndex) {
                        html += '<div class="w-2 h-1.5 rounded-full bg-emerald-500/50 transition-all duration-300"></div>';
                    } else {
                        html += '<div class="w-2 h-1.5 rounded-full bg-white/10 transition-all duration-300"></div>';
                    }
                }
                dotsContainer.innerHTML = html;
            }
        }

        function validateCurrentStep() {
            const stepId = currentFlow[currentStepIndex];
            let isValid = true;
            
            if (stepId === 'step-company') {
                const input = document.getElementById('company_name');
                if (!input.value.trim()) {
                    input.closest('.float-group').classList.add('has-error');
                    isValid = false;
                } else {
                    input.closest('.float-group').classList.remove('has-error');
                }
            } else if (stepId === 'step-tenant') {
                const input = document.getElementById('tenant_id');
                if (!input.value) {
                    input.closest('.float-group').classList.add('has-error');
                    isValid = false;
                } else {
                    input.closest('.float-group').classList.remove('has-error');
                }
            } else if (stepId === 'step-mitra') {
                const input = document.getElementById('mitra_account_id');
                if (!input.value) {
                    document.getElementById('mitra_search_input').closest('.float-group').classList.add('has-error');
                    isValid = false;
                } else {
                    document.getElementById('mitra_search_input').closest('.float-group').classList.remove('has-error');
                }
            } else if (stepId === 'step-email') {
                const input = document.getElementById('email');
                if (!input.value.trim() || !input.value.includes('@')) {
                    input.closest('.float-group').classList.add('has-error');
                    isValid = false;
                } else {
                    input.closest('.float-group').classList.remove('has-error');
                }
            }
            
            return isValid;
        }

        function handleEnter(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const stepId = currentFlow[currentStepIndex];
                if (stepId !== 'step-password') {
                    nextStep();
                } else {
                    document.getElementById('registerForm').submit();
                }
                return false;
            }
            return true;
        }

        // ── Data Fetching Logic ──
        function fetchCompanies() {
            const tenantSelect = document.getElementById('tenant_id');
            if (tenantSelect.options.length <= 1) {
                fetch('/register/companies')
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(company => {
                            const option = document.createElement('option');
                            option.value = company.id;
                            option.textContent = company.name;
                            option.className = 'bg-surface text-white';
                            tenantSelect.appendChild(option);
                        });
                        if('{{ old("tenant_id") }}') {
                            tenantSelect.value = '{{ old("tenant_id") }}';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching companies:', error);
                    });
            }
        }
        
        let searchTimeout = null;
        function searchMitraDebounce(query) {
            const tenantId = document.getElementById('tenant_id').value;
            const dropdown = document.getElementById('search_mitra_dropdown');
            const info = document.getElementById('search_mitra_info');
            const list = document.getElementById('search_mitra_list');
            
            // Reset selection if typing
            document.getElementById('mitra_account_id').value = '';
            document.getElementById('mitra_selected_icon').classList.add('hidden');
            
            if (!query || query.length < 2) {
                list.innerHTML = '';
                info.textContent = 'Ketik minimal 2 karakter...';
                info.style.display = 'block';
                return;
            }
            
            info.textContent = 'Mencari...';
            info.style.display = 'block';
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetch(`/register/search-tenant-mitras/${tenantId}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        list.innerHTML = '';
                        if (data.length === 0) {
                            info.textContent = 'Nama mitra tidak ditemukan.';
                        } else {
                            info.style.display = 'none';
                            data.forEach(mitra => {
                                const div = document.createElement('div');
                                div.className = 'search-result-item';
                                const initial = mitra.owner_name.charAt(0).toUpperCase();
                                div.innerHTML = `
                                    <div class="icon">${initial}</div>
                                    <div class="flex-1">
                                        <div class="text-white font-bold text-sm">${mitra.owner_name}</div>
                                        <div class="text-slate-400 text-xs">${mitra.platform || 'Bebas'}</div>
                                    </div>
                                `;
                                div.onclick = () => selectMitra(mitra.id, mitra.owner_name);
                                list.appendChild(div);
                            });
                        }
                    });
            }, 300);
        }
        
        function selectMitra(id, name) {
            document.getElementById('mitra_account_id').value = id;
            const searchInput = document.getElementById('mitra_search_input');
            searchInput.value = name;
            
            document.getElementById('search_mitra_dropdown').classList.remove('active');
            document.getElementById('mitra_selected_icon').classList.remove('hidden');
            searchInput.closest('.float-group').classList.remove('has-error');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const input = document.getElementById('mitra_search_input');
            const dropdown = document.getElementById('search_mitra_dropdown');
            if (input && !input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });

        // ── Form Visuals ──
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            icon.innerHTML = isPassword
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
        }

        // Icon color on focus
        document.querySelectorAll('.float-group input, .float-group select').forEach(input => {
            const icon = input.parentElement.querySelector('svg:first-child');
            if (icon) {
                input.addEventListener('focus', () => {
                    if (!input.closest('.has-error')) icon.classList.replace('text-slate-500', 'text-accent');
                });
                input.addEventListener('blur', () => {
                    if (!input.closest('.has-error')) icon.classList.replace('text-accent', 'text-slate-500');
                });
            }
        });

        // Password strength
        const pwInput = document.getElementById('password');
        if (pwInput) {
            const segments = document.querySelectorAll('#strength-bar .strength-segment');
            const strengthText = document.getElementById('strength-text');
            const levels = [
                { label: '', color: 'transparent', fills: 0 },
                { label: 'Weak', color: '#f87171', fills: 1 },
                { label: 'Fair', color: '#fb923c', fills: 2 },
                { label: 'Good', color: '#facc15', fills: 3 },
                { label: 'Strong', color: '#34d399', fills: 4 },
            ];

            pwInput.addEventListener('input', () => {
                const v = pwInput.value;
                let score = 0;
                if (v.length >= 6) score++;
                if (v.length >= 10) score++;
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
                if (/[0-9]/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;

                let level = 0;
                if (v.length === 0) level = 0;
                else if (score <= 1) level = 1;
                else if (score <= 2) level = 2;
                else if (score <= 3) level = 3;
                else level = 4;

                const l = levels[level];
                segments.forEach((seg, i) => {
                    seg.style.background = i < l.fills ? l.color : 'rgba(255,255,255,.08)';
                });
                strengthText.textContent = l.label;
                strengthText.style.color = l.color === 'transparent' ? 'rgba(148,163,184,.5)' : l.color;
            });
        }

        // Particles
        (function createParticles() {
            const container = document.getElementById('particles');
            const count = 12; 
            for (let i = 0; i < count; i++) {
                const p = document.createElement('div');
                p.classList.add('particle');
                const size = Math.random() * 4 + 2;
                const left = Math.random() * 100;
                const delay = Math.random() * 12;
                const duration = Math.random() * 10 + 12;
                const opacity = Math.random() * 0.3 + 0.1;
                p.style.cssText = `
                    width:${size}px; height:${size}px;
                    left:${left}%; bottom:-10px;
                    background: rgba(52,211,153,${opacity});
                    animation-delay:${delay}s;
                    animation-duration:${duration}s;
                `; 
                container.appendChild(p);
            }
        })();
    </script>
</body>
</html>