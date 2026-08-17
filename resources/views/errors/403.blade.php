<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#021a14">
    <title>Akses Ditolak - Algrow Capital</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        base: '#021a14',
                        surface: '#042f22',
                        accent: '#34d399',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
    </style>
</head>
<body class="min-h-screen bg-base grid-bg relative flex items-center justify-center p-4 overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="blob w-[400px] h-[400px] bg-red-500/[.05] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="blob w-[300px] h-[300px] bg-emerald-500/[.05] top-0 left-0"></div>

    <div class="relative z-10 w-full max-w-lg mx-auto text-center">
        <!-- Shield Icon with animation -->
        <div class="mb-8 relative inline-block">
            <div class="absolute inset-0 bg-red-500/20 rounded-full blur-xl animate-pulse"></div>
            <div class="relative w-24 h-24 bg-red-500/10 border border-red-500/20 rounded-3xl flex items-center justify-center transform -rotate-6 hover:rotate-0 transition-transform duration-300">
                <i class="fa-solid fa-shield-halved text-4xl text-red-500"></i>
            </div>
        </div>

        <h1 class="text-4xl sm:text-5xl font-extrabold text-white mb-4 tracking-tight">403</h1>
        <h2 class="text-xl sm:text-2xl font-bold text-red-400 mb-4">ANDA TIDAK MEMILIKI AKSES</h2>
        
        <p class="text-slate-400 text-sm sm:text-base mb-8 max-w-md mx-auto leading-relaxed">
            {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin untuk melihat atau mengakses halaman ini. Jika ini adalah sebuah kesalahan, silakan hubungi Administrator.' }}
        </p>

        <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 h-12 px-8 rounded-full bg-emerald-500/10 text-emerald-400 font-bold border border-emerald-500/20 hover:bg-emerald-500 hover:text-base hover:border-emerald-500 transition-all duration-300 shadow-[0_0_20px_rgba(16,185,129,0.15)] hover:shadow-[0_0_30px_rgba(16,185,129,0.4)]">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
