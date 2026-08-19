@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-lg" style="border-radius: 15px;">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-3 border-bottom border-emerald-900 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-white">
                    <i class="fa-solid fa-user me-2 text-emerald-500"></i> Profil Saya
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary-custom" id="btn-edit-mode" onclick="toggleEditMode()">
                    <i class="fa-solid fa-edit me-1"></i> Perubahan Data
                </button>
            </div>
            
            <div class="card-body p-4">
                <!-- VIEW MODE -->
                <div id="profile-view">
                    <div class="row mb-4">
                        <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">NAMA LENGKAP</div>
                        <div class="col-sm-8 text-white fw-bold">{{ $user->name }}</div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">USERNAME</div>
                        <div class="col-sm-8 text-white fw-bold">{{ $user->username }}</div>
                    </div>

                    <div class="col-12 mt-4">
                        <h6 class="text-white border-bottom border-emerald-900 pb-2 mb-3">Informasi Sekuritas & Bank</h6>
                    </div>

                    <div class="row mb-4">
                        <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">SEKURITAS / PLATFORM</div>
                        <div class="col-sm-8">
                            @if($user->sekuritas)
                                <span class="badge bg-black bg-opacity-40 text-emerald-400 border border-emerald-900 border-opacity-50 px-3 py-2 shadow-sm">{{ strtoupper($user->sekuritas) }}</span>
                            @else
                                <span class="text-white opacity-50">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">PASSWORD SEKURITAS</div>
                        <div class="col-sm-8">
                            @if($user->password_sekuritas)
                            <div class="input-group input-group-sm w-75 shadow-sm">
                                <input type="password" readonly class="form-control bg-black bg-opacity-20 border-emerald-900 border-opacity-50 text-white ticker-font" id="pass_view" value="{{ $user->password_sekuritas }}">
                                <button class="btn btn-outline-primary-custom" onclick="togglePassword('pass_view', 'pass_view_icon')">
                                    <i id="pass_view_icon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @else
                                <span class="text-white opacity-50">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">PIN SEKURITAS</div>
                        <div class="col-sm-8">
                            @if($user->pin_sekuritas)
                            <div class="input-group input-group-sm w-75 shadow-sm">
                                <input type="password" readonly class="form-control bg-black bg-opacity-20 border-emerald-900 border-opacity-50 text-white ticker-font" id="pin_view" value="{{ $user->pin_sekuritas }}">
                                <button class="btn btn-outline-primary-custom" onclick="togglePassword('pin_view', 'pin_view_icon')">
                                    <i id="pin_view_icon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @else
                                <span class="text-white opacity-50">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">REKENING BANK</div>
                        <div class="col-sm-8 text-white">
                            <div><span class="text-emerald-500 opacity-50 small">BANK:</span> <span class="fw-bold ticker-font">{{ $user->bank ?? '-' }}</span></div>
                            <div><span class="text-emerald-500 opacity-50 small">NO. REK:</span> <span class="fw-bold ticker-font">{{ $user->no_rek ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- EDIT MODE -->
                <div id="profile-edit" class="d-none">
                    <form action="{{ route('my-profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-emerald-500 small fw-bold">NAMA LENGKAP</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-emerald-500 small fw-bold">USERNAME</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-white border-bottom border-emerald-900 pb-2 mb-3">Informasi Sekuritas & Bank</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label text-emerald-500 small fw-bold">SEKURITAS YANG DIPAKAI (PLATFORM)</label>
                                <input type="text" name="sekuritas" class="form-control" value="{{ old('sekuritas', $user->sekuritas) }}" placeholder="Contoh: Ajaib, IPOT, dll">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-emerald-500 small fw-bold">PASSWORD SEKURITAS</label>
                                <input type="text" name="password_sekuritas" class="form-control" value="{{ old('password_sekuritas', $user->password_sekuritas) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-emerald-500 small fw-bold">PIN SEKURITAS</label>
                                <input type="text" name="pin_sekuritas" class="form-control" value="{{ old('pin_sekuritas', $user->pin_sekuritas) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-emerald-500 small fw-bold">NAMA BANK</label>
                                <input type="text" name="bank" class="form-control" value="{{ old('bank', $user->bank) }}" placeholder="Contoh: BCA, Mandiri, dll">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-emerald-500 small fw-bold">NOMOR REKENING</label>
                                <input type="text" name="no_rek" class="form-control" value="{{ old('no_rek', $user->no_rek) }}">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-emerald-900 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleEditMode()">Batal</button>
                            <button type="submit" class="btn btn-primary-custom px-4">
                                <i class="fa-solid fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEditMode() {
        var viewDiv = document.getElementById('profile-view');
        var editDiv = document.getElementById('profile-edit');
        var btnEdit = document.getElementById('btn-edit-mode');

        if (editDiv.classList.contains('d-none')) {
            editDiv.classList.remove('d-none');
            viewDiv.classList.add('d-none');
            btnEdit.classList.add('d-none');
        } else {
            editDiv.classList.add('d-none');
            viewDiv.classList.remove('d-none');
            btnEdit.classList.remove('d-none');
        }
    }

    function togglePassword(inputId, iconId) {
        var x = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (x.type === "password") {
            x.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            x.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    @if($errors->any())
        // Jika ada error validasi, otomatis buka edit mode
        document.addEventListener('DOMContentLoaded', function() {
            toggleEditMode();
        });
    @endif
</script>
@endsection
