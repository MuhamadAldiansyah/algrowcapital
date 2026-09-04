@extends('layouts.app')

@section('title', 'Detail Grup: ' . $mitraGroup->name)

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom border-emerald-900 border-opacity-50 pb-3 mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-white mb-0"><i class="fa-solid fa-layer-group me-2 text-emerald-400"></i>{{ $mitraGroup->name }}</h4>
        <p class="text-emerald-400 small mb-0"><i class="fa-solid fa-user-tie me-1"></i> Handler: <strong>{{ $mitraGroup->handler_name }}</strong></p>
    </div>
    
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
        <a href="{{ route('mitra-groups.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm w-100 w-sm-auto text-center">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
        <button type="button" class="btn btn-outline-light rounded-pill px-4 shadow-sm w-100 w-sm-auto text-center" data-bs-toggle="modal" data-bs-target="#editGroupModal">
            <i class="fa-solid fa-pen-nib me-2"></i>Edit Grup
        </button>
    </div>
</div>

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="background: rgba(5, 22, 12, 0.95); backdrop-filter: blur(16px); box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(16, 185, 129, 0.2), 0 0 30px rgba(16, 185, 129, 0.1); border-radius: 20px;">
            <form action="{{ route('mitra-groups.update', $mitraGroup->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Glowing Header -->
                <div class="modal-header border-0 pb-0 position-relative" style="background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.15) 0%, transparent 70%);">
                    <div>
                        <h4 class="modal-title text-white fw-bold ticker-font mb-1" style="text-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);">
                            <i class="fa-solid fa-pen-nib text-emerald-400 me-2"></i>EDIT GRUP
                        </h4>
                        <p class="small text-emerald-500 opacity-75 mb-0">Konfigurasi nama dan handler grup.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white opacity-50 hover-emerald position-absolute top-0 end-0 m-4" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-emerald-500 fw-bold small text-uppercase tracking-wide mb-2">Nama Grup</label>
                        <div class="input-group">
                            <span class="input-group-text bg-black bg-opacity-40 border-emerald-900 border-end-0 text-emerald-500 border-opacity-50">
                                <i class="fa-solid fa-layer-group"></i>
                            </span>
                            <input type="text" name="name" class="form-control bg-black bg-opacity-40 border-emerald-900 border-start-0 text-white shadow-none px-3 py-2 ticker-font fs-6" required value="{{ $mitraGroup->name }}" placeholder="Contoh: GRUP ALPHA">
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-emerald-500 fw-bold small text-uppercase tracking-wide mb-2">Nama Handler</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-black bg-opacity-40 border-emerald-900 border-end-0 text-emerald-500 border-opacity-50">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <input type="text" name="handler_name" class="form-control bg-black bg-opacity-40 border-emerald-900 border-start-0 text-white shadow-none px-3 py-2 ticker-font fs-6" required value="{{ $mitraGroup->handler_name }}" placeholder="Nama PIC Utama">
                        </div>
                        
                        <div class="d-flex align-items-start p-3 rounded-3 border border-warning border-opacity-25" style="background: rgba(255, 193, 7, 0.05);">
                            <i class="fa-solid fa-circle-info text-warning mt-1 me-3 fs-5"></i>
                            <p class="mb-0 small text-warning opacity-75 lh-sm" style="font-size: 0.8rem;">
                                <strong>Perhatian:</strong> Jika Anda mengubah nama handler, maka nama handler di <u>seluruh akun</u> yang berada di dalam Grup ini akan ikut tersinkronisasi secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary border-0 text-white opacity-50 hover-emerald px-4 rounded-pill" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-primary-custom px-4 rounded-pill shadow-lg" style="flex: 1;">
                        <i class="fa-solid fa-check-circle me-2"></i> SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Accounts IN Group -->
    <div class="col-xl-6">
        <div class="card stat-node border-0 shadow-lg h-100">
            <div class="card-header border-bottom border-emerald-900 p-4 bg-emerald-900 bg-opacity-10 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <h6 class="text-white fw-bold mb-0 text-center text-sm-start"><i class="fa-solid fa-users me-2 text-emerald-400"></i>ANGGOTA GRUP ({{ $groupAccounts->count() }})</h6>
                <button type="button" class="btn btn-outline-danger w-100 w-sm-auto rounded-pill" id="btnRemoveSelected" disabled onclick="document.getElementById('formRemoveAccounts').submit();">
                    Keluarkan (<span id="removeCount">0</span>)
                </button>
            </div>
            <div class="card-body p-0">
                <form action="{{ route('mitra-groups.remove', $mitraGroup->id) }}" method="POST" id="formRemoveAccounts">
                    @csrf
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table align-middle mb-0 table-hover" style="font-size: 0.85rem;">
                            <thead class="text-white sticky-top bg-emerald-950">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input border-emerald-500 bg-transparent" type="checkbox" id="selectAllRemove">
                                        </div>
                                    </th>
                                    <th>NAMA & ID</th>
                                    <th>PLATFORM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupAccounts as $account)
                                <tr class="news-hover cursor-pointer" onclick="toggleRemoveCheckbox({{ $account->id }})">
                                    <td class="ps-4" onclick="event.stopPropagation();">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input remove-checkbox border-emerald-500 bg-transparent" type="checkbox" name="account_ids[]" value="{{ $account->id }}" id="chkRemove{{ $account->id }}" onchange="updateRemoveCounter()">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $account->owner_name }}</div>
                                        <div class="text-white opacity-50 ticker-font small">#{{ $account->id }} | {{ $account->username }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-black bg-opacity-40 text-white border border-white border-opacity-25 px-2">
                                            {{ strtoupper($account->platform) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <i class="fa-solid fa-box-open fs-1 mb-3 text-white opacity-20 d-block"></i>
                                        <span class="text-white opacity-75">Grup ini masih kosong.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Available Accounts -->
    <div class="col-xl-6">
        <div class="card stat-node border-0 shadow-lg h-100 border-start border-emerald-500 border-4">
            <div class="card-header border-bottom border-emerald-900 p-4 bg-black bg-opacity-50">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                    <h6 class="text-white fw-bold mb-0 text-center text-sm-start"><i class="fa-solid fa-user-plus me-2 text-emerald-400"></i>TAMBAHKAN AKUN</h6>
                    <button type="button" class="btn btn-primary-custom fw-bold w-100 w-sm-auto rounded-pill" id="btnAssignSelected" disabled onclick="document.getElementById('formAssignAccounts').submit();">
                        <i class="fa-solid fa-arrow-left me-1"></i> Masukkan (<span id="assignCount">0</span>)
                    </button>
                </div>
                
                <form action="{{ route('mitra-groups.show', $mitraGroup->id) }}" method="GET">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-emerald-900 text-white">
                            <i class="fa-solid fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-emerald-900 border-start-0" placeholder="Cari akun nganggur..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-outline-emerald border-emerald-900">CARI</button>
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <form action="{{ route('mitra-groups.assign', $mitraGroup->id) }}" method="POST" id="formAssignAccounts">
                    @csrf
                    <div class="table-responsive" style="max-height: 550px;">
                        <table class="table align-middle mb-0 table-hover" style="font-size: 0.85rem;">
                            <thead class="text-white sticky-top bg-black">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input border-emerald-500 bg-transparent" type="checkbox" id="selectAllAssign">
                                        </div>
                                    </th>
                                    <th>NAMA & ID</th>
                                    <th>STATUS GRUP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableAccounts as $account)
                                <tr class="news-hover cursor-pointer" onclick="toggleAssignCheckbox({{ $account->id }})">
                                    <td class="ps-4" onclick="event.stopPropagation();">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input assign-checkbox border-emerald-500 bg-transparent" type="checkbox" name="account_ids[]" value="{{ $account->id }}" id="chkAssign{{ $account->id }}" onchange="updateAssignCounter()">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $account->owner_name }}</div>
                                        <div class="text-white opacity-50 ticker-font small">#{{ $account->id }} | {{ $account->username }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">Nganggur</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <span class="text-white opacity-50">Tidak ada akun tersedia.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .ticker-font { font-family: 'Courier New', Courier, monospace; }
    .custom-checkbox .form-check-input { width: 18px; height: 18px; cursor: pointer; }
    .custom-checkbox .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
</style>

<script>
    // Remove Logic
    function toggleRemoveCheckbox(id) {
        const checkbox = document.getElementById('chkRemove' + id);
        if(checkbox) { checkbox.checked = !checkbox.checked; updateRemoveCounter(); }
    }

    function updateRemoveCounter() {
        const checkboxes = document.querySelectorAll('.remove-checkbox:checked');
        document.getElementById('removeCount').innerText = checkboxes.length;
        document.getElementById('btnRemoveSelected').disabled = checkboxes.length === 0;
    }

    document.getElementById('selectAllRemove').addEventListener('change', function(e) {
        document.querySelectorAll('.remove-checkbox').forEach(chk => chk.checked = e.target.checked);
        updateRemoveCounter();
    });

    // Assign Logic
    function toggleAssignCheckbox(id) {
        const checkbox = document.getElementById('chkAssign' + id);
        if(checkbox) { checkbox.checked = !checkbox.checked; updateAssignCounter(); }
    }

    function updateAssignCounter() {
        const checkboxes = document.querySelectorAll('.assign-checkbox:checked');
        document.getElementById('assignCount').innerText = checkboxes.length;
        document.getElementById('btnAssignSelected').disabled = checkboxes.length === 0;
    }

    document.getElementById('selectAllAssign').addEventListener('change', function(e) {
        document.querySelectorAll('.assign-checkbox').forEach(chk => chk.checked = e.target.checked);
        updateAssignCounter();
    });
</script>
@endsection
