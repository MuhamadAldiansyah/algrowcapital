<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#021a14">
    <title>Algrow Capital - Investasi Saham Tanpa Modal</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        base: '#021a14',
                        surface: '#042f22',
                        surfaceHighlight: '#064a38',
                        accent: '#34d399',
                        accentDim: '#065f46',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Base Setup - Fallback styles for safety */
        html, body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #021a14;
            color: #ffffff;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Background Grid */
        .grid-bg {
            background-image:
                linear-gradient(rgba(52,211,153,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(52,211,153,.03) 1px, transparent 1px);
            background-size: 48px 48px;
            min-height: 100vh;
        }

        /* Glowing Blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        /* Glassmorphism Utilities */
        .glass-nav {
            background: rgba(2, 26, 20, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(4, 47, 34, 0.3);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(52, 211, 153, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            border-color: rgba(52, 211, 153, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -5px rgba(52, 211, 153, 0.1);
        }

        /* Buttons */
        .btn-shimmer {
            position: relative;
            overflow: hidden;
            z-index: 1;
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
            z-index: -1;
        }
        @keyframes shimmer {
            0%,100% { left: -100%; }
            50% { left: 150%; }
        }

        .text-gradient {
            background: linear-gradient(135deg, #34d399 0%, #2dd4bf 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Scroll Animation Classes */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="grid-bg relative selection:bg-accent selection:text-base">

    <!-- Ambient Background Effects -->
    <div class="blob w-[600px] h-[600px] bg-emerald-600/20 -top-32 -left-32 animate-pulse-slow"></div>
    <div class="blob w-[500px] h-[500px] bg-teal-600/10 top-[40%] -right-40 animate-float"></div>
    <div class="blob w-[400px] h-[400px] bg-emerald-500/10 bottom-0 left-1/3 animate-pulse-slow" style="animation-delay: 1s;"></div>

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-20 h-20 flex items-center justify-between relative">
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="text-xl font-bold tracking-tight text-white hover:text-emerald-400 transition-colors">Algrow Capital</a>
            </div>

            <!-- Role Switcher -->
            <div class="hidden md:flex items-center gap-8 absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('landing') }}" class="text-emerald-400 font-semibold text-sm relative after:absolute after:bottom-[-6px] after:left-0 after:w-full after:h-0.5 after:bg-emerald-400 after:rounded-full">Untuk Mitra</a>
                <a href="{{ route('asset-manager') }}" class="text-slate-400 hover:text-white transition-colors text-sm font-medium">Untuk Asset Manager</a>
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
                    <a href="{{ route('register') }}" class="btn-shimmer px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-105 transition-all">
                        Gabung Sekarang
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="relative z-10 pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 sm:px-12 lg:px-20 max-w-7xl mx-auto text-center">
            
            <!-- Mobile Role Switcher -->
            <div class="md:hidden flex bg-white/5 p-1 rounded-xl mx-auto mb-8 max-w-[280px] border border-white/10 backdrop-blur-md">
                <a href="{{ route('landing') }}" class="flex-1 py-2.5 text-xs font-bold rounded-lg bg-emerald-500 text-white shadow-lg shadow-emerald-500/30">Untuk Mitra</a>
                <a href="{{ route('asset-manager') }}" class="flex-1 py-2.5 text-xs font-medium rounded-lg text-slate-400 hover:text-white transition-colors">Asset Manager</a>
            </div>

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card mb-8 border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <span class="text-sm font-medium text-emerald-200">Mitra Investasi Saham #1</span>
            </div>

            <!-- Headline - DIHAPUS reveal agar langsung muncul -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6">
                Investasi Saham Indonesia <br class="hidden sm:block" />
                <span class="text-gradient">Tanpa Keluar Modal</span>
            </h1>

            <!-- Subheadline - DIHAPUS reveal agar langsung muncul -->
            <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Platform kolaborasi investasi modern. Kami menyediakan modal, Anda menyediakan akun. Kita bagi keuntungan bersama dari setiap kenaikan saham.
            </p>

            <!-- CTA Button - DIHAPUS reveal agar langsung muncul -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center w-full">
                <a href="{{ route('register') }}" class="btn-shimmer w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-lg shadow-xl shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    Bergabung Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            
            <!-- Dashboard Preview Visual - Tetap ada reveal untuk efek masuk -->
            <div class="reveal mt-16 mx-auto max-w-5xl rounded-xl bg-surface/50 border border-white/5 p-2 shadow-2xl backdrop-blur-sm">
                <div class="bg-base/80 rounded-lg overflow-hidden h-64 sm:h-80 flex items-center justify-center border border-white/5 relative">
                    <svg class="absolute bottom-0 left-0 w-full h-full opacity-20" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0,100 L0,80 Q20,70 40,85 T80,60 T100,20 L100,100 Z" fill="#34d399"></path>
                    </svg>
                    <div class="relative z-10 text-center">
                        <div class="text-emerald-400 font-mono text-4xl mb-2">+126.8%</div>
                        <div class="text-slate-400 text-sm uppercase tracking-widest">Avg. Saham Return</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Concept: How It Works -->
        <section class="relative z-10 py-24 px-6 sm:px-12 lg:px-20 bg-base">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Bagaimana Cara Kerjanya?</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">Konsep sederhana yang saling menguntungkan. Fokus pada alokasi saham dan pembagian profit yang transparan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div class="reveal glass-card p-8 rounded-3xl relative group" style="transition-delay: 0ms;">
                        <div class="absolute -top-4 -left-4 w-12 h-12 rounded-full bg-surfaceHighlight border border-emerald-500/30 flex items-center justify-center text-accent font-bold text-xl shadow-lg z-10">1</div>
                        <div class="mt-4 mb-4 w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Mitra Siapkan Akun</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">Mitra menyediakan akun sekuritas yang sudah disepakati (Stockbit, Ajaib, dll) untuk dikelola.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="reveal glass-card p-8 rounded-3xl relative group" style="transition-delay: 100ms;">
                        <div class="absolute -top-4 -left-4 w-12 h-12 rounded-full bg-surfaceHighlight border border-emerald-500/30 flex items-center justify-center text-accent font-bold text-xl shadow-lg z-10">2</div>
                        <div class="mt-4 mb-4 w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Algrow Sediakan Modal</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">Modal investasi 100% berasal dari Algrow Capital. Mitra tidak perlu setor sepeserpun.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="reveal glass-card p-8 rounded-3xl relative group" style="transition-delay: 200ms;">
                        <div class="absolute -top-4 -left-4 w-12 h-12 rounded-full bg-surfaceHighlight border border-emerald-500/30 flex items-center justify-center text-accent font-bold text-xl shadow-lg z-10">3</div>
                        <div class="mt-4 mb-4 w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Eksekusi Saham</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">Dana digunakan untuk membeli saham baru dengan potensi keuntungan tinggi.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="reveal glass-card p-8 rounded-3xl relative group" style="transition-delay: 300ms;">
                        <div class="absolute -top-4 -left-4 w-12 h-12 rounded-full bg-surfaceHighlight border border-emerald-500/30 flex items-center justify-center text-accent font-bold text-xl shadow-lg z-10">4</div>
                        <div class="mt-4 mb-4 w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Bagi Hasil Profit</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">Keuntungan dibagikan sesuai kesepakatan awal. Transparan & akuntabel.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Section (Split Layout) -->
        <section class="relative z-10 py-24 px-6 sm:px-12 lg:px-20 overflow-hidden">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row items-center gap-16">
                    
                    <!-- Left Content -->
                    <div class="lg:w-1/2 reveal">
                        <h2 class="text-3xl sm:text-4xl font-bold mb-6">Saling Menguntungkan & <span class="text-gradient">Transparan</span></h2>
                        <p class="text-slate-400 mb-8 leading-relaxed">
                            Konsep ini dirancang untuk memecah masalah alokasi saham yang seringkali sulit didapatkan oleh individu, sekaligus memberikan kesempatan bagi Mitra untuk mendapatkan passive income.
                        </p>

                        <ul class="space-y-6">
                            <li class="flex items-start gap-4">
                                <div class="mt-1 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold mb-1">Zero Risk Modal</h4>
                                    <p class="text-sm text-slate-400">Mitra tidak mengeluarkan uang sama sekali, sehingga risiko kerugian modal ada di pihak Algrow.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="mt-1 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold mb-1">Peluang Alokasi Lebih Besar</h4>
                                    <p class="text-sm text-slate-400">Dengan mengelola banyak akun, probabilitas mendapatkan jatah saham (allotment) meningkat drastis.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="mt-1 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold mb-1">Pengelolaan Terstruktur</h4>
                                    <p class="text-sm text-slate-400">Strategi investasi telah ditentukan oleh tim ahli Algrow Capital untuk memaksimalkan potensi cuan.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Right Visual -->
                    <div class="lg:w-1/2 w-full reveal" style="transition-delay: 200ms;">
                        <div class="glass-card rounded-3xl p-8 border border-white/10 bg-gradient-to-br from-surface to-base">
                            <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/5">
                                <span class="text-slate-400 text-sm font-medium">Platform Didukung</span>
                                <span class="text-emerald-400 text-xs font-bold px-2 py-1 bg-emerald-500/10 rounded">SECURE</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Stockbit Visual -->
                                <div class="bg-white/5 hover:bg-white/10 rounded-xl p-4 flex flex-col items-center justify-center gap-3 transition-colors cursor-default h-32">
                                    <!-- Simple icon for Stockbit -->
                                    <svg class="w-8 h-8 text-orange-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2L2 22h20L12 2zm0 4l6 14H6l6-14z"/>
                                    </svg>
                                    <span class="font-semibold text-sm">Stockbit</span>
                                </div>
                                <!-- Ajaib Visual -->
                                <div class="bg-white/5 hover:bg-white/10 rounded-xl p-4 flex flex-col items-center justify-center gap-3 transition-colors cursor-default h-32">
                                    <!-- Simple icon for Ajaib -->
                                    <svg class="w-8 h-8 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                    </svg>
                                    <span class="font-semibold text-sm">Ajaib</span>
                                </div>
                            </div>
                            <div class="mt-6 p-4 rounded-xl bg-emerald-500/5 border border-emerald-500/10">
                                <p class="text-xs text-emerald-200 leading-relaxed text-center">
                                    *Akun tetap dalam kendali penuh Mitra (Nama & Data Diri Mitra). Hanya dana yang dikelola bersama.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Detailed Explanation Section (The Text provided) -->
        <section class="relative z-10 py-24 px-6 sm:px-12 lg:px-20 bg-surface/30">
            <div class="max-w-4xl mx-auto reveal">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-white mb-4">Tentang Algrow Capital</h2>
                    <div class="w-20 h-1 bg-accent mx-auto rounded-full"></div>
                </div>

                <div class="prose prose-invert prose-lg mx-auto text-slate-300">
                    <p class="first-letter:text-5xl first-letter:font-bold first-letter:text-accent first-letter:float-left first-letter:mr-2">
                        <strong class="text-white">Algrow Capital</strong> merupakan konsep kerja sama investasi yang berfokus pada saham-saham yang ada di Indonesia. Kami menghadirkan inovasi di mana Mitra tidak perlu menyediakan modal investasi, melainkan menyediakan akun sekuritas yang telah disepakati untuk dikelola.
                    </p>
                    
                    <p>
                        Modal investasi sepenuhnya berasal dari pihak Algrow Capital. Dana tersebut kemudian digunakan secara strategis untuk melakukan investasi pada saham-saham baru listing yang melalui analisis mendalam memiliki potensi keuntungan signifikan.
                    </p>

                    <div class="my-8 p-6 rounded-2xl bg-surface border border-white/5 not-prose">
                        <h4 class="text-accent font-bold mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Transparansi & Profit Sharing
                        </h4>
                        <p class="text-sm text-slate-400">
                            Apabila investasi menghasilkan keuntungan, profit akan dibagikan antara Algrow Capital dan Mitra sesuai dengan perjanjian kerja sama yang telah disepakati bersama di awal.
                        </p>
                    </div>

                    <p>
                        Konsep ini bertujuan menciptakan hubungan kerja sama <strong>saling menguntungkan</strong>. Mitra memperoleh peluang mendapatkan penghasilan dari akun yang dimilikinya tanpa harus menyediakan modal investasi. Di sisi lain, Algrow Capital dapat meningkatkan peluang memperoleh alokasi saham Indonesia yang lebih besar melalui pengelolaan portofolio beberapa akun terverifikasi.
                    </p>
                    <p>
                        Dengan pengelolaan yang terstruktur, transparansi pembagian keuntungan, serta strategi investasi yang telah ditentukan, Algrow Capital menawarkan konsep investasi yang berorientasi pada <strong>pertumbuhan dan keuntungan bersama</strong>.
                    </p>
                </div>

                <div class="mt-12 text-center">
                    <!-- Tombol Static -->
                    <a href="{{ route('register') }}" class="inline-block btn-shimmer px-10 py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold shadow-lg hover:shadow-emerald-500/40 hover:-translate-y-1 transition-all">
                        Bergabung Sekarang
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/[0.05] bg-base pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-20">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-lg font-bold text-white">Algrow Capital</span>
                    </div>
                    <p class="text-slate-500 text-sm max-w-sm">
                        Mitra terpercaya investasi saham Indonesia. Kolaborasi modal dan akun untuk pertumbuhan finansial bersama.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Perusahaan</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Cara Kerja</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Pernyataan Risiko</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/[0.05] pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-600">
                <p>&copy; {{ date('Y') }} Algrow Capital. All rights reserved.</p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500/50"></span>
                    <span>System Operational</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Simple Script for Scroll Animations -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            if(revealElements.length > 0) {
                revealElements.forEach(el => observer.observe(el));
            }
        });
    </script>

    <!-- Algrow AI Chatbot Floating Widget -->
    <div id="algrowChatWidget" class="fixed bottom-5 right-5 sm:bottom-6 sm:right-6 z-50 font-sans">
        <!-- Floating Trigger Button -->
        <button id="chatbotToggleBtn" onclick="toggleChatbot()" type="button" aria-label="Buka Chatbot Pendaftaran"
                class="group flex items-center gap-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white pl-4 pr-5 py-3.5 rounded-full shadow-[0_10px_30px_rgba(16,185,129,0.35)] hover:shadow-[0_15px_35px_rgba(16,185,129,0.5)] hover:scale-105 active:scale-95 transition-all duration-300">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-100"></span>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="font-bold text-sm tracking-wide">Tanya Cara Join</span>
        </button>

        <!-- Chat Window Popup -->
        <div id="chatbotWindow"
             class="hidden fixed inset-x-3 bottom-20 sm:inset-auto sm:absolute sm:bottom-16 sm:right-0 sm:w-[400px] h-[550px] max-h-[82vh] bg-[#021a14]/95 backdrop-blur-2xl border border-emerald-500/25 rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.7)] flex flex-col overflow-hidden transition-all duration-300 transform origin-bottom-right">
            
            <!-- Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-[#042f22] to-[#064a38] border-b border-emerald-500/20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white font-black text-lg shadow-md shadow-emerald-900/50">
                        A
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-white text-sm">Algrow AI</h3>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Pendaftaran</span>
                        </div>
                        <p class="text-xs text-emerald-200/70">Panduan Join & Akun Sekuritas</p>
                    </div>
                </div>
                <button onclick="toggleChatbot()" type="button" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Guardrail Scope Banner -->
            <div class="px-4 py-2 bg-emerald-950/60 border-b border-emerald-500/10 text-[11px] text-emerald-300/80 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Fokus khusus seputar cara daftar, syarat, & sekuritas mitra.</span>
            </div>

            <!-- Chat Messages Container -->
            <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 text-sm scroll-smooth">
                <!-- Welcome Bot Message -->
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xs font-bold shrink-0 mt-0.5">
                        AI
                    </div>
                    <div class="bg-[#042f22]/90 border border-emerald-500/20 text-slate-100 rounded-2xl rounded-tl-sm px-4 py-3 max-w-[85%] shadow-sm space-y-2">
                        <p>Halo! 👋 Mau tahu cara gabung ke Algrow Capital atau panduan pendaftaran akun sekuritas?</p>
                        <p class="text-xs text-slate-400">Pilih pertanyaan cepat di bawah atau ketik langsung:</p>
                    </div>
                </div>

                <!-- Quick Suggested Questions Chips -->
                <div id="quickQuestionsContainer" class="flex flex-col gap-1.5 pt-1 pl-9">
                    <button type="button" onclick="askPreset('Bagaimana cara join ke Algrow Capital?')"
                            class="text-left text-xs bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/25 hover:border-emerald-400 text-emerald-200 px-3 py-2 rounded-xl transition-all">
                        🚀 Bagaimana cara join ke Algrow?
                    </button>
                    <button type="button" onclick="askPreset('Pakai aplikasi sekuritas apa saja?')"
                            class="text-left text-xs bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/25 hover:border-emerald-400 text-emerald-200 px-3 py-2 rounded-xl transition-all">
                        📱 Pakai aplikasi sekuritas apa?
                    </button>
                    <button type="button" onclick="askPreset('Bagaimana cara daftar akun sekuritas Stockbit atau Ajaib?')"
                            class="text-left text-xs bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/25 hover:border-emerald-400 text-emerald-200 px-3 py-2 rounded-xl transition-all">
                        📝 Cara daftar akun sekuritasnya gimana?
                    </button>
                    <button type="button" onclick="askPreset('Apakah butuh modal sendiri untuk daftar?')"
                            class="text-left text-xs bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/25 hover:border-emerald-400 text-emerald-200 px-3 py-2 rounded-xl transition-all">
                        💰 Apakah butuh modal sendiri?
                    </button>
                </div>
            </div>

            <!-- Typing Indicator (Hidden by default) -->
            <div id="chatTypingIndicator" class="hidden px-4 py-2 flex items-center gap-2 text-xs text-emerald-400/80 pl-11">
                <span class="flex gap-1">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 0.15s;"></span>
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 0.3s;"></span>
                </span>
                <span>Algrow AI sedang mengetik...</span>
            </div>

            <!-- Input Bar -->
            <div class="p-3 bg-[#032319] border-t border-emerald-500/20">
                <form id="chatbotForm" onsubmit="handleChatSubmit(event)" class="flex items-center gap-2">
                    <input type="text" id="chatInput" placeholder="Tanya seputar pendaftaran & sekuritas..." autocomplete="off"
                           class="flex-1 bg-white/5 border border-emerald-500/20 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 transition-all">
                    <button type="submit" id="chatSendBtn"
                            class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white p-2.5 rounded-xl transition-all shadow-md shadow-emerald-500/20 disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </form>
                <div class="mt-2 flex justify-between items-center text-[10px] text-slate-500 px-1">
                    <span>Tertarik bergabung?</span>
                    <a href="{{ route('register') }}" class="text-emerald-400 font-bold hover:underline">Daftar Akun Sekarang &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Chatbot Client-Side Controller Script -->
    <script>
        const chatHistory = [];
        let isChatbotOpen = false;

        function toggleChatbot() {
            const win = document.getElementById('chatbotWindow');
            const btn = document.getElementById('chatbotToggleBtn');
            isChatbotOpen = !isChatbotOpen;
            
            if (isChatbotOpen) {
                win.classList.remove('hidden');
                document.getElementById('chatInput').focus();
            } else {
                win.classList.add('hidden');
            }
        }

        function askPreset(question) {
            document.getElementById('chatInput').value = question;
            handleChatSubmit(new Event('submit'));
        }

        function appendMessage(sender, text) {
            const container = document.getElementById('chatMessages');
            const isUser = sender === 'user';
            
            const msgRow = document.createElement('div');
            msgRow.className = `flex items-start gap-2.5 ${isUser ? 'justify-end' : 'justify-start'}`;

            // Format linebreaks and markdown bold
            let formattedText = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`([^`]+)`/g, '<code class="bg-black/40 px-1 py-0.5 rounded text-emerald-300">$1</code>')
                .replace(/\n/g, '<br>');

            if (isUser) {
                msgRow.innerHTML = `
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-[85%] shadow-sm text-sm">
                        ${formattedText}
                    </div>
                `;
            } else {
                msgRow.innerHTML = `
                    <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xs font-bold shrink-0 mt-0.5">
                        AI
                    </div>
                    <div class="bg-[#042f22]/90 border border-emerald-500/20 text-slate-100 rounded-2xl rounded-tl-sm px-4 py-3 max-w-[88%] shadow-sm text-sm space-y-2">
                        <div>${formattedText}</div>
                        <div class="pt-1 flex gap-2">
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-500 text-black px-3 py-1.5 rounded-lg hover:bg-emerald-400 transition-colors shadow">
                                <span>Daftar Sekarang</span> &rarr;
                            </a>
                        </div>
                    </div>
                `;
            }

            container.appendChild(msgRow);
            container.scrollTop = container.scrollHeight;
        }

        async function handleChatSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const query = input.value.trim();
            if (!query) return;

            // Clear input & disable submit button temporarily
            input.value = '';
            document.getElementById('chatSendBtn').disabled = true;

            // Append user bubble
            appendMessage('user', query);
            chatHistory.push({ role: 'user', content: query });

            // Show typing indicator
            const indicator = document.getElementById('chatTypingIndicator');
            indicator.classList.remove('hidden');
            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            try {
                const response = await fetch("{{ route('chatbot.message') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: query,
                        history: chatHistory
                    })
                });

                const data = await response.json();
                indicator.classList.add('hidden');

                if (data.success && data.reply) {
                    appendMessage('model', data.reply);
                    chatHistory.push({ role: 'model', content: data.reply });
                } else {
                    appendMessage('model', 'Mohon maaf, terjadi kendala saat memproses pertanyaan. Silakan coba klik tombol [Daftar Sekarang] di atas.');
                }
            } catch (err) {
                indicator.classList.add('hidden');
                appendMessage('model', 'Koneksi terputus. Pastikan perangkat Anda terhubung ke internet.');
            } finally {
                document.getElementById('chatSendBtn').disabled = false;
                input.focus();
            }
        }
    </script>
</body>
</html>