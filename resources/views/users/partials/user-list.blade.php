<div class="row mb-4">
    <!-- Active Summary Card -->
    <div class="col-md-4">
        <div class="card stat-node border-start border-4 border-success shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-emerald-500 mb-1 text-uppercase small fw-bold opacity-75">User Aktif Saat Ini</h6>
                        <h2 class="fw-bold text-white mb-0 d-flex align-items-center ticker-font">
                            {{ $totalActiveCount }}
                            <span class="pulse-online ms-3" style="width: 12px; height: 12px;"></span>
                        </h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center border border-success border-opacity-25 shadow-sm" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-signal-status fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: ONLINE USERS -->
<div class="card stat-node border-0 shadow-lg mb-4 overflow-hidden" style="border-radius: 15px;">
    <div class="card-header bg-black bg-opacity-20 pt-4 pb-2 border-bottom border-emerald-900 d-flex justify-content-between align-items-center px-4">
        <h5 class="fw-bold mb-0 text-white">
            <i class="fa-solid fa-bolt me-2 text-warning"></i> PENGGUNA ONLINE <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 ms-2" style="font-size: 0.7rem;">{{ $onlineUsers->count() }} TERDETEKSI</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">USER</th>
                        <th>ROLE</th>
                        <th>NO. TELEPON</th>
                        <th>IP ADDRESS</th>
                        <th>AKTIVITAS TERAKHIR</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-end pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onlineUsers as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3 bg-success bg-opacity-20 text-success border border-success border-opacity-25 fw-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-white">{{ $user->name }}</div>
                                    <div class="text-emerald-500 small opacity-75">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge @if($user->role === 'developer') text-primary border-primary @elseif($user->role === 'admin') text-danger border-danger @else text-secondary border-secondary @endif bg-black bg-opacity-40 border border-opacity-25 px-3 py-2 text-uppercase" style="font-size: 0.65rem;">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>
                            <div class="text-white small">{{ $user->phone ?? '-' }}</div>
                        </td>
                        <td><code class="text-emerald-400 bg-black bg-opacity-20 px-2 py-1 rounded small">{{ $user->ip_address }}</code></td>
                        <td>
                            <div class="small fw-medium text-white ticker-font">
                                <i class="fa-regular fa-clock me-1 text-emerald-500"></i> {{ $user->last_activity_time->diffForHumans() }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 shadow-sm" style="font-size: 0.65rem;">
                                <span class="pulse-online" style="width: 8px; height: 8px; margin-right: 5px;"></span> ONLINE
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            @if(in_array(Auth::user()->role, ['owner', 'developer']))
                            <button class="btn btn-sm text-white border border-emerald-500 border-opacity-50 bg-transparent" 
                                onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->username) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', '{{ $user->status ?? 'active' }}', '{{ $user->tenant_id }}')">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm text-danger border border-danger border-opacity-50 bg-transparent">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-emerald-500 opacity-50 small"><i class="fa-solid fa-lock"></i> No Access</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-emerald-500 opacity-50">
                            <i class="fa-solid fa-ghost fs-1 d-block mb-3"></i>
                            TIDAK ADA PENGGUNA YANG SEDANG ONLINE.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECTION 2: ALL / OFFLINE USERS -->
<div class="card stat-node border-0 shadow-lg" style="border-radius: 15px;">
    <div class="card-header bg-black bg-opacity-20 pt-4 pb-2 border-bottom border-emerald-900 px-4">
        <h6 class="fw-bold mb-0 text-emerald-500 small text-uppercase opacity-75">Daftar Semua Pengguna & Riwayat</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: 0.9rem;">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">NAMA</th>
                        <th>ROLE</th>
                        <th>NO. TELEPON</th>
                        <th>AKTIVITAS TERAKHIR</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-end pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offlineUsers as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar user-avatar-small me-3 bg-black bg-opacity-40 text-emerald-500 border border-emerald-900 border-opacity-50 fw-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-white">{{ $user->name }}</div>
                                    <div class="text-emerald-500 small opacity-75">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-black bg-opacity-40 text-emerald-400 border border-emerald-900 border-opacity-30 px-2 py-1 text-uppercase" style="font-size: 0.6rem;">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>
                            <div class="text-white small">{{ $user->phone ?? '-' }}</div>
                        </td>
                        <td class="text-emerald-500 opacity-50 small ticker-font">
                            {{ $user->last_activity_time ? $user->last_activity_time->translatedFormat('d M Y, H:i') : 'OFFLINE' }}
                        </td>
                        <td class="text-center">
                            @if($user->status === 'blocked')
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1" style="font-size: 0.6rem;">
                                    DIBLOKIR
                                </span>
                            @else
                                <span class="badge bg-black bg-opacity-40 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1" style="font-size: 0.6rem;">
                                    OFFLINE
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if(in_array(Auth::user()->role, ['owner', 'developer']))
                            <button class="btn btn-sm text-white border border-emerald-500 border-opacity-50 bg-transparent" 
                                onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->username) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', '{{ $user->status ?? 'active' }}', '{{ $user->tenant_id }}')">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm text-danger border border-danger border-opacity-50 bg-transparent">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-emerald-500 opacity-50 small"><i class="fa-solid fa-lock"></i> No Access</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
