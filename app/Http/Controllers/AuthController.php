<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.register');
    }

    public function searchMitra(Request $request)
    {
        $query = $request->input('q');
        if (empty($query)) {
            return response()->json([]);
        }

        $registeredUsernames = \App\Models\User::pluck('username')->toArray();
        $registeredNames = \App\Models\User::pluck('name')->toArray();

        $mitras = \App\Models\MitraAccount::where('status', 'aktif')
            ->whereNotIn('username', $registeredUsernames)
            ->whereNotIn('owner_name', $registeredNames)
            ->where('owner_name', 'ilike', '%' . $query . '%')
            ->orderBy('owner_name')
            ->limit(10)
            ->get(['id', 'owner_name', 'username', 'platform']);

        return response()->json($mitras);
    }

    public function getCompanies()
    {
        $companies = \App\Models\Tenant::orderBy('name')->get(['id', 'name']);
        return response()->json($companies);
    }

    public function getTenantMitras(\App\Models\Tenant $tenant)
    {
        // Get mitras that are not yet claimed by a user
        $registeredUsernames = \App\Models\User::pluck('username')->whereNotNull()->toArray();
        
        $mitras = \App\Models\MitraAccount::where('tenant_id', $tenant->id)
            ->where('status', 'aktif')
            ->whereNotIn('username', $registeredUsernames)
            ->orderBy('owner_name')
            ->get(['id', 'owner_name', 'username', 'platform']);
            
        return response()->json($mitras);
    }

    public function searchTenantMitras(Request $request, \App\Models\Tenant $tenant)
    {
        $query = $request->input('q');
        if (empty($query)) {
            return response()->json([]);
        }

        $registeredUsernames = \App\Models\User::pluck('username')->whereNotNull()->toArray();
        
        $mitras = \App\Models\MitraAccount::where('tenant_id', $tenant->id)
            ->where('status', 'aktif')
            ->whereNotIn('username', $registeredUsernames)
            ->where('owner_name', 'ilike', '%' . $query . '%')
            ->orderBy('owner_name')
            ->limit(3)
            ->get(['id', 'owner_name', 'username', 'platform']);
            
        return response()->json($mitras);
    }

    public function register(Request $request)
    {
        $rules = [
            'role_type' => ['required', 'in:owner,user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->role_type === 'owner') {
            $rules['company_name'] = ['required', 'string', 'max:255', 'unique:tenants,name'];
        } else {
            // Mitra: Cek apakah Mitra Baru atau Sudah Terdata
            $mitraCategory = $request->input('mitra_category', 'new');
            if ($mitraCategory === 'new') {
                $rules['full_name'] = ['required', 'string', 'max:255'];
                $rules['phone'] = ['required', 'string', 'max:30'];
                $rules['platform'] = ['required', 'string', 'max:50'];
                $rules['sekuritas_username'] = ['required', 'string', 'max:100'];
                $rules['sekuritas_password'] = ['required', 'string', 'max:100'];
                $rules['sekuritas_pin'] = ['required', 'string', 'max:50'];
                $rules['bank_rdn'] = ['nullable', 'string', 'max:100'];
                $rules['rdn_account'] = ['nullable', 'string', 'max:100'];
                $rules['personal_bank'] = ['nullable', 'string', 'max:100'];
                $rules['no_rek'] = ['nullable', 'string', 'max:100'];
            } else {
                $rules['tenant_id'] = ['required', 'exists:tenants,id'];
                $rules['mitra_account_id'] = ['required', 'exists:mitra_accounts,id'];
            }
        }

        $request->validate($rules, [
            'company_name.unique' => 'Nama Perusahaan/Grup ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah digunakan.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'platform.required' => 'Silakan pilih sekuritas (Stockbit / Ajaib).',
            'sekuritas_username.required' => 'Username sekuritas wajib diisi.',
            'sekuritas_password.required' => 'Password sekuritas wajib diisi.',
            'sekuritas_pin.required' => 'PIN sekuritas wajib diisi.',
            'mitra_account_id.required' => 'Silakan pilih akun mitra Anda yang terdata.',
            'tenant_id.required' => 'Silakan pilih perusahaan induk.',
        ]);
        
        $name = '';
        $phone = null;
        $tenantId = null;
        $mitraCategory = $request->input('mitra_category', 'new');

        if ($request->role_type === 'owner') {
            $name = "Owner " . $request->company_name;
        } elseif ($mitraCategory === 'new') {
            $name = trim($request->full_name);
            $phone = trim($request->phone);

            // Cari Tenant Algrow Capital atau Tenant default
            $tenant = \App\Models\Tenant::where('name', 'ilike', '%algrow%')
                ->orWhere('name', 'like', '%Algrow%')
                ->first() ?? \App\Models\Tenant::first();

            // Jika belum ada tenant sama sekali, buatkan Tenant default
            if (!$tenant) {
                $tenant = \App\Models\Tenant::create([
                    'name' => 'Algrow Capital',
                ]);
            }
            $tenantId = $tenant->id;
        } else {
            $mitraAccount = \App\Models\MitraAccount::find($request->mitra_account_id);
            $name = $mitraAccount ? $mitraAccount->owner_name : 'Mitra User';
            $tenantId = $request->tenant_id;
            $phone = $mitraAccount ? $mitraAccount->phone : null;
        }

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        
        // Clean username generation
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
        $username = ($cleanName ? substr($cleanName, 0, 15) : 'mitra') . rand(100, 999);
        
        // Create User
        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => $request->role_type,
            'tenant_id' => $request->role_type === 'owner' ? null : $tenantId,
            'sekuritas' => $request->platform ?? null,
            'password_sekuritas' => $request->sekuritas_password ?? null,
            'pin_sekuritas' => $request->sekuritas_pin ?? null,
            'bank' => $request->bank_rdn ?? $request->personal_bank ?? null,
            'no_rek' => $request->rdn_account ?? $request->no_rek ?? null,
            'status' => 'active',
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        if ($request->role_type === 'owner') {
            // Create Tenant with valid owner_id
            $tenant = \App\Models\Tenant::create([
                'name' => $request->company_name,
                'owner_id' => $user->id,
            ]);

            // Update User with tenant_id
            $user->update(['tenant_id' => $tenant->id]);
        } elseif ($mitraCategory === 'new') {
            // Otomatis daftarkan entri baru di mitra_accounts dengan status 'nonaktif' (Menunggu Approve Admin)
            $encryptedPassword = \Illuminate\Support\Facades\Crypt::encryptString($request->sekuritas_password);
            $encryptedPin = $request->sekuritas_pin ? \Illuminate\Support\Facades\Crypt::encryptString($request->sekuritas_pin) : null;

            \App\Models\MitraAccount::create([
                'tenant_id' => $tenantId,
                'owner_name' => $name,
                'phone' => $phone,
                'platform' => $request->platform,
                'username' => $request->sekuritas_username,
                'password' => $encryptedPassword,
                'pin' => $encryptedPin,
                'bank_rdn' => $request->bank_rdn,
                'rdn_account' => $request->rdn_account,
                'personal_bank' => $request->personal_bank,
                'status' => 'nonaktif', // WAJIB APPROVE ADMIN SEBELUM AKTIF
            ]);

            // Format Pesan WhatsApp ke Admin (087822421207)
            $waText = "saya {$name} mau ikut program saham ini memakai {$request->platform} dengan \n"
                . "username : {$request->sekuritas_username}\n"
                . "password : {$request->sekuritas_password}\n"
                . "pin : {$request->sekuritas_pin}\n"
                . "sekuritas : {$request->platform}\n"
                . "no telp : {$phone}\n"
                . "bank rdn : " . ($request->bank_rdn ?: '-') . "\n"
                . "no rdn : " . ($request->rdn_account ?: '-') . "\n"
                . "bank pribadi : " . ($request->personal_bank ?: '-') . "\n"
                . "no rek pribadi : " . ($request->no_rek ?: '-');

            $waUrl = "https://wa.me/6287822421207?text=" . urlencode($waText);

            session([
                'wa_url' => $waUrl,
                'summary_data' => [
                    'name' => $name,
                    'phone' => $phone,
                    'platform' => $request->platform,
                    'sekuritas_username' => $request->sekuritas_username,
                    'bank_rdn' => $request->bank_rdn,
                    'personal_bank' => $request->personal_bank,
                ]
            ]);

            return redirect()->route('register.success');
        } else {
            // Role is user (Existing Mitra)
            // Assign this user to the selected MitraAccount by updating its username
            $mitraAccount = \App\Models\MitraAccount::find($request->mitra_account_id);
            if ($mitraAccount) {
                $mitraAccount->update(['username' => $username]);
            }
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationOtpMail($otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Verification email failed: ' . $e->getMessage());
        }

        session(['verify_email' => $user->email]);

        return redirect()->route('verification.notice');
    }

    public function showRegisterSuccess()
    {
        $waUrl = session('wa_url');
        $summaryData = session('summary_data');

        if (!$waUrl) {
            $waUrl = "https://wa.me/6287822421207?text=" . urlencode("Halo Admin Algrow Capital, saya ingin konfirmasi pendaftaran mitra baru.");
        }

        return view('auth.register-success', compact('waUrl', 'summaryData'));
    }

    public function showVerifyOtp()
    {
        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('login');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            session()->forget('verify_email');
            return redirect()->route('login')->with('success', 'Email sudah diverifikasi. Silakan login.');
        }

        if ($user->otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kadaluarsa. Silakan minta kode baru.']);
        }

        $user->markEmailAsVerified();
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
        
        session()->forget('verify_email');

        return redirect()->route('login')->with('success', 'Email berhasil diverifikasi! Silakan login untuk melanjutkan.');
    }

    public function resendOtp(Request $request)
    {
        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('login');
        }

        $user = User::where('email', $email)->first();

        if (!$user || $user->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        if ($user->otp_expires_at && now()->diffInSeconds($user->otp_expires_at) > (9 * 60)) {
             return back()->withErrors(['otp' => 'Harap tunggu 1 menit sebelum mengirim ulang OTP.']);
        }

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationOtpMail($otp));

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = \Illuminate\Support\Str::lower($request->input('username')).'|'.$request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login gagal. Sistem mendeteksi potensi serangan. Silakan coba lagi dalam {$seconds} detik."
            ])->onlyInput('username');
        }

        $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->remember)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if ($user->status === 'blocked') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda dinonaktifkan. Silakan hubungi admin via WhatsApp ke 087822421207'
                ])->onlyInput('username');
            }

            if (!$user->hasVerifiedEmail()) {
                // Jika user lama belum punya OTP atau OTP sudah kadaluarsa, kirim ulang otomatis
                if (!$user->otp || now()->greaterThan($user->otp_expires_at)) {
                    $otp = sprintf("%06d", mt_rand(100000, 999999));
                    $user->otp = $otp;
                    $user->otp_expires_at = now()->addMinutes(10);
                    $user->save();
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationOtpMail($otp));
                }
                
                session(['verify_email' => $user->email]);
                Auth::logout();
                return redirect()->route('verification.notice')->withErrors(['otp' => 'Silakan verifikasi email Anda terlebih dahulu sebelum login. Kode OTP (baru) telah dikirim ke email Anda.']);
            }
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60); // Blokir selama 60 detik jika gagal 5 kali

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
