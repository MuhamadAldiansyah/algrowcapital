<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Reset Password - Algrow Capital</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#059669',
                        accent: '#34d399',
                        base: '#020617',
                        surface: '#0f172a',
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

        .float-group { position: relative; }
        .float-group input { transition: border-color .3s, box-shadow .3s, background .3s; }
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
        
        .float-group.has-error input { border-color: rgba(248,113,113,.4); }
        .float-group.has-error input:focus { border-color: rgba(248,113,113,.6); box-shadow: 0 0 0 4px rgba(248,113,113,.08); }
        .float-group.has-error input:focus ~ label,
        .float-group.has-error input:not(:placeholder-shown) ~ label { color: #f87171; }

        .btn-shimmer { position: relative; overflow: hidden; }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(30deg);
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer { 0% { left: -150%; } 100% { left: 150%; } }

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

        .blob { position: absolute; border-radius: 50%; filter: blur(60px); }

        .entrance { opacity: 0; animation: fadeUp .6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .grid-bg {
            background-image: 
                linear-gradient(to right, rgba(52,211,153,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(52,211,153,0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .logo-ring { box-shadow: inset 0 0 0 2px rgba(255,255,255,.2), 0 8px 16px rgba(52,211,153,.2); }
    </style>
</head>

<body class="min-h-[100dvh] bg-base grid-bg relative overflow-hidden">
    <!-- Mesh blobs -->
    <div class="blob w-[500px] h-[500px] bg-emerald-500/[.07] -top-48 -left-48" style="animation-delay:0s"></div>
    <div class="blob w-[400px] h-[400px] bg-teal-400/[.06] top-1/2 -right-40" style="animation-delay:-4s"></div>
    <div class="blob w-[350px] h-[350px] bg-emerald-300/[.05] -bottom-32 left-1/3" style="animation-delay:-8s"></div>

    <div id="particles" class="fixed inset-0 pointer-events-none z-0"></div>

    <div class="relative z-10 min-h-[100dvh] flex flex-col justify-center px-4 py-6 sm:p-8">
        <div class="w-full max-w-[420px] mx-auto my-auto">
            <div class="card-border rounded-[28px] sm:rounded-[34px] p-[2px] entrance" style="animation-delay:.1s">
                <div class="relative z-10 rounded-[26px] sm:rounded-[32px] bg-surface/90 backdrop-blur-xl px-5 py-8 sm:px-9 sm:py-10 shadow-[0_32px_80px_rgba(0,0,0,.5)]">

                    <!-- Logo -->
                    <div class="text-center mb-9 entrance" style="animation-delay:.25s">
                        <div class="w-[72px] h-[72px] mx-auto rounded-2xl bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 flex items-center justify-center logo-ring mb-5 rotate-3 hover:rotate-0 transition-transform duration-500">
                            <span class="text-[28px] font-extrabold text-white tracking-tight">A</span>
                        </div>
                        <h1 class="text-[28px] font-extrabold tracking-tight mb-2">
                            <span class="bg-gradient-to-r from-emerald-200 via-accent to-teal-400 bg-clip-text text-transparent">
                                Create New Password
                            </span>
                        </h1>
                        <p class="text-slate-400 text-[13.5px] font-medium tracking-wide">
                            Secure your account with a new password.
                        </p>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email -->
                        <div class="float-group @error('email') has-error @enderror entrance" style="animation-delay:.32s">
                            <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" placeholder=" " 
                                   class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-4 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all" required autofocus>
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <label for="email">Email Address</label>
                            @error('email') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password -->
                        <div class="float-group @error('password') has-error @enderror entrance" style="animation-delay:.38s">
                            <input type="password" id="password" name="password" placeholder=" " class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all" required>
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            <label>New Password</label>
                            <button type="button" onclick="togglePassword('password','pw-icon-1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-accent transition-colors p-1"><svg id="pw-icon-1" xmlns="http://www.w3.org/2000/svg" class="h-[16px] w-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                            @error('password') <p class="text-red-400 text-[11px] font-medium mt-1.5 pl-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="float-group entrance" style="animation-delay:.44s">
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder=" " class="w-full h-[54px] rounded-2xl bg-white/[.04] border border-white/[.08] pl-[48px] pr-12 text-white text-base outline-none focus:border-accent/50 focus:bg-white/[.06] focus:shadow-[0_0_0_4px_rgba(52,211,153,.1)] transition-all" required>
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-slate-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            <label>Confirm Password</label>
                            <button type="button" onclick="togglePassword('password_confirmation','pw-icon-2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-accent transition-colors p-1"><svg id="pw-icon-2" xmlns="http://www.w3.org/2000/svg" class="h-[16px] w-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2 entrance" style="animation-delay:.50s">
                            <button type="submit" class="btn-shimmer w-full h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 hover:scale-[1.015] active:scale-[.985] transition-all duration-200 flex items-center justify-center gap-2.5">
                                Reset Password
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            <p class="text-center text-slate-600 text-[11px] mt-6 font-medium tracking-wide entrance" style="animation-delay:.6s">
                &copy; 2025 Algrow Capital. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        document.querySelectorAll('.float-group input').forEach(input => {
            const icon = input.parentElement.querySelector('svg:first-child');
            if (icon) {
                input.addEventListener('focus', () => { if (!input.closest('.has-error')) icon.classList.replace('text-slate-500', 'text-accent'); });
                input.addEventListener('blur', () => { if (!input.closest('.has-error')) icon.classList.replace('text-accent', 'text-slate-500'); });
            }
        });

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.innerHTML = isPassword
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
        }

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
                p.style.cssText = `width:${size}px; height:${size}px; left:${left}%; bottom:-10px; background: rgba(52,211,153,${opacity}); animation-delay:${delay}s; animation-duration:${duration}s;`;
                container.appendChild(p);
            }
        })();
    </script>
</body>
</html>
