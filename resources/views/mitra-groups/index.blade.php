@extends('layouts.app')

@section('title', 'Handler Mitra')

@section('content')
<div class="row g-4 mt-n3 mt-md-0">
    @if(in_array(Auth::user()->role, ['owner', 'developer']))
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card stat-node border-0 shadow-sm sticky-md-top" style="top: 80px; z-index: 10;">
            <div class="card-header bg-transparent border-bottom border-emerald-900 pt-3 pb-2 px-4">
                <h5 class="fw-bold text-emerald-400 mb-0" id="form-title"><i class="fa-solid fa-plus-circle me-2"></i>Buat Handler Baru</h5>
            </div>
            <div class="card-body px-4 pt-2 pb-4">
                <form id="groupForm" action="{{ route('mitra-groups.store') }}" method="POST">
                    @csrf
                    <div id="method-container"></div>
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">NAMA HANDLER</label>
                        <input type="text" id="group_name" name="name" class="form-control border-emerald-900 bg-black bg-opacity-25 text-white" required placeholder="Contoh: Tim Alpha">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-white small fw-bold">NAMA HANDLER (Pilih Admin)</label>
                        <select id="group_handler" name="handler_name" class="form-select border-emerald-900 bg-black bg-opacity-25 text-white" required>
                            <option value="">-- Pilih Admin --</option>
                            @forelse($admins as $admin)
                                <option value="{{ $admin->name }}">{{ $admin->name }}</option>
                            @empty
                                <option value="" disabled>Belum ada user dengan role Admin</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1 fw-bold py-2"><i class="fa-solid fa-save me-2"></i><span id="text-submit">SIMPAN Handler</span></button>
                        <button type="button" id="btn-cancel" class="btn btn-outline-secondary d-none px-3" onclick="resetForm()" title="Batal Edit"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8 mt-5 pt-4 pt-md-0 mt-md-0">
    @else
    <div class="col-md-12 mt-2">
    @endif
        <h6 class="text-emerald-400 mb-2 fw-bold"><i class="fa-solid fa-boxes-stacked me-2"></i>DAFTAR HANDLER TERSEDIA</h6>
        <div class="row g-3">
            @forelse($groups as $group)
            <div class="col-sm-6">
                <div class="card stat-node border-0 shadow-sm h-100 news-hover" style="cursor: pointer;" onclick="window.location='{{ route('mitra-groups.show', $group->id) }}'">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="fw-bold text-white mb-0" style="margin-right: 15px;">{{ $group->name }}</h5>
                            @if(in_array(Auth::user()->role, ['owner', 'developer']))
                            <div class="dropdown" onclick="event.stopPropagation();">
                                <button class="btn btn-sm btn-link text-emerald-500 opacity-50 hover-emerald p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical fs-5 px-2"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end bg-black border border-emerald-900 shadow-lg" style="min-width: 160px;">
                                    <li><button type="button" class="dropdown-item text-white hover-emerald" onclick="editGroup('{{ $group->id }}', '{{ addslashes($group->name) }}', '{{ addslashes($group->handler_name) }}')"><i class="fa-solid fa-pen-to-square me-2" style="width: 15px;"></i> Edit Handler</button></li>
                                    <li>
                                        <form action="{{ route('mitra-groups.destroy', $group->id) }}" method="POST" onsubmit="confirmDelete(event, 'Hapus Handler ini? Akun-akun di dalamnya akan kembali nganggur (tanpa handler).')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash-can me-2" style="width: 15px;"></i> Hapus Handler</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                        <p class="text-emerald-400 small mb-3"><i class="fa-solid fa-user-tie me-1"></i> Handler: {{ $group->handler_name }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <span class="badge bg-emerald-900 text-emerald-400 px-3 py-2 rounded-pill"><i class="fa-solid fa-users me-1"></i> {{ $group->accounts_count }} Akun Terdaftar</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-folder-open fs-1 mb-3 text-emerald-500 opacity-20 d-block"></i>
                <span class="text-white fw-bold">Belum ada Handler yang dibuat</span>
                <p class="text-white opacity-50 small mb-0 mt-1">Silakan buat Handler baru di panel sebelah kiri.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('groupForm');
    const methodContainer = document.getElementById('method-container');
    const inputName = document.getElementById('group_name');
    const inputHandler = document.getElementById('group_handler');
    const formTitle = document.getElementById('form-title');
    const textSubmit = document.getElementById('text-submit');
    const btnCancel = document.getElementById('btn-cancel');
    
    function editGroup(id, name, handler) {
        form.action = `/mitra-groups/${id}`;
        methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        inputName.value = name;
        
        // Cek apakah opsi sudah ada, jika belum tambahkan sementara agar tidak kosong
        let optionExists = Array.from(inputHandler.options).some(option => option.value === handler);
        if (!optionExists && handler) {
            let newOption = new Option(handler + " (Tidak Aktif/Manual)", handler, true, true);
            inputHandler.add(newOption);
        } else {
            inputHandler.value = handler;
        }
        
        formTitle.innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Edit Handler';
        textSubmit.innerText = 'UPDATE Handler';
        btnCancel.classList.remove('d-none');
        
        // Scroll to form smoothly
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function resetForm() {
        form.action = `{{ route('mitra-groups.store') }}`;
        methodContainer.innerHTML = '';
        form.reset();
        
        formTitle.innerHTML = '<i class="fa-solid fa-plus-circle me-2"></i>Buat Handler Baru';
        textSubmit.innerText = 'SIMPAN Handler';
        btnCancel.classList.add('d-none');
    }
</script>
@endsection
