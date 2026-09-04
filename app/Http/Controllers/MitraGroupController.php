<?php

namespace App\Http\Controllers;

use App\Models\MitraGroup;
use App\Models\MitraAccount;
use Illuminate\Http\Request;

class MitraGroupController extends Controller
{
    public function index()
    {
        $groups = MitraGroup::withCount(['accounts' => function($q) {
            $q->where('status', 'aktif');
        }])->get();
        
        $user = auth()->user();
        $adminQuery = \App\Models\User::where('role', 'admin');
        
        // Jika bukan developer, batasi admin hanya dari tenant (perusahaan) yang sama
        if ($user && $user->role !== 'developer') {
            $adminQuery->where('tenant_id', $user->tenant_id);
        }
        
        $admins = $adminQuery->orderBy('name')->get();

        return view('mitra-groups.index', compact('groups', 'admins'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'handler_name' => 'required|string|max:255',
        ]);

        MitraGroup::create($validated);
        return redirect()->route('mitra-groups.index')->with('success', 'Wadah baru berhasil dibuat.');
    }

    public function update(Request $request, MitraGroup $mitraGroup)
    {
        if (auth()->user()->role === 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'handler_name' => 'required|string|max:255',
        ]);

        $mitraGroup->update($validated);
        
        // Also update handler_name on all accounts within this group
        MitraAccount::where('mitra_group_id', $mitraGroup->id)->update(['handler_name' => $validated['handler_name']]);

        return redirect()->back()->with('success', 'Wadah berhasil diperbarui.');
    }

    public function destroy(MitraGroup $mitraGroup)
    {
        if (auth()->user()->role === 'admin') {
            abort(403, 'Akses ditolak.');
        }

        // Nullify the group_id on all accounts, and clear their handler_name
        MitraAccount::where('mitra_group_id', $mitraGroup->id)->update([
            'mitra_group_id' => null,
            'handler_name' => null
        ]);
        
        $mitraGroup->delete();
        return redirect()->route('mitra-groups.index')->with('success', 'Wadah berhasil dihapus.');
    }

    public function show(Request $request, MitraGroup $mitraGroup)
    {
        $search = $request->input('search');
        
        // Accounts already in this group
        $groupAccounts = $mitraGroup->accounts()->where('status', 'aktif')->get();
        
        // Available accounts (not in ANY group)
        $query = MitraAccount::whereNull('mitra_group_id')->where('status', 'aktif')->orderBy('id', 'asc');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('owner_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        $availableAccounts = $query->get();

        return view('mitra-groups.show', compact('mitraGroup', 'groupAccounts', 'availableAccounts', 'search'));
    }

    public function assignAccounts(Request $request, MitraGroup $mitraGroup)
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:mitra_accounts,id'
        ]);

        // Assign them to this group, and update their handler_name to match the group's
        MitraAccount::whereIn('id', $request->account_ids)->update([
            'mitra_group_id' => $mitraGroup->id,
            'handler_name' => $mitraGroup->handler_name
        ]);

        return redirect()->back()->with('success', count($request->account_ids) . ' akun berhasil ditambahkan ke wadah.');
    }

    public function removeAccounts(Request $request, MitraGroup $mitraGroup)
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:mitra_accounts,id'
        ]);

        // Remove from group and clear handler
        MitraAccount::whereIn('id', $request->account_ids)
            ->where('mitra_group_id', $mitraGroup->id) // Security check
            ->update([
                'mitra_group_id' => null,
                'handler_name' => null
            ]);

        return redirect()->back()->with('success', count($request->account_ids) . ' akun berhasil dikeluarkan dari wadah.');
    }
}
