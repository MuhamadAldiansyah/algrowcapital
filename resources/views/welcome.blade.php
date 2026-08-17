<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem IPO Demo | Emerald Premium</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            :root {
                --deep-emerald: #071f11;
                --emerald-green: #10b981;
                --emerald-dark: #064e3b;
                --mint-emerald: #34d399;
                --neon-glow: 0 0 15px rgba(16, 185, 129, 0.4);
            }

            body {
                background: linear-gradient(135deg, #05160c 0%, #071f11 50%, #05160c 100%);
                color: #ffffff;
                font-family: 'Outfit', sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }

            /* Animated Background Nodes */
            .bg-node {
                position: absolute;
                width: 300px;
                height: 300px;
                background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
                border-radius: 50%;
                z-index: -1;
                filter: blur(50px);
                animation: float 20s infinite alternate;
            }

            @keyframes float {
                0% { transform: translate(0, 0) scale(1); }
                100% { transform: translate(100px, 100px) scale(1.2); }
            }

            .ticker-font { font-family: 'JetBrains Mono', monospace; }

            .stat-node {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
                border: 1px solid rgba(16, 185, 129, 0.1);
                border-radius: 30px;
                padding: 3rem;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                max-width: 550px;
                width: 90%;
                z-index: 10;
                text-center: center !important;
            }

            .logo-glow {
                font-size: 3.5rem;
                font-weight: 800;
                letter-spacing: -2px;
                background: linear-gradient(to right, #10b981, #34d399);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 0 30px rgba(16, 185, 129, 0.3);
            }

            .btn-emerald {
                background: #10b981;
                color: #071f11;
                border: none;
                padding: 1rem 2.5rem;
                font-weight: 700;
                border-radius: 50px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                text-transform: uppercase;
                letter-spacing: 1px;
                box-shadow: var(--neon-glow);
            }

            .btn-emerald:hover {
                background: #34d399;
                transform: translateY(-3px);
                box-shadow: 0 0 25px rgba(52, 211, 153, 0.6);
                color: #05160c;
            }

            .btn-outline-emerald {
                background: transparent;
                color: #10b981;
                border: 2px solid rgba(16, 185, 129, 0.3);
                padding: 1rem 2.5rem;
                font-weight: 700;
                border-radius: 50px;
                transition: all 0.3s;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .btn-outline-emerald:hover {
                border-color: #10b981;
                background: rgba(16, 185, 129, 0.05);
                color: #34d399;
            }

            .feature-pill {
                background: rgba(16, 185, 129, 0.05);
                border: 1px solid rgba(16, 185, 129, 0.1);
                padding: 0.5rem 1.2rem;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 600;
                color: #10b981;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }
        </style>
    </head>
    <body>
        <!-- Floating Nodes -->
        <div class="bg-node" style="top: -100px; left: -100px;"></div>
        <div class="bg-node" style="bottom: -150px; right: -50px; animation-duration: 25s; animation-direction: reverse;"></div>

        <div class="stat-node text-center">
            <div class="feature-pill">
                <span class="spinner-grow spinner-grow-sm" role="status"></span>
                INSTITUTIONAL GRADE TERMINAL v4.0
            </div>
            
            <h1 class="logo-glow mb-2">EMERALD</h1>
            <p class="text-white opacity-50 mb-5 fs-6">
                Professional IPO Management Console for <br>
                High-Performance Capital Allocation.
            </p>

            @if (Route::has('login'))
                <div class="d-grid gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-emerald">
                            <i class="fa-solid fa-gauge-high me-2"></i> ENTER TERMINAL
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-emerald">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> LOGIN CONSOLE
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-emerald">
                                <i class="fa-solid fa-user-plus me-2"></i> REGISTER ACCESS
                            </a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="mt-5 pt-4 border-top border-white border-opacity-10">
                <div class="row ticker-font small text-emerald-500 opacity-40">
                    <div class="col-4">REAL-TIME DATA</div>
                    <div class="col-4">E2E ENCRYPTED</div>
                    <div class="col-4">MULTI-TENANT</div>
                </div>
            </div>
        </div>
    </body>
</html>
