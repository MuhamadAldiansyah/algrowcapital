<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Pro - Algrow Capital</title>
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #021a14; color: white; }
        
        .card-shimmer {
            position: relative;
            overflow: hidden;
        }
        .card-shimmer::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(105deg, transparent 20%, rgba(255,255,255,.05) 50%, transparent 80%);
            animation: shimmer 4s infinite linear;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 200%; }
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden p-6 pb-20">
    <!-- Blobs Background -->
    <div class="blob w-[600px] h-[600px] bg-emerald-500/15 -top-40 -right-40 fixed"></div>
    <div class="blob w-[500px] h-[500px] bg-teal-500/15 -bottom-32 -left-32 fixed"></div>

    <div class="w-full max-w-5xl mx-auto relative z-10 pt-10">
        
        <div class="text-center mb-16">
            <div class="inline-flex items-center justify-center p-2.5 bg-emerald-500/10 rounded-2xl mb-6 border border-emerald-500/20 shadow-[0_0_20px_rgba(16,185,129,0.15)]">
                <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight bg-gradient-to-r from-white via-emerald-100 to-emerald-300 bg-clip-text text-transparent">Upgrade Kinerja Anda</h1>
            <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">Kelola ribuan akun mitra, lacak profit otomatis, dan distribusikan alokasi IPO tanpa pusing.</p>
        </div>

        @if(session('warning'))
            <div class="mb-8 p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-amber-400 text-center font-medium max-w-3xl mx-auto shadow-lg backdrop-blur-md">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl text-red-400 text-center font-medium max-w-3xl mx-auto shadow-lg backdrop-blur-md">
                {{ session('error') }}
            </div>
        @endif

        <!-- Pricing Cards Row -->
        <div class="flex flex-col md:flex-row justify-center items-stretch gap-8 mb-20 max-w-4xl mx-auto">
            @foreach($plans as $plan)
            <div class="relative flex-1 w-full max-w-md mx-auto group">
                
                @if($plan->duration_months == 6)
                <!-- Popular Badge -->
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[11px] md:text-xs uppercase tracking-widest font-extrabold px-5 py-2 rounded-full shadow-[0_0_20px_rgba(52,211,153,0.5)] border border-emerald-400/50 flex items-center gap-2 z-20 whitespace-nowrap transform group-hover:-translate-y-1 transition-all duration-300">
                    <svg class="w-4 h-4 text-yellow-300 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    Paling Laris
                </div>
                @endif

                <div class="bg-gradient-to-b from-surface to-base p-[2px] rounded-[32px] h-full shadow-2xl transition-all duration-500 group-hover:shadow-[0_0_40px_rgba(16,185,129,0.2)] group-hover:-translate-y-2 relative overflow-hidden {{ $plan->duration_months == 6 ? 'border border-emerald-500/50' : 'border border-white/5' }}">
                    
                    @if($plan->duration_months == 6)
                    <div class="absolute inset-0 bg-gradient-to-b from-emerald-500/10 to-transparent opacity-50"></div>
                    @endif

                    <div class="bg-[#032319] rounded-[30px] p-8 md:p-10 h-full flex flex-col justify-between relative z-10">
                        <div>
                            <h3 class="text-3xl font-extrabold text-white mb-3">{{ $plan->name }}</h3>
                            <p class="text-emerald-400/80 text-sm font-medium">Investasi terbaik untuk bisnis Anda.</p>
                            
                            <div class="mt-8 mb-10 flex items-baseline gap-2">
                                <span class="text-5xl font-extrabold text-white">Rp {{ number_format($plan->price / 1000, 0, ',', '.') }}</span><span class="text-2xl font-bold text-accent">K</span>
                                <span class="text-slate-400 font-medium text-lg">/ {{ $plan->duration_months }} bln</span>
                            </div>
                            
                            <ul class="space-y-4 mb-10">
                                <li class="flex items-center gap-3 text-slate-300">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    Akses penuh semua fitur
                                </li>
                                <li class="flex items-center gap-3 text-slate-300">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    Support prioritas
                                </li>
                            </ul>
                        </div>

                        @if($hasActiveSubscription)
                            <div class="w-full py-4 px-6 rounded-xl bg-white/5 border border-white/10 text-center font-bold text-slate-300">
                                Langganan Anda Sedang Aktif
                            </div>
                            <a href="{{ route('dashboard') }}" class="mt-4 block w-full py-4 rounded-xl bg-accent hover:bg-emerald-400 text-base font-bold text-center text-white transition-all shadow-lg shadow-emerald-500/20">
                                Kembali ke Dashboard
                            </a>
                        @else
                            <div>
                                <form action="{{ route('pricing.purchase', $plan->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-4 rounded-xl {{ $plan->duration_months == 6 ? 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white shadow-[0_0_20px_rgba(52,211,153,0.3)] hover:shadow-[0_0_30px_rgba(52,211,153,0.5)]' : 'bg-white/10 hover:bg-white/20 text-white' }} font-bold text-lg hover:-translate-y-1 transition-all">
                                        Pilih Paket Ini
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Features List Section -->
        <div class="max-w-4xl mx-auto bg-surface/40 p-8 md:p-12 rounded-[2rem] border border-white/5 backdrop-blur-xl mb-12 shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-teal-500/5"></div>
            <div class="relative z-10">
                <div class="text-center mb-10">
                    <h3 class="text-2xl font-extrabold text-white">Fitur Eksklusif Algrow Capital</h3>
                    <div class="w-16 h-1 bg-accent mx-auto mt-4 rounded-full"></div>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-black/20 p-6 rounded-2xl border border-white/5 hover:border-emerald-500/30 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-4 text-accent">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h4 class="font-bold text-white mb-2 text-lg">Akun Mitra Unlimited</h4>
                        <p class="text-sm text-slate-400 leading-relaxed">Kelola sebanyak apapun akun klien dan investor Anda dalam satu dashboard terpusat tanpa batasan.</p>
                    </div>

                    <div class="bg-black/20 p-6 rounded-2xl border border-white/5 hover:border-emerald-500/30 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-4 text-accent">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h4 class="font-bold text-white mb-2 text-lg">Distribusi Profit Instan</h4>
                        <p class="text-sm text-slate-400 leading-relaxed">Sistem akan menghitung otomatis *management fee* dan persentase ROI secara adil dan akurat.</p>
                    </div>

                    <div class="bg-black/20 p-6 rounded-2xl border border-emerald-500/20 hover:border-emerald-400/50 transition-colors relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl"></div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/30 flex items-center justify-center mb-4 text-emerald-400 relative z-10 shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h4 class="font-bold text-white mb-2 text-lg relative z-10">Privasi Super Ketat</h4>
                        <p class="text-sm text-slate-300 leading-relaxed relative z-10">Sistem dienkripsi penuh. <span class="text-emerald-400 font-bold">Bahkan pihak Developer tidak bisa mengintip data Anda.</span> Rahasia 100% aman.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-white transition-colors text-sm font-medium flex items-center justify-center gap-2 mx-auto px-4 py-2 rounded-lg hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
</body>
</html>
