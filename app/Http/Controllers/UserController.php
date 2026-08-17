<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the users with their activity status.
     */
    public function index(Request $request)
    {
        // 5 minute threshold for "Online" status
        $threshold = time() - 300;
        
        $query = User::query();
        if (auth()->user()->role !== 'developer') {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        // Fetch users and join with the latest session data
        $users = $query->get()->map(function ($user) use ($threshold) {
            // Get the latest session for this user
            $session = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderBy('last_activity', 'desc')
                ->first();

            $user->is_online = $session && $session->last_activity >= $threshold;
            $user->last_activity_time = $session ? Carbon::createFromTimestamp($session->last_activity) : null;
            $user->ip_address = $session ? $session->ip_address : 'N/A';
            $user->user_agent = $session ? $session->user_agent : 'N/A';
            
            return $user;
        });

        // Separate online and offline users
        $onlineUsers = $users->where('is_online', true)->sortByDesc('last_activity_time');
        $offlineUsers = $users->where('is_online', false)->sortByDesc('last_activity_time');
        
        $totalActiveCount = $onlineUsers->count();

        if ($request->ajax()) {
            return view('users.partials.user-list', compact('onlineUsers', 'offlineUsers', 'totalActiveCount'));
        }

        return view('users.index', compact('onlineUsers', 'offlineUsers', 'totalActiveCount'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $allowedRoles = ['investor', 'user'];
        if (auth()->user()->role === 'developer') {
            $allowedRoles = ['admin', 'investor', 'developer', 'owner', 'user'];
        } elseif (auth()->user()->role === 'owner') {
            $allowedRoles = ['admin', 'investor', 'user'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'status' => 'required|in:active,blocked',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        
        if (auth()->user()->role === 'developer') {
            $validated['tenant_id'] = $request->input('tenant_id') ?: null;
        } else {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'developer' && $user->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Akses ditolak.');
        }

        $allowedRoles = ['investor', 'user'];
        if (auth()->user()->role === 'developer') {
            $allowedRoles = ['admin', 'investor', 'developer', 'owner', 'user'];
        } elseif (auth()->user()->role === 'owner') {
            $allowedRoles = ['admin', 'investor', 'user'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'status' => 'required|in:active,blocked',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (auth()->user()->role === 'developer') {
            $validated['tenant_id'] = $request->input('tenant_id') ?: null;
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'developer' && $user->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->investor) {
            $user->investor->delete();
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
