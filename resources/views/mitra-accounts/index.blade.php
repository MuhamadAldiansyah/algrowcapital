@extends('layouts.app')

@section('title', 'Manajemen Akun Mitra')

@section('content')
<style>
    .editable-cell {
        cursor: pointer;
        position: relative;
        padding-right: 20px !important;
        transition: background 0.3s;
        min-width: 100px;
    }
    .editable-cell:hover {
        background: rgba(16, 185, 129, 0.1) !important;
    }
    .editable-cell::after {
        content: '\f304';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.7rem;
        color: #10b981;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .editable-cell:hover::after {
        opacity: 1;
    }
    .editing-input {
        background: #05160c !important;
        color: #ffffff !important;
        border: 1px solid #10b981 !important;
        border-radius: 4px;
        padding: 2px 5px;
        width: 100%;
        box-sizing: border-box;
        font-size: inherit;
        font-family: inherit;
        outline: none;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    /* Mencegah pergeseran kolom saat edit */
    table {
        table-layout: fixed;
        width: 100%;
    }
    th, td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    th:nth-child(1), td:nth-child(1) { width: 100px; } /* Platform */
    th:nth-child(2), td:nth-child(2) { width: 180px; } /* Owner */
    th:nth-child(3), td:nth-child(3) { width: 150px; } /* Username */
    th:nth-child(4), td:nth-child(4) { width: 120px; } /* Password */
    th:nth-child(5), td:nth-child(5) { width: 80px; }  /* PIN */
    th:nth-child(6), td:nth-child(6) { width: 100px; } /* Bank */
    th:nth-child(7), td:nth-child(7) { width: 150px; } /* RDN */
    th:nth-child(8), td:nth-child(8) { width: 150px; } /* Device */
    th:nth-child(9), td:nth-child(9) { width: 120px; } /* Aksi */

    @media (max-width: 1200px) {
        table { table-layout: auto; }
        th, td { white-space: nowrap; }
    }
    .btn-status-filter {
        background: transparent;
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
    }
    .btn-status-filter:hover {
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.5);
    }
    .btn-status-filter.active {
        background: #10b981 !important;
        color: #05160c !important;
        border-color: #10b981 !important;
        font-weight: bold;
    }
</style>

<div class="card stat-node border-0 shadow-lg">
    <div class="card-header bg-black bg-opacity-20 pt-4 pb-3 border-bottom border-emerald-900 mb-3 px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h5 class="fw-bold mb-0 text-white text-center text-md-start"><i class="fa-solid fa-id-card me-2 text-emerald-500"></i>DAFTAR AKUN MITRA</h5>
            
            <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
                <a href="{{ route('mitra-accounts.export') }}" class="btn btn-outline-primary-custom border-white border-opacity-50 px-3 rounded-pill w-100 w-sm-auto text-center mb-0">
                    <i class="fa-solid fa-file-excel me-1"></i> EKSPORT EXCEL
                </a>
                <button type="button" class="btn btn-outline-primary-custom border-white border-opacity-50 px-3 rounded-pill w-100 w-sm-auto text-center mb-0" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> IMPORT CSV
                </button>
                <a href="{{ route('mitra-accounts.create') }}" class="btn btn-primary-custom px-3 rounded-pill w-100 w-sm-auto text-center mb-0 fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> TAMBAH AKUN
                </a>
            </div>
        </div>
        
        <!-- Status Filter Tabs -->
        <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
            <button class="btn btn-sm px-3 rounded-pill btn-status-filter active flex-grow-1 flex-md-grow-0" data-status="semua">
                <i class="fa-solid fa-list me-1"></i> SEMUA ({{ $counts['semua'] }})
            </button>
            <button class="btn btn-sm px-3 rounded-pill btn-status-filter flex-grow-1 flex-md-grow-0" data-status="aktif">
                <i class="fa-solid fa-circle-check me-1"></i> AKTIF ({{ $counts['aktif'] }})
            </button>
            <button class="btn btn-sm px-3 rounded-pill btn-status-filter flex-grow-1 flex-md-grow-0" data-status="nonaktif">
                <i class="fa-solid fa-circle-xmark me-1"></i> NONAKTIF ({{ $counts['nonaktif'] }})
            </button>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="mb-4 mt-2 table-responsive w-100 border-0">
            <table class="table align-middle" id="mitraTable" style="width: 100%; min-width: 1050px;">
                <thead>
                    <tr>
                        <th>PLATFORM</th>
                        <th>OWNER</th>
                        <th>USERNAME</th>
                        <th>PASSWORD</th>
                        <th>PIN</th>
                        <th>BANK RDN</th>
                        <th>NO. REKENING</th>
                        <th>DEVICE</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data diload via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content stat-node border-0 shadow-lg rounded-4 w-100" style="background: #05160c !important;">
            <form action="{{ route('mitra-accounts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom border-emerald-900 pb-3 p-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-file-csv me-2 text-emerald-500"></i>IMPORT AKUN VIA CSV / EXCEL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if(Auth::user()->role === 'developer')
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">PERUSAHAAN (TENANT) TUJUAN</label>
                        <select name="tenant_id" class="form-select bg-black text-white border-emerald-900">
                            <option value="">-- Tanpa Perusahaan (Developer/Independen) --</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label text-emerald-500 small fw-bold">FILE CSV / EXCEL</label>
                        <input type="file" name="file" class="form-control bg-black bg-opacity-20 text-white border-emerald-900" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <small class="text-white-50 mt-2 d-block">Format kolom yang didukung: <strong>ID, PLATFORM, Nama Pemilik, Username, password, PIN, BANK RDN, Rekening RDN, Status, Device, HANDLER</strong></small>
                </div>
                <div class="modal-footer border-top border-emerald-900 pt-4 p-4 d-flex flex-column flex-sm-row justify-content-between gap-3">
                    <a href="{{ route('mitra-accounts.template') }}" class="btn btn-outline-info px-4 rounded-pill w-100 w-sm-auto text-center order-3 order-sm-1">
                        <i class="fa-solid fa-download me-1"></i> DOWNLOAD TEMPLATE EXCEL
                    </a>
                    <div class="d-flex flex-column-reverse flex-sm-row gap-2 w-100 w-sm-auto order-1 order-sm-2">
                        <button type="button" class="btn btn-outline-secondary px-4 rounded-pill w-100 w-sm-auto mb-0" data-bs-dismiss="modal">BATAL</button>
                        <button type="submit" class="btn btn-primary-custom border-white border-opacity-50 px-4 rounded-pill w-100 w-sm-auto fw-bold">MULAI IMPORT</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Inject CSS to hide scrollbar but keep functionality, and add spacing above pagination
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .dataTables_scrollBody::-webkit-scrollbar { display: none; }
            .dataTables_scrollBody { -ms-overflow-style: none; scrollbar-width: none; }
            .dataTables_info, .dataTables_paginate { margin-top: 1.5rem !important; }
        `)
        .appendTo('head');

    let currentStatus = 'semua';

    // Initialize DataTables
    const table = $('#mitraTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('mitra-accounts.index') }}",
            type: 'GET',
            data: function (d) {
                d.status = currentStatus;
            }
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
            search: "_INPUT_",
            searchPlaceholder: "Cari data akun..."
        },
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        order: [[1, 'asc']], // Default sort by Owner
        // removed scrollX: true to let native bootstrap .table-responsive handle it perfectly
        columnDefs: [
            { targets: [8], orderable: false } // Disable sorting on Action column
        ]
    });

    // Custom Status Filter Logic
    $('.btn-status-filter').on('click', function(e) {
        e.preventDefault();
        $('.btn-status-filter').removeClass('active');
        $(this).addClass('active');
        
        currentStatus = $(this).data('status');
        table.ajax.reload(); // Reload table data silently
    });

    // Event Delegation for Inline Editing
    $(document).on('dblclick', '.editable-cell', function() {
        const cell = $(this);
        if (cell.find('input').length > 0) return;

        const currentText = cell.text().trim() === '-' ? '' : cell.text().trim();
        const id = cell.data('id');
        const field = cell.data('field');

        const input = $('<input>', {
            type: 'text',
            class: 'form-control form-control-sm bg-black bg-opacity-50 text-white border-emerald-500',
            value: currentText
        });

        cell.html(input);
        input.focus();

        input.on('blur keypress', function(e) {
            if (e.type === 'keypress' && e.which !== 13) return;
            
            const newValue = $(this).val().trim();
            
            if (newValue === currentText || newValue === '') {
                cell.html(currentText === '' ? '-' : currentText);
                return;
            }

            if (newValue !== currentText) {
                cell.html('<i class="fa-solid fa-spinner fa-spin text-emerald-500"></i>');

                $.ajax({
                    url: `/mitra-accounts/${id}/update-field`,
                    method: 'PATCH',
                    data: {
                        field: field,
                        value: newValue,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        cell.html(newValue);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Data diperbarui',
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#05160c',
                            color: '#ffffff'
                        });
                    },
                    error: function() {
                        cell.html(currentText === '' ? '-' : currentText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal memperbarui data',
                            background: '#05160c',
                            color: '#ffffff'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection
@endsection
