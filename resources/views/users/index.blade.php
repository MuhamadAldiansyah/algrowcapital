@extends('layouts.app')

@section('title', 'Manajemen User & Aktivitas')

@section('content')
<style>
    .pulse-online {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #22c55e;
        border-radius: 50%;
        margin-right: 8px;
        box-shadow: 0 0 0 rgba(34, 197, 94, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background-color: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #071f11;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(34, 197, 94, 0.02);
    }

    .status-badge-offline {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
</style>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <p class="text-emerald-400 opacity-75 small mb-0">Kelola akun pengguna dan pantau aktivitas mereka secara real-time.</p>
    </div>
    <button type="button" class="btn btn-primary-custom rounded-pill shadow-lg fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fa-solid fa-plus me-2"></i>TAMBAH USER
    </button>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-black border border-emerald-900 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-bottom border-emerald-900 pb-3">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-user-plus text-emerald-500 me-2"></i>Tambah User Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">NAMA LENGKAP</label>
                        <input type="text" name="name" class="form-control bg-black text-white border-emerald-900" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">USERNAME</label>
                        <input type="text" name="username" class="form-control bg-black text-white border-emerald-900" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">EMAIL</label>
                        <input type="email" name="email" class="form-control bg-black text-white border-emerald-900" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">ROLE</label>
                        <select name="role" class="form-select bg-black text-white border-emerald-900" required>
                            <option value="investor">Investor</option>
                            <option value="user">User Biasa</option>
                            @if(Auth::user()->role === 'developer' || Auth::user()->role === 'owner')
                            <option value="admin">Admin</option>
                            @endif
                            @if(Auth::user()->role === 'developer')
                            <option value="developer">Developer</option>
                            <option value="owner">Owner</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">STATUS AKUN</label>
                        <select name="status" class="form-select bg-black text-white border-emerald-900" required>
                            <option value="active">Aktif</option>
                            <option value="blocked">Diblokir</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">PASSWORD</label>
                        <div class="input-group">
                            <input type="password" name="password" id="add_password" class="form-control bg-black text-white border-emerald-900 border-end-0" required minlength="6">
                            <button class="btn border border-emerald-900 border-start-0 bg-black text-emerald-500" type="button" onclick="togglePassword('add_password', 'add_eye_icon')">
                                <i class="fa-solid fa-eye" id="add_eye_icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-emerald-900 pt-3">
                    <button type="button" class="btn btn-dark text-white rounded-pill px-4" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-2"></i>SIMPAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-black border border-emerald-900 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-bottom border-emerald-900 pb-3">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-user-pen text-emerald-500 me-2"></i>Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">NAMA LENGKAP</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-black text-white border-emerald-900" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">USERNAME</label>
                        <input type="text" name="username" id="edit_username" class="form-control bg-black text-white border-emerald-900" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">EMAIL</label>
                        <input type="email" name="email" id="edit_email" class="form-control bg-black text-white border-emerald-900" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">ROLE</label>
                        <select name="role" id="edit_role" class="form-select bg-black text-white border-emerald-900" required>
                            <option value="investor">Investor</option>
                            <option value="user">User Biasa</option>
                            @if(Auth::user()->role === 'developer' || Auth::user()->role === 'owner')
                            <option value="admin">Admin</option>
                            @endif
                            @if(Auth::user()->role === 'developer')
                            <option value="developer">Developer</option>
                            <option value="owner">Owner</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">STATUS AKUN</label>
                        <select name="status" id="edit_status" class="form-select bg-black text-white border-emerald-900" required>
                            <option value="active">Aktif</option>
                            <option value="blocked">Diblokir</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">PASSWORD (Opsional)</label>
                        <div class="input-group">
                            <input type="password" name="password" id="edit_password" class="form-control bg-black text-white border-emerald-900 border-end-0" minlength="6" placeholder="Kosongkan jika tidak ingin diubah">
                            <button class="btn border border-emerald-900 border-start-0 bg-black text-emerald-500" type="button" onclick="togglePassword('edit_password', 'edit_eye_icon')">
                                <i class="fa-solid fa-eye" id="edit_eye_icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-emerald-900 pt-3">
                    <button type="button" class="btn btn-dark text-white rounded-pill px-4" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-2"></i>SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div id="user-activity-container">
    @include('users.partials.user-list')
</div>

@endsection

@section('scripts')
<script>
    /**
     * Silent Refresh Logic
     * Fetches the latest user activity data every 5 seconds without refreshing the page.
     */
    function fetchActiveUsers() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById('user-activity-container');
            if (container) {
                container.innerHTML = html;
            }
        })
        .catch(error => console.warn('Silent refresh failed:', error));
    }

    // Start polling every 5 seconds
    setInterval(fetchActiveUsers, 5000);

    function editUser(id, name, username, email, role, status) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_password').value = '';
        document.getElementById('editUserForm').action = '/users/' + id;
        
        var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        editModal.show();
    }
    function togglePassword(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
@endsection
