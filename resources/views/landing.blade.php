<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#021a14">
    <title>Algrow Capital - Investasi Saham IPO</title>
    
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
        /* CSS animations from modern UI */
        html, body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #021a14;
            color: #ffffff;
            overflow-x: hidden;
        }

        .grid-bg {
            background-image:
                linear-gradient(rgba(52,211,153,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(52,211,153,.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
        }

        .glass-nav {
            background: rgba(2, 26, 20, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(4, 47, 34, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
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
                rgba(255,255,255,.15) 50%,
                transparent 80%
            );
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%,100% { left: -100%; }
            50% { left: 150%; }
        }

        .entrance {
            opacity: 0;
            transform: translateY(24px);
            animation: enterUp .8s cubic-bezier(.16,1,.3,1) forwards;
        }
        @keyframes enterUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .text-gradient {
            background: linear-gradient(to right, #34d399, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen grid-bg relative selection:bg-accent selection:text-base">

    <!-- Background effects -->
    <div class="blob w-[600px] h-[600px] bg-emerald-500/[.05] -top-32 -left-32"></div>
    <div class="blob w-[500px] h-[500px] bg-teal-400/[.04] top-1/2 -right-40"></div>
    <div class="blob w-[400px] h-[400px] bg-emerald-300/[.03] bottom-0 left-1/3"></div>

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-20 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <span class="text-xl font-extrabold text-white">A</span>
                </div>
                <span class="text-xl font-bold tracking-tight text-white">Algrow Capital</span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.05] text-white font-medium text-sm transition-all">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.05] text-white font-medium text-sm transition-all hidden sm:block">
                        Sign In
                    </a>
                    <a href="{{ route('login') }}" class="btn-shimmer px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-105 transition-all">
                        Login to Portal
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="relative z-10 pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 sm:px-12 lg:px-20 max-w-7xl mx-auto flex flex-col items-center text-center min-h-[100dvh]">
        
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card mb-8 entrance" style="animation-delay: 0.1s">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
            <span class="text-sm font-medium text-emerald-200">Mitra Investasi IPO Terpercaya</span>
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-8 entrance" style="animation-delay: 0.2s">
            Pertumbuhan Bersama di <br class="hidden sm:block" />
            <span class="text-gradient">Pasar Saham IPO</span>
        </h1>

        <div class="max-w-4xl glass-card rounded-[32px] p-8 sm:p-12 text-left sm:text-center entrance mx-auto" style="animation-delay: 0.3s">
            <p class="text-slate-300 text-base sm:text-lg leading-relaxed space-y-6">
                <strong class="text-white font-semibold">Algrow Capital</strong> merupakan konsep kerja sama investasi yang berfokus pada saham-saham baru listing atau IPO. Dalam kerja sama ini, Mitra tidak perlu menyediakan modal investasi, melainkan menyediakan akun sekuritas seperti Stockbit atau Ajaib yang telah disepakati untuk dikelola. 
                <br><br>
                Modal investasi berasal dari pihak Algrow Capital, kemudian dana tersebut digunakan untuk melakukan investasi pada saham-saham baru listing yang dianggap memiliki potensi keuntungan. Apabila investasi menghasilkan keuntungan, profit akan dibagikan antara Algrow Capital dan partner sesuai dengan kesepakatan yang telah dibuat. 
                <br><br>
                Konsep ini bertujuan menciptakan hubungan kerja sama yang saling menguntungkan, di mana partner memperoleh peluang mendapatkan penghasilan dari akun yang dimilikinya tanpa harus menyediakan modal investasi, sedangkan Algrow Capital dapat meningkatkan peluang memperoleh alokasi saham IPO melalui pengelolaan beberapa akun. Dengan adanya pengelolaan yang terstruktur, transparansi pembagian keuntungan, serta strategi investasi yang telah ditentukan, Algrow Capital menawarkan konsep investasi yang berorientasi pada pertumbuhan dan keuntungan bersama.
            </p>
        </div>

        <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center items-center entrance w-full" style="animation-delay: 0.4s">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-shimmer w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-lg shadow-xl shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    Ke Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-shimmer w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-lg shadow-xl shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    Masuk ke Portal
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endauth
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/[0.05] bg-base">
        <div class="max-w-7xl mx-auto px-6 sm:px-12 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <p>&copy; {{ date('Y') }} Algrow Capital. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>
