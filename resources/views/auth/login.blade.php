<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#021a14">
    <title>Login - Algrow Capital</title>

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
            touch-action: manipulation; /* Disable double-tap zoom on mobile */
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
        .float-group input {
            transition: border-color .3s, box-shadow .3s;
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
        .float-group input:not(:placeholder-shown) ~ label {
            top: 0;
            left: 16px;
            font-size: .7rem;
            font-weight: 700;
            color: #34d399;
            background: linear-gradient(to bottom, rgba(4,47,34,.95) 50%, rgba(4,47,34,.6) 100%);
            letter-spacing: .06em;
            text-transform: uppercase;
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
            filter: blur(60px); /* Reduced blur, removed animation */
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

        /* ── Checkbox custom ── */
        .chk-custom {
            appearance: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,.15);
            border-radius: 6px;
            background: rgba(255,255,255,.04);
            cursor: pointer;
            position: relative;
            transition: all .2s;
            flex-shrink: 0;
        }
        .chk-custom:checked {
            background: #34d399;
            border-color: #34d399;
        }
        .chk-custom:checked::after {
            content: '';
            position: absolute;
            left: 5px; top: 1px;
            width: 5px; height: 10px;
            border: solid #021a14;
            border-width: 0 2.5px 2.5px 0;
            transform: rotate(45deg);
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
        <div class="w-full max-w-[420px] mx-auto my-auto">

            <!-- Animated border wrapper -->
            <div class="card-border rounded-[28px] sm:rounded-[34px] p-[2px] entrance" style="animation-delay:.1s">
                <!-- Inner card -->
                <div class="relative z-10 rounded-[26px] sm:rounded-[32px] bg-surface/90 backdrop-blur-xl px-5 py-8 sm:px-9 sm:py-10 shadow-[0_32px_80px_rgba(0,0,0,.5)]">

                    <!-- Logo -->
                    <div class="text-center mb-9 entrance" style="animation-delay:.25s">
                        <div class="w-[72px] h-[72px] mx-auto rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 flex items-center justify-center logo-ring mb-5 rotate-3 hover:rotate-0 transition-transform duration-500">
                            <span class="text-[28px] font-extrabold text-white tracking-tight">A</span>
                        </div>

                        <h1 class="text-[28px] font-extrabold tracking-tight">
                            <span class="bg-gradient-to-r from-emerald-200 via-accent to-teal-400 bg-clip-text text-transparent">
                                Algrow Capital
                            </span>
                        </h1>

                        <p class="text-slate-400 mt-2.5 text-[13px] font-medium tracking-wide">
                            Welcome back. Sign in to your account.
                        </p>
                    </div>

                    <!-- Success -->
                    @if(session('success'))
                        <div class="mb-5 entrance" style="animation-delay:.3s">
                            <div class="flex items-start gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-500/[.08] backdrop-blur px-4 py-3.5 text-emerald-300 text-[13px] font-medium">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif

                    <!-- Error -->
                    @if($errors->any())
                        <div class="mb-5 entrance" style="animation-delay:.3s">
                            <div class="flex items-start gap-3 rounded-2xl border border-red-400/15 bg-red-500/[.08] backdrop-blur px-4 py-3.5 text-red-300 text-[13px] font-medium">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                </svg>
                                {{ $errors->first() }}
                            </div>
                        </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Username -->
                        <div class="float-group entrance" style="animation-delay:.35s">
                            <input type="text"
                                   name="username"
                                   value="{{ old('username') }}"
                                   placeholder=" "
                                   class="w-full h-[58px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all"
                                   required autofocus>
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/>
                            </svg>
                            <label>Username / Email</label>
                        </div>

                        <!-- Password -->
                        <div class="float-group entrance" style="animation-delay:.42s">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder=" "
                                   class="w-full h-[58px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all"
                                   required>
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                            <label>Password</label>

                            <button type="button"
                                    onclick="togglePassword()"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-accent transition-colors p-0.5">
                                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Remember / Forgot -->
                        <div class="flex items-center justify-between entrance" style="animation-delay:.48s">
                            <label class="flex items-center gap-2.5 text-slate-400 text-[13px] font-medium cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="chk-custom">
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="text-accent/80 hover:text-accent text-[13px] font-semibold transition-colors">
                                Forgot password?
                            </a>
                        </div>

                        <!-- Submit -->
                        <div class="entrance" style="animation-delay:.54s">
                            <button type="submit"
                                    class="btn-shimmer w-full h-[58px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-base tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200">
                                Sign In
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="flex items-center gap-4 my-6 entrance" style="animation-delay:.6s">
                        <div class="flex-1 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                        <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-widest">or</span>
                        <div class="flex-1 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                    </div>

                    <!-- Sign up link -->
                    <p class="text-center text-slate-400 text-[13px] font-medium entrance" style="animation-delay:.66s">
                        New to Algrow Capital?
                        <a href="{{ route('register') }}" class="text-accent hover:text-emerald-200 font-bold transition-colors">
                            Create account
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-slate-600 text-[11px] mt-6 font-medium tracking-wide entrance" style="animation-delay:.72s">
                &copy; 2025 Algrow Capital. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // ── Toggle password visibility ──
        function togglePassword() {
            const input = document.getElementById('password');
            const open = document.getElementById('eyeOpen');
            const closed = document.getElementById('eyeClosed');
            input.type = input.type === 'password' ? 'text' : 'password';
            open.classList.toggle('hidden');
            closed.classList.toggle('hidden');
        }

        // ── Icon color on focus ──
        document.querySelectorAll('.float-group input').forEach(input => {
            const icon = input.parentElement.querySelector('svg:first-child');
            if (icon) {
                input.addEventListener('focus', () => icon.classList.replace('text-slate-500', 'text-accent'));
                input.addEventListener('blur', () => icon.classList.replace('text-accent', 'text-slate-500'));
            }
        });

        // ── Particles (Optimized) ──
        (function createParticles() {
            const container = document.getElementById('particles');
            const count = 12; // Reduced count
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
                `; // Removed heavy box-shadow
                container.appendChild(p);
            }
        })();
    </script>
</body>
</html>