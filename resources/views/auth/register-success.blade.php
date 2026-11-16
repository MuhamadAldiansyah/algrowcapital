<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#021a14">
    <title>Pendaftaran Berhasil - Algrow Capital</title>

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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .grid-bg {
            background-image:
                linear-gradient(rgba(52,211,153,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(52,211,153,.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }
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
                rgba(255,255,255,.2) 50%,
                transparent 80%
            );
            animation: shimmer 2.5s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%,100% { left: -100%; }
            50% { left: 150%; }
        }
    </style>
</head>

<body class="min-h-[100dvh] bg-base grid-bg relative flex flex-col justify-center px-4 py-8">
    <div class="relative z-10 w-full max-w-[540px] mx-auto">
        <div class="rounded-[30px] bg-surface/95 backdrop-blur-xl px-6 py-8 sm:px-9 sm:py-10 shadow-[0_32px_80px_rgba(0,0,0,.6)] border border-emerald-500/30 text-white text-center">
            
            <!-- Success Icon Animation -->
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/30 ring-8 ring-emerald-500/10 animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>

            <!-- Title & Status -->
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">
                Pendaftaran Terkirim! 🎉
            </h1>
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold mb-5">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>STATUS: MENUNGGU APPROVAL ADMIN</span>
            </div>

            <p class="text-slate-300 text-sm leading-relaxed mb-6">
                Data pendaftaran Anda telah berhasil disimpan di sistem <strong>Algrow Capital</strong>. Langkah terakhir: silakan kirim konfirmasi data Anda ke WhatsApp Admin agar akun Anda langsung diverifikasi dan di-approve.
            </p>

            <!-- WA Big Action Button -->
            <div class="space-y-3 mb-6">
                <a href="{{ $waUrl }}" id="waBtn" target="_blank"
                   class="btn-shimmer w-full h-[60px] rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 text-white font-extrabold text-base tracking-wide shadow-xl shadow-emerald-500/35 hover:shadow-emerald-500/50 hover:scale-[1.02] active:scale-[.98] transition-all flex items-center justify-center gap-3">
                    <!-- WhatsApp Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.148.567 4.167 1.558 5.918l-1.566 5.726 5.867-1.539c1.705.932 3.666 1.471 5.753 1.471 6.627 0 12-5.373 12-12s-5.373-12-12-12zm.049 21.84c-1.84 0-3.579-.496-5.086-1.378l-.364-.213-3.778.991 1.008-3.682-.234-.372c-.96-1.527-1.473-3.308-1.473-5.186 0-5.424 4.414-9.837 9.845-9.837 5.43 0 9.842 4.413 9.843 9.836 0 5.424-4.413 9.843-9.845 9.843z"/>
                    </svg>
                    <span>Kirim Data ke WhatsApp Admin</span>
                </a>
                <p class="text-xs text-emerald-300/80" id="autoRedirectText">
                    Membuka WhatsApp otomatis dalam <span id="countdown" class="font-bold text-white">3</span> detik...
                </p>
            </div>

            <!-- Preview Data Card -->
            @if(isset($summaryData))
            <div class="p-4 rounded-2xl bg-white/[.04] border border-white/[.08] text-left text-xs space-y-2 mb-6">
                <div class="font-bold text-emerald-400 text-sm border-b border-white/10 pb-2 mb-2">
                    📋 Ringkasan Data Pendaftaran:
                </div>
                <div class="grid grid-cols-2 gap-2 text-slate-300">
                    <div><span class="text-slate-500">Nama:</span> <strong class="text-white">{{ $summaryData['name'] }}</strong></div>
                    <div><span class="text-slate-500">No. WA:</span> <strong class="text-white">{{ $summaryData['phone'] }}</strong></div>
                    <div><span class="text-slate-500">Sekuritas:</span> <strong class="text-emerald-300">{{ $summaryData['platform'] }}</strong></div>
                    <div><span class="text-slate-500">User Sekuritas:</span> <strong class="text-white">{{ $summaryData['sekuritas_username'] }}</strong></div>
                    <div><span class="text-slate-500">Bank RDN:</span> <strong class="text-white">{{ $summaryData['bank_rdn'] ?: '-' }}</strong></div>
                    <div><span class="text-slate-500">Bank Pribadi:</span> <strong class="text-white">{{ $summaryData['personal_bank'] ?: '-' }}</strong></div>
                </div>
            </div>
            @endif

            <!-- Navigation Links -->
            <div class="pt-2 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                <a href="{{ route('landing') }}" class="hover:text-emerald-400 transition-colors">
                    &larr; Halaman Utama
                </a>
                <a href="{{ route('login') }}" class="text-emerald-400 font-bold hover:underline">
                    Masuk ke Akun &rarr;
                </a>
            </div>

        </div>
    </div>

    <script>
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        const waUrl = "{{ $waUrl }}";

        const timer = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = waUrl;
            }
        }, 1000);
    </script>
</body>
</html>
