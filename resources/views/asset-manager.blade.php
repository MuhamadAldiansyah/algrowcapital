<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#021a14">
    <title>Algrow Capital - Manajemen Portofolio untuk Asset Manager</title>
    
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
        html, body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #021a14;
            color: #ffffff;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        .grid-bg {
            background-image:
                linear-gradient(rgba(52,211,153,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(52,211,153,.03) 1px, transparent 1px);
            background-size: 48px 48px;
            min-height: 100vh;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

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
    <div class="blob w-[600px] h-[600px] bg-teal-600/20 -top-32 -left-32 animate-pulse-slow"></div>
    <div class="blob w-[500px] h-[500px] bg-emerald-600/10 top-[40%] -right-40 animate-float"></div>
    <div class="blob w-[400px] h-[400px] bg-teal-500/10 bottom-0 left-1/3 animate-pulse-slow" style="animation-delay: 1s;"></div>

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 sm:px-12 lg:px-20 h-20 flex items-center justify-between relative">
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="text-xl font-bold tracking-tight text-white hover:text-emerald-400 transition-colors">Algrow Capital</a>
            </div>

            <!-- Role Switcher -->
            <div class="hidden md:flex items-center gap-8 absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('landing') }}" class="text-slate-400 hover:text-white transition-colors text-sm font-medium">Untuk Mitra</a>
                <a href="{{ route('asset-manager') }}" class="text-emerald-400 font-semibold text-sm relative after:absolute after:bottom-[-6px] after:left-0 after:w-full after:h-0.5 after:bg-emerald-400 after:rounded-full">Untuk Asset Manager</a>
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
                    <a href="{{ route('register') }}" class="btn-shimmer px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-bold text-sm shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:scale-105 transition-all">
                        Daftar Owner
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="relative z-10 pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 sm:px-12 lg:px-20 max-w-7xl mx-auto text-center">
            
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card mb-8 border-teal-500/20">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                <span class="text-sm font-medium text-teal-200">SaaS Multi-Tenant B2B</span>
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6">
                Skalakan Kapasitas <br class="hidden sm:block" />
                <span class="text-gradient from-teal-400 to-emerald-400">Pengelolaan Saham Anda</span>
            </h1>

            <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Kelola puluhan hingga ratusan akun sekuritas Mitra dari satu dasbor terpusat. Dilengkapi otomatisasi perhitungan profit sharing & manajemen portofolio.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center w-full">
                <a href="{{ route('register') }}" class="btn-shimmer w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-bold text-lg shadow-xl shadow-teal-500/25 hover:shadow-teal-500/40 hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    Daftar Sebagai Owner
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            
            <div class="reveal mt-16 mx-auto max-w-5xl rounded-xl bg-surface/50 border border-white/5 p-2 shadow-2xl backdrop-blur-sm">
                <div class="bg-base/80 rounded-lg overflow-hidden h-64 sm:h-80 flex flex-col items-center justify-center border border-white/5 relative">
                    <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0,50 L20,40 L40,60 L60,30 L80,50 L100,20 L100,100 L0,100 Z" fill="#2dd4bf"></path>
                    </svg>
                    <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center w-full px-8">
                        <div>
                            <div class="text-teal-400 font-mono text-3xl font-bold mb-1">150+</div>
                            <div class="text-slate-400 text-xs uppercase tracking-wider">Akun Dikelola</div>
                        </div>
                        <div>
                            <div class="text-emerald-400 font-mono text-3xl font-bold mb-1">1.2B</div>
                            <div class="text-slate-400 text-xs uppercase tracking-wider">AUM (Rp)</div>
                        </div>
                        <div>
                            <div class="text-teal-400 font-mono text-3xl font-bold mb-1">98%</div>
                            <div class="text-slate-400 text-xs uppercase tracking-wider">Akurasi Profit</div>
                        </div>
                        <div>
                            <div class="text-emerald-400 font-mono text-3xl font-bold mb-1">Auto</div>
                            <div class="text-slate-400 text-xs uppercase tracking-wider">Rekapitulasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features for Asset Manager -->
        <section class="relative z-10 py-24 px-6 sm:px-12 lg:px-20 bg-base">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Fitur Khusus Asset Manager</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">Semua alat yang Anda butuhkan untuk menjalankan bisnis manajemen aset saham secara profesional dan akuntabel.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Feature 1 -->
                    <div class="reveal glass-card p-8 rounded-3xl" style="transition-delay: 0ms;">
                        <div class="mb-6 w-14 h-14 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002 2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Multi-Tenant & Manajemen Grup</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Daftarkan perusahaan Anda sebagai Tenant. Undang Mitra dan kelompokkan akun-akun mereka ke dalam berbagai Grup Manajemen untuk mempermudah monitoring, penugasan eksekusi, dan pelaporan per grup.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="reveal glass-card p-8 rounded-3xl" style="transition-delay: 100ms;">
                        <div class="mb-6 w-14 h-14 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Eksekusi Massal (Bulk Placement)</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Tinggalkan input manual satu per satu. Lakukan penempatan modal (placement), pencatatan jatah (allotment), dan hasil penjualan saham ke puluhan akun sekaligus secara instan lewat fitur Bulk Actions.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="reveal glass-card p-8 rounded-3xl" style="transition-delay: 200ms;">
                        <div class="mb-6 w-14 h-14 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Profit Sharing Otomatis</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Sistem secara otomatis menghitung pembagian keuntungan bersih untuk Owner, Investor penyedia dana, dan Mitra penyedia akun berdasarkan persentase kesepakatan yang Anda atur. Bebas dari kesalahan hitung Excel.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="reveal glass-card p-8 rounded-3xl" style="transition-delay: 300ms;">
                        <div class="mb-6 w-14 h-14 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Sistem Wallet & Mutasi Investor</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Platform dilengkapi dengan sistem dompet (wallet) internal. Lacak arus kas masuk (deposit) dan keluar (withdraw) dari para investor Anda dengan rapi layaknya buku tabungan perbankan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative z-10 py-24 px-6 sm:px-12 lg:px-20 bg-surface/30">
            <div class="max-w-4xl mx-auto text-center reveal">
                <h2 class="text-3xl sm:text-5xl font-bold text-white mb-6">Siap Menjadi Asset Manager Profesional?</h2>
                <p class="text-slate-400 mb-10 text-lg">
                    Daftarkan perusahaan Anda sekarang dan mulai kelola portofolio klien Anda dengan transparansi dan efisiensi tingkat tinggi.
                </p>
                <div class="flex justify-center">
                    <a href="{{ route('register') }}" class="btn-shimmer px-10 py-4 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-bold text-lg shadow-xl shadow-teal-500/25 hover:shadow-teal-500/40 hover:-translate-y-1 transition-all">
                        Daftar Sebagai Owner
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
                        Solusi perangkat lunak (SaaS) manajemen investasi saham B2B. Membantu Asset Manager mengelola modal dan akun secara profesional.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Perusahaan</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Harga & Paket</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a></li>
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
</body>
</html>
