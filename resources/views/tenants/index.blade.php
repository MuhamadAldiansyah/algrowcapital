@extends('layouts.app')

@section('title', 'Daftar Klien (Tenants)')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <p class="text-emerald-400 opacity-75 small mb-0">Kelola perusahaan klien dan status langganan mereka di sini.</p>
    </div>
</div>

<div class="card border-0 shadow-lg">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Nama Perusahaan</th>
                        <th class="px-4 py-3">Owner (Pemilik)</th>
                        <th class="px-4 py-3 text-center">Status Langganan</th>
                        <th class="px-4 py-3 text-center">Berlaku Sampai</th>
                        <th class="px-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                        @php
                            $activeSub = $tenant->subscriptions->first();
                        @endphp
                        <tr>
                            <td class="px-4 py-3 fw-bold">
                                {{ $tenant->name }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $tenant->owner ? $tenant->owner->name . ' (' . $tenant->owner->email . ')' : 'Tidak ada' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($tenant->owner && $tenant->owner->role === 'developer')
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                                        <i class="fa-solid fa-crown me-1"></i> VIP (Developer)
                                    </span>
                                @elseif($activeSub)
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-check-circle me-1"></i> {{ $activeSub->plan->name }} (Aktif)
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-xmark-circle me-1"></i> Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($tenant->owner && $tenant->owner->role === 'developer')
                                    <span class="badge bg-warning text-dark border border-warning border-opacity-50 px-3 py-1 rounded-pill" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-infinity me-1"></i> Lifetime
                                    </span>
                                @else
                                    {{ $activeSub ? $activeSub->end_date->format('d M Y') : '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-md-end text-center">
                                @if($tenant->owner && $tenant->owner->role === 'developer')
                                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-md-end gap-2" id="action-btns-{{ $tenant->id }}">
                                        <button type="button" class="btn btn-outline-warning btn-sm rounded-pill fw-bold px-3 text-nowrap w-100 w-md-auto" onclick="toggleEditTenant('{{ $tenant->id }}')" id="btn-edit-tenant-{{ $tenant->id }}">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Nama
                                        </button>
                                        
                                        <form action="{{ route('tenants.destroy', $tenant->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('BAHAYA: Yakin ingin menghapus perusahaan VIP ini? Seluruh data user dan transaksi di dalamnya akan ikut terhapus permanen!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-3 text-nowrap w-100 w-md-auto">
                                                <i class="fa-solid fa-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>

                                    <form id="form-edit-tenant-{{ $tenant->id }}" action="{{ route('tenants.update', $tenant->id) }}" method="POST" class="d-none flex-column flex-md-row align-items-stretch align-items-md-center justify-content-md-end gap-2 mt-2 mt-md-0" onsubmit="return confirm('Simpan perubahan nama perusahaan?')">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control form-control-sm bg-black text-white border-emerald-900" value="{{ $tenant->name }}" required>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2" onclick="toggleEditTenant('{{ $tenant->id }}')"><i class="fa-solid fa-times"></i></button>
                                            <button type="submit" class="btn btn-success btn-sm rounded-pill fw-bold px-3 text-nowrap">
                                                <i class="fa-solid fa-save me-1"></i> Simpan
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    @if($activeSub)
                                        <div class="d-flex flex-column align-items-stretch align-items-md-end gap-2">
                                            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-md-end gap-2" id="action-btns-{{ $tenant->id }}">
                                            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill fw-bold px-3 text-nowrap w-100 w-md-auto" onclick="toggleEdit('{{ $tenant->id }}')" id="btn-edit-{{ $tenant->id }}">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Paket
                                            </button>
                                            
                                            <form id="form-deactivate-{{ $tenant->id }}" action="{{ route('tenants.deactivate-subscription', $tenant->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Yakin ingin menonaktifkan langganan perusahaan ini secara paksa?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-3 text-nowrap w-100 w-md-auto">
                                                    <i class="fa-solid fa-power-off me-1"></i> Nonaktifkan
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <form id="form-sub-{{ $tenant->id }}" action="{{ route('tenants.activate-subscription', $tenant->id) }}" method="POST" class="d-none flex-column flex-md-row align-items-stretch align-items-md-center justify-content-md-end gap-2 mt-2 mt-md-0" onsubmit="return confirm('Simpan perubahan paket untuk perusahaan ini?')">
                                                @csrf
                                                <input type="date" name="custom_end_date" class="form-control form-control-sm bg-black text-white border-emerald-900" value="{{ $activeSub->end_date->format('Y-m-d') }}" title="Edit Tanggal Berakhir">
                                                <select name="plan_id" class="form-select form-select-sm bg-black text-white border-emerald-900" onchange="updateEndDate(this)">
                                                    @foreach($plans as $plan)
                                                        <option value="{{ $plan->id }}" data-duration="{{ $plan->duration_months }}" {{ ($activeSub->plan_id == $plan->id) ? 'selected' : '' }}>{{ $plan->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2" onclick="toggleEdit('{{ $tenant->id }}')"><i class="fa-solid fa-times"></i></button>
                                                    <button type="submit" class="btn btn-success btn-sm rounded-pill fw-bold px-3 text-nowrap">
                                                        <i class="fa-solid fa-save me-1"></i> Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @else
                                        <form id="form-sub-{{ $tenant->id }}" action="{{ route('tenants.activate-subscription', $tenant->id) }}" method="POST" class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-md-end gap-2" onsubmit="return confirm('Aktifkan langganan untuk perusahaan ini?')">
                                            @csrf
                                            <select name="plan_id" class="form-select form-select-sm bg-black text-white border-emerald-900" onchange="updateEndDate(this)">
                                                @foreach($plans as $plan)
                                                    <option value="{{ $plan->id }}" data-duration="{{ $plan->duration_months }}">{{ $plan->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary-custom btn-sm rounded-pill fw-bold px-3 text-nowrap">
                                                <i class="fa-solid fa-bolt me-1"></i> Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($tenants->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-building-circle-xmark fs-2 mb-3 opacity-50"></i>
                                <p class="mb-0">Belum ada klien yang terdaftar.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function toggleEdit(id) {
        const actionBtns = document.getElementById('action-btns-' + id);
        const form = document.getElementById('form-sub-' + id);
        
        if(form.classList.contains('d-none')) {
            form.classList.remove('d-none');
            form.classList.add('d-flex');
            actionBtns.classList.add('d-none');
            actionBtns.classList.remove('d-flex');
        } else {
            form.classList.add('d-none');
            form.classList.remove('d-flex');
            actionBtns.classList.remove('d-none');
            actionBtns.classList.add('d-flex');
        }
    }

    function toggleEditTenant(id) {
        const actionBtns = document.getElementById('action-btns-' + id);
        const form = document.getElementById('form-edit-tenant-' + id);
        
        if(form.classList.contains('d-none')) {
            form.classList.remove('d-none');
            form.classList.add('d-flex');
            actionBtns.classList.add('d-none');
            actionBtns.classList.remove('d-flex');
        } else {
            form.classList.add('d-none');
            form.classList.remove('d-flex');
            actionBtns.classList.remove('d-none');
            actionBtns.classList.add('d-flex');
        }
    }

    function updateEndDate(selectElement) {
        const duration = parseInt(selectElement.options[selectElement.selectedIndex].getAttribute('data-duration'));
        if (isNaN(duration)) return;

        // Find the date input in the same form
        const form = selectElement.closest('form');
        const dateInput = form.querySelector('input[type="date"]');
        
        if (dateInput) {
            const date = new Date();
            date.setMonth(date.getMonth() + duration);
            
            // Format to YYYY-MM-DD
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            
            dateInput.value = `${yyyy}-${mm}-${dd}`;
            
            // Add a brief highlight effect to indicate it changed
            dateInput.style.transition = 'box-shadow 0.3s';
            dateInput.style.boxShadow = '0 0 10px #10b981';
            setTimeout(() => { dateInput.style.boxShadow = 'none'; }, 500);
        }
    }
</script>
@endsection
@endsection
