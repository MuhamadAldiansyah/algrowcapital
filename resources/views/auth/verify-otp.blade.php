<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#021a14">
    <title>Verify Email - Algrow Capital</title>

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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .grid-bg {
            background-image:
                linear-gradient(rgba(52,211,153,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(52,211,153,.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .otp-input {
            width: 100%;
            max-width: 54px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem; /* responsive font size */
            font-weight: 700;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            color: #fff;
            transition: all 0.2s;
        }
        .otp-input:focus {
            outline: none;
            border-color: rgba(52,211,153,.5);
            background: rgba(255,255,255,.06);
            box-shadow: 0 0 0 4px rgba(52,211,153,.1);
        }
    </style>
</head>
<body class="min-h-[100dvh] bg-base grid-bg relative flex flex-col justify-center px-4 py-6">
    <div class="relative z-10 w-full max-w-[440px] mx-auto">
        <div class="rounded-[26px] bg-surface/90 backdrop-blur-xl px-5 py-8 sm:px-9 sm:py-10 shadow-[0_32px_80px_rgba(0,0,0,.5)] border border-emerald-500/20">
            
            <div class="text-center mb-8">
                <h1 class="text-[28px] font-extrabold tracking-tight text-white mb-2">Verifikasi Email</h1>
                <p class="text-slate-400 text-[14px] font-medium leading-relaxed">
                    Kami telah mengirimkan 6 digit kode OTP ke email Anda <br>
                    <span class="text-emerald-400 font-bold">{{ $email }}</span>
                </p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('verification.verify') }}" method="POST" id="otpForm">
                @csrf
                <input type="hidden" name="otp" id="final_otp">
                
                <div class="flex justify-between gap-1 sm:gap-2 mb-8" id="otp-container">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]*" inputmode="numeric">
                </div>

                <button type="submit" class="w-full h-[54px] rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-500 to-teal-500 text-white font-bold text-[15px] shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 transition-all flex items-center justify-center">
                    Verifikasi Sekarang
                </button>
            </form>

            <div class="mt-8 text-center">
                <form action="{{ route('verification.resend') }}" method="POST">
                    @csrf
                    <p class="text-slate-400 text-sm">
                        Belum menerima kode? 
                        <button type="submit" class="text-emerald-400 font-bold hover:text-emerald-300 transition-colors">
                            Kirim Ulang
                        </button>
                    </p>
                </form>
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-slate-400 text-[13px] font-medium hover:text-emerald-400 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('final_otp');
        const form = document.getElementById('otpForm');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1) {
                    if (index < inputs.length - 1) inputs[index + 1].focus();
                }
                updateHiddenInput();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '') {
                    if (index > 0) inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').slice(0, inputs.length).replace(/[^0-9]/g, '');
                pastedData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        if (i < inputs.length - 1) inputs[i + 1].focus();
                        else inputs[i].focus();
                    }
                });
                updateHiddenInput();
            });
        });

        function updateHiddenInput() {
            let otp = '';
            inputs.forEach(input => otp += input.value);
            hiddenInput.value = otp;
        }

        form.addEventListener('submit', (e) => {
            updateHiddenInput();
            if (hiddenInput.value.length !== 6) {
                e.preventDefault();
                alert('Silakan masukkan 6 digit kode OTP lengkap.');
            }
        });
        
        // Focus first input
        window.addEventListener('load', () => {
            inputs[0].focus();
        });
    </script>
</body>
</html>
