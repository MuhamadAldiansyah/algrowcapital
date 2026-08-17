<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Forgot Password - Algrow Capital</title>
    
    <!-- Tailwind CSS (via CDN for local dev) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Theme Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#059669', // Emerald 600
                        accent: '#34d399', // Emerald 400
                        base: '#020617', // Slate 950
                        surface: '#0f172a', // Slate 900
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
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
        .float-group input {
            transition: border-color .3s, box-shadow .3s, background .3s;
        }
        .float-group label {
            position: absolute;
            left: 48px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(148,163,184,.7);
            font-size: .95rem;
            pointer-events: none;
            transition: all .25s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            padding: 0 4px;
        }
        .float-group input:focus ~ label,
        .float-group input:not(:placeholder-shown) ~ label {
            top: 0;
            left: 40px;
            font-size: .75rem;
            color: #34d399;
            font-weight: 600;
            background: linear-gradient(to bottom, rgba(4,47,34,.95) 50%, rgba(4,47,34,.6) 100%);
            border-radius: 4px;
            letter-spacing: .02em;
        }

        /* ── Button Shimmer ── */
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(30deg);
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer {
            0% { left: -150%; }
            100% { left: 150%; }
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
            animation: fadeUp .6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Background Grid ── */
        .grid-bg {
            background-image: 
                linear-gradient(to right, rgba(52,211,153,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(52,211,153,0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* ── Logo Ring ── */
        .logo-ring {
            box-shadow: inset 0 0 0 2px rgba(255,255,255,.2), 0 8px 16px rgba(52,211,153,.2);
        }
    </style>
</head>

<body class="min-h-[100dvh] bg-base grid-bg relative overflow-hidden">
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

                        <h1 class="text-[28px] font-extrabold tracking-tight mb-2">
                            <span class="bg-gradient-to-r from-emerald-200 via-accent to-teal-400 bg-clip-text text-transparent">
                                Reset Password
                            </span>
                        </h1>
                        <p class="text-slate-400 text-[13.5px] leading-relaxed font-medium tracking-wide max-w-[280px] mx-auto">
                            Enter your email address and we'll send you instructions to reset your password.
                        </p>
                    </div>

                    <!-- Status Message (Placeholder for future functionality) -->
                    @if (session('status'))
                        <div class="mb-6 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 flex items-start gap-3 entrance" style="animation-delay:.3s">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-emerald-300 text-[13px] font-medium leading-relaxed">
                                {{ session('status') }}
                            </p>
                        </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <!-- Email Input -->
                        <div class="float-group entrance" style="animation-delay:.32s">
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   placeholder=" " 
                                   class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-4 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all"
                                   required 
                                   autofocus>
                            
                            <!-- Email Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            
                            <label for="email">Email Address</label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2 entrance" style="animation-delay:.40s">
                            <button type="submit" 
                                    class="btn-shimmer w-full h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-base tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200 flex items-center justify-center gap-2.5">
                                Send Reset Link
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    <!-- Back to Login -->
                    <div class="mt-8 text-center entrance" style="animation-delay:.48s">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-emerald-300 text-[13px] font-semibold transition-colors group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            Back to Sign In
                        </a>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-slate-600 text-[11px] mt-6 font-medium tracking-wide entrance" style="animation-delay:.56s">
                &copy; 2025 Algrow Capital. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // ── Icon color on focus ──
        document.querySelectorAll('.float-group input').forEach(input => {
            const icon = input.parentElement.querySelector('svg:first-child');
            if (icon) {
                input.addEventListener('focus', () => {
                    icon.classList.replace('text-slate-500', 'text-accent');
                });
                input.addEventListener('blur', () => {
                    icon.classList.replace('text-accent', 'text-slate-500');
                });
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
                `; 
                container.appendChild(p);
            }
        })();
    </script>
</body>
</html>
