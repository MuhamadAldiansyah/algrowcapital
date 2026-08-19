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

        $user->update($validated);

        return redirect()->route('my-profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
