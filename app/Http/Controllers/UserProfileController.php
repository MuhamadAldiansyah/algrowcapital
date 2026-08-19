<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
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
