<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // Auto-sync from MitraAccount if User profile is missing key data or corrupted (encrypted string mistakenly saved)
        if (!$user->sekuritas || !$user->password_sekuritas || str_starts_with($user->password_sekuritas, 'eyJ')) {
            $mitraAcc = \App\Models\MitraAccount::where('username', $user->username)->first();
            if ($mitraAcc) {
                if (!$user->sekuritas) $user->sekuritas = $mitraAcc->platform;
                
                // If it's missing or corrupted (starts with eyJ)
                if (!$user->password_sekuritas || str_starts_with($user->password_sekuritas, 'eyJ')) {
                    try { 
                        $user->password_sekuritas = $mitraAcc->password ? \Illuminate\Support\Facades\Crypt::decryptString($mitraAcc->password) : null; 
                    } catch(\Exception $e) {
                        $user->password_sekuritas = $mitraAcc->password; // Fallback to raw text if not encrypted
                    }
                }
                
                // If it's missing or corrupted (starts with eyJ)
                if (!$user->pin_sekuritas || str_starts_with($user->pin_sekuritas, 'eyJ')) {
                    try { 
                        $user->pin_sekuritas = $mitraAcc->pin ? \Illuminate\Support\Facades\Crypt::decryptString($mitraAcc->pin) : null; 
                    } catch(\Exception $e) {
                        $user->pin_sekuritas = $mitraAcc->pin; // Fallback to raw text if not encrypted
                    }
                }
                
                if (!$user->bank) $user->bank = $mitraAcc->bank_rdn;
                if (!$user->no_rek) $user->no_rek = $mitraAcc->rdn_account;
                $user->save();
            }
        }

        return view('profile.edit', compact('user'));
    }

    private function ensureUserColumns()
    {
        try {
            $url = env('DATABASE_URL');
            if ($url && str_contains($url, ':6543')) {
                $sessionUrl = str_replace(':6543', ':5432', $url);
                config(['database.connections.pgsql.url' => $sessionUrl]);
                \Illuminate\Support\Facades\DB::purge('pgsql');
            }

            if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(255) NULL;');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS sekuritas VARCHAR(255) NULL;');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS password_sekuritas VARCHAR(255) NULL;');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS pin_sekuritas VARCHAR(255) NULL;');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS bank VARCHAR(255) NULL;');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS no_rek VARCHAR(255) NULL;');
            } elseif (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
                $columns = ['phone', 'sekuritas', 'password_sekuritas', 'pin_sekuritas', 'bank', 'no_rek'];
                foreach ($columns as $col) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', $col)) {
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users ADD COLUMN `{$col}` VARCHAR(255) NULL;");
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ensureUserColumns failed: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'sekuritas' => 'nullable|string|max:255',
            'password_sekuritas' => 'nullable|string|max:255',
            'pin_sekuritas' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:255',
            'no_rek' => 'nullable|string|max:255',
        ]);

        try {
            $user->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), '42703') || str_contains($e->getMessage(), 'Unknown column')) {
                $this->ensureUserColumns();
                $user->update($validated);
            } else {
                throw $e;
            }
        }

        return redirect()->route('my-profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePhone(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
        ], [
            'phone.required' => 'Nomor telepon wajib diisi.'
        ]);

        try {
            $user->update(['phone' => $validated['phone']]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'phone') && (str_contains($e->getMessage(), '42703') || str_contains($e->getMessage(), 'Unknown column'))) {
                $this->ensureUserColumns();
                $user->update(['phone' => $validated['phone']]);
            } else {
                throw $e;
            }
        }

        return redirect()->back()->with('success', 'Nomor telepon berhasil disimpan.');
    }
}
