<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // Auto-sync from MitraAccount if User profile is missing key data
        if (!$user->sekuritas || !$user->password_sekuritas) {
            $mitraAcc = \App\Models\MitraAccount::where('username', $user->username)->first();
            if ($mitraAcc) {
                if (!$user->sekuritas) $user->sekuritas = $mitraAcc->platform;
                
                if (!$user->password_sekuritas) {
                    try { 
                        $user->password_sekuritas = $mitraAcc->password ? \Illuminate\Support\Facades\Crypt::decryptString($mitraAccount->password) : null; 
                    } catch(\Exception $e) {
                        $user->password_sekuritas = $mitraAcc->password; // Fallback to raw text if not encrypted
                    }
                }
                
                if (!$user->pin_sekuritas) {
                    try { 
                        $user->pin_sekuritas = $mitraAcc->pin ? \Illuminate\Support\Facades\Crypt::decryptString($mitraAccount->pin) : null; 
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
