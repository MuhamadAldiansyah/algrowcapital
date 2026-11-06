@extends('layouts.app')

@section('title', 'WhatsApp Blast Manager')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .card-header-mobile {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.5rem !important;
        }
        .card-header-mobile .badge {
            align-self: flex-start;
        }
        .action-btns-mobile {
            width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        .action-btns-mobile .btn {
            width: 100% !important;
        }
        .recipient-tabs-mobile {
            flex-direction: column !important;
            gap: 0.25rem !important;
        }
        .recipient-tabs-mobile .nav-item {
            width: 100% !important;
        }
        .recipient-tabs-mobile .nav-link {
            text-align: left !important;
            padding: 0.6rem 0.85rem !important;
            font-size: 0.82rem !important;
        }
    }
</style>
<div class="container-fluid px-0">

    <!-- CLOUD HOSTING (VERCEL) BANNER ALERT -->
    <div id="vercelAlertBox" class="alert alert-warning border-warning border-opacity-40 bg-black bg-opacity-60 text-white p-3 mb-4 rounded-3 d-none shadow">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h6 class="text-warning fw-bold mb-1">
                    <i class="fa-solid fa-cloud-bolt me-2"></i> Aplikasi Berjalan di Vercel Cloud (Online)
                </h6>
                <p class="small opacity-80 mb-0">
                    Karena website ini diakses melalui cloud Vercel, server Vercel memerlukan <strong>URL Tunnel</strong> untuk dapat berkomunikasi dengan engine WhatsApp di laptop Anda (Drive D:).
                </p>
            </div>
            <button type="button" class="btn btn-sm btn-warning text-black fw-bold px-3 py-2 text-nowrap" onclick="openGatewayModal()">
                <i class="fa-solid fa-network-wired me-1"></i> Masukkan URL Tunnel
            </button>
        </div>
    </div>

    <!-- TOP STATUS ROW -->
    <div class="row g-3 mb-4">
        <!-- CONNECTION CARD -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg" style="background: rgba(5, 22, 12, 0.85); border: 1px solid rgba(16, 185, 129, 0.25) !important;">
                <div class="card-header bg-black bg-opacity-30 d-flex justify-content-between align-items-center py-3 card-header-mobile">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-400 fs-4"></i>
                        <span class="fw-bold text-white letter-spacing-1">STATUS AKUN PENGIRIM (SENDER)</span>
                    </div>
                    <div id="connectionStatusBadge">
                        <span class="badge bg-secondary bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-2">
                            <i class="fa-solid fa-spinner fa-spin me-1"></i> Memeriksa Sesi...
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- GATEWAY CONNECTION INFO STRIP -->
                    <div class="p-3 mb-4 rounded-3 bg-black bg-opacity-40 border border-emerald-900 border-opacity-50">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div>
                                <span class="small text-white opacity-50 text-uppercase fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Target Gateway / Service:</span>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-emerald-950 text-emerald-300 border border-emerald-500 border-opacity-40 small" id="gatewayTypeBadge">
                                        GATEWAY
                                    </span>
                                    <code class="text-emerald-400 small ticker-font" id="activeGatewayText">{{ $currentGatewayUrl }}</code>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary-custom px-3 py-1" onclick="openGatewayModal()">
                                    <i class="fa-solid fa-sliders me-1"></i> Ubah Gateway
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-emerald-400 fw-bold text-uppercase">Nomor WhatsApp Pengirim</label>
                            <div class="input-group">
                                <span class="input-group-text bg-black bg-opacity-50 text-emerald-400 border-emerald-900 border-opacity-50">
                                    <i class="fa-solid fa-phone"></i>
                                </span>
                                <input type="text" id="senderPhone" class="form-control text-white ticker-font" value="087822421207" placeholder="Contoh: 087822421207">
                            </div>
                            <small class="text-white opacity-50 mt-1 d-block">
                                Nomor yang akan digunakan untuk mengirim pesan blast.
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end action-btns-mobile" id="actionButtonsGroup">
                                <button type="button" class="btn btn-primary-custom px-3 py-2 flex-fill flex-md-grow-0" id="btnPairCode" onclick="requestPairingCode()">
                                    <i class="fa-solid fa-key me-1"></i> Hubungkan (Pairing Code)
                                </button>
                                <button type="button" class="btn btn-outline-primary-custom px-3 py-2 flex-fill flex-md-grow-0" id="btnQrCode" onclick="requestQrCode()">
                                    <i class="fa-solid fa-qrcode me-1"></i> Scan QR
                                </button>
                                <button type="button" class="btn btn-outline-danger px-3 py-2 flex-fill flex-md-grow-0 d-none" id="btnLogout" onclick="disconnectWhatsApp()">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i> Putuskan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PAIRING CODE DISPLAY BOX -->
                    <div id="pairingCodeBox" class="mt-4 p-3 rounded-3 bg-black bg-opacity-50 border border-emerald-500 border-opacity-30 d-none">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            <div>
                                <h6 class="text-emerald-400 fw-bold mb-1">
                                    <i class="fa-solid fa-mobile-screen-button me-2"></i> KODE TAUTAN WHATSAPP:
                                </h6>
                                <p class="small text-white opacity-75 mb-0">
                                    Buka WA di HP Anda &rarr; <strong>Perangkat Tertaut</strong> &rarr; <strong>Tautkan Perangkat</strong> &rarr; <strong>Tautkan dengan nomor telepon</strong>, lalu masukkan kode berikut:
                                </p>
                            </div>
                            <div class="text-center">
                                <div class="display-6 fw-bold text-emerald-300 ticker-font bg-emerald-950 bg-opacity-50 px-4 py-2 rounded-2 border border-emerald-500 border-opacity-40" id="pairingCodeDisplay">
                                    ----
                                </div>
                                <button class="btn btn-sm btn-link text-emerald-400 text-decoration-none mt-1" onclick="copyPairingCode()">
                                    <i class="fa-regular fa-copy me-1"></i> Salin Kode
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- QR CODE DISPLAY MODAL / CONTAINER -->
                    <div id="qrCodeBox" class="mt-4 p-3 rounded-3 bg-black bg-opacity-50 border border-emerald-500 border-opacity-30 text-center d-none">
                        <h6 class="text-emerald-400 fw-bold mb-2">Pindai QR Code Menggunakan WhatsApp di HP Anda</h6>
                        <div class="p-2 bg-white d-inline-block rounded-3 shadow">
                            <img id="qrCodeImage" src="" alt="WhatsApp QR Code" style="width: 220px; height: 220px;">
                        </div>
                        <p class="small text-white opacity-50 mt-2 mb-0">QR akan diperbarui otomatis jika kedaluwarsa.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ENGINE / STORAGE INFO CARD -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-lg" style="background: rgba(5, 22, 12, 0.85); border: 1px solid rgba(16, 185, 129, 0.25) !important;">
                <div class="card-header bg-black bg-opacity-30 py-3">
                    <span class="fw-bold text-white letter-spacing-1">
                        <i class="fa-solid fa-shield-halved text-emerald-400 me-2"></i> INFORMASI SISTEM & DRIVE D:
                    </span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-white opacity-75">Engine WhatsApp:</span>
                            <span class="text-emerald-400 fw-bold">Baileys v6 (Lightweight)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-white opacity-75">Lokasi File Sesi:</span>
                            <span class="text-white ticker-font">D:\...\wa_service\session</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-white opacity-75">Anti-Ban Protection:</span>
                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30">AKTIF (3 Lapisan)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 small">
                            <span class="text-white opacity-75">Service Status:</span>
                            <span id="serviceStatusPill" class="text-emerald-400 fw-bold">Checking...</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-2 bg-black bg-opacity-40 border border-emerald-900 border-opacity-40">
                        <div class="small text-white opacity-75">
                            <i class="fa-solid fa-circle-check text-emerald-400 me-1"></i> <strong>Aman di Drive D:</strong> Seluruh modul, file sesi, dan cache terisolasi di partisi D:, menjaga Drive C Anda tetap bersih dan lega.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN BLAST SECTION -->
    <div class="row g-4">
        <!-- LEFT: RECIPIENT SOURCE (DAFTAR PENERIMA) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg h-100" style="background: rgba(5, 22, 12, 0.85); border: 1px solid rgba(16, 185, 129, 0.25) !important;">
                <div class="card-header bg-black bg-opacity-30 d-flex justify-content-between align-items-center py-3 card-header-mobile">
                    <span class="fw-bold text-white letter-spacing-1">
                        <i class="fa-solid fa-users text-emerald-400 me-2"></i> 1. PILIH DAFTAR PENERIMA
                    </span>
                    <span class="badge bg-emerald-950 text-emerald-300 border border-emerald-500 border-opacity-40" id="recipientCountBadge">
                        0 Penerima Terpilih
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- Nav Tabs for Source -->
                    <ul class="nav nav-pills mb-3 p-1 rounded-3 bg-black bg-opacity-40 border border-emerald-900 border-opacity-30 recipient-tabs-mobile" id="recipientTabs">
                        <li class="nav-item flex-fill text-center">
                            <button class="nav-link active w-100 py-2 small fw-bold" id="tab-db" data-bs-toggle="pill" data-bs-target="#content-db" type="button">
                                <i class="fa-solid fa-database me-1"></i> Database Mitra & User ({{ $usersWithPhone->count() }})
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center">
                            <button class="nav-link w-100 py-2 small fw-bold" id="tab-manual" data-bs-toggle="pill" data-bs-target="#content-manual" type="button">
                                <i class="fa-solid fa-keyboard me-1"></i> Input / Paste
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center">
                            <button class="nav-link w-100 py-2 small fw-bold" id="tab-file" data-bs-toggle="pill" data-bs-target="#content-file" type="button">
                                <i class="fa-solid fa-file-excel me-1"></i> Upload File
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2">
                        <!-- TAB 1: DATABASE -->
                        <div class="tab-pane fade show active" id="content-db">
                            <!-- SEARCH & FILTER TOOLBAR FOR MOBILE & DESKTOP -->
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-black bg-opacity-50 text-emerald-400 border-emerald-900 border-opacity-50">
                                        <i class="fa-solid fa-search"></i>
                                    </span>
                                    <input type="text" id="dbRecipientSearch" class="form-control text-white border-emerald-900 border-opacity-50 bg-black bg-opacity-40" 
                                           placeholder="Cari nama atau no. telepon..." onkeyup="filterRecipientList(this.value)">
                                    <button class="btn btn-outline-secondary border-emerald-900 border-opacity-50 text-white opacity-75" type="button" onclick="document.getElementById('dbRecipientSearch').value=''; filterRecipientList('')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAllDb" onchange="toggleSelectAllDb(this)">
                                    <label class="form-check-label text-white small" for="checkAllDb">Pilih Semua ({{ $usersWithPhone->count() }})</label>
                                </div>
                                <span class="small text-white opacity-50" style="font-size: 0.75rem;">Hanya nomor valid &ge; 8 digit</span>
                            </div>
                            <div class="table-responsive rounded-2 border border-emerald-900 border-opacity-40" style="max-height: 340px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="sticky-top bg-black">
                                        <tr>
                                            <th style="width: 38px;" class="text-center">#</th>
                                            <th>NAMA</th>
                                            <th class="d-none d-sm-table-cell">NO. WHATSAPP</th>
                                            <th class="text-end text-sm-start">ROLE</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recipientTableBody">
                                        @forelse($usersWithPhone as $u)
                                        <tr class="recipient-row">
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input db-recipient-check" 
                                                       value="{{ $u->phone }}" 
                                                       data-name="{{ $u->name }}" 
                                                       onchange="recalculateRecipients()">
                                            </td>
                                            <td>
                                                <div class="text-white fw-bold text-truncate" style="max-width: 200px;">{{ $u->name }}</div>
                                                <div class="d-sm-none text-emerald-400 ticker-font small mt-1">
                                                    <i class="fa-brands fa-whatsapp me-1"></i>{{ $u->phone }}
                                                </div>
                                            </td>
                                            <td class="ticker-font text-emerald-400 d-none d-sm-table-cell">{{ $u->phone }}</td>
                                            <td class="text-end text-sm-start">
                                                <span class="badge bg-black bg-opacity-50 text-emerald-300 border border-emerald-900 border-opacity-40 small" style="font-size: 0.7rem;">
                                                    {{ strtoupper($u->role ?? 'MITRA') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-white opacity-50">
                                                <i class="fa-solid fa-address-book fs-3 d-block mb-2"></i>
                                                Belum ada kontak dengan no telepon di database.<br>
                                                <span class="small">Silakan gunakan tab <strong>Input / Paste</strong> atau <strong>Upload File</strong> di atas.</span>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: MANUAL INPUT / PASTE -->
                        <div class="tab-pane fade" id="content-manual">
                            <label class="form-label small text-emerald-400 fw-bold">Tempel Nomor & Nama (Satu per baris):</label>
                            <textarea id="manualRecipientsInput" class="form-control text-white ticker-font" rows="8" 
                                      placeholder="Contoh format:&#10;087822421207, Budi Santoso&#10;081234567890, Siti Rahma&#10;085712345678"
                                      oninput="recalculateRecipients()"></textarea>
                            <small class="text-white opacity-50 mt-2 d-block">
                                Format: <code>NomorTelepon, Nama</code> atau cukup <code>NomorTelepon</code> saja.
                            </small>
                        </div>

                        <!-- TAB 3: UPLOAD FILE -->
                        <div class="tab-pane fade" id="content-file">
                            <div class="p-4 border border-dashed border-emerald-500 border-opacity-40 rounded-3 text-center bg-black bg-opacity-30">
                                <i class="fa-solid fa-cloud-arrow-up text-emerald-400 fs-1 mb-2"></i>
                                <h6 class="text-white fw-bold mb-1">Unggah Dokumen Kontak</h6>
                                <p class="small text-white opacity-50 mb-3">Mendukung format .xlsx, .xls, .csv, atau .txt</p>
                                <input type="file" id="fileContactInput" class="d-none" accept=".xlsx,.xls,.csv,.txt" onchange="uploadContactFile(this)">
                                <button type="button" class="btn btn-outline-primary-custom px-4" onclick="document.getElementById('fileContactInput').click()">
                                    <i class="fa-solid fa-folder-open me-2"></i> Pilih File dari Komputer
                                </button>
                            </div>
                            <div id="fileUploadResult" class="mt-3 d-none">
                                <div class="alert alert-success d-flex align-items-center mb-0 py-2">
                                    <i class="fa-solid fa-check-circle me-2"></i>
                                    <span id="fileUploadResultText" class="small"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: TEMPLATE PESAN & PENGATURAN ANTI-BAN -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg h-100" style="background: rgba(5, 22, 12, 0.85); border: 1px solid rgba(16, 185, 129, 0.25) !important;">
                <div class="card-header bg-black bg-opacity-30 py-3">
                    <span class="fw-bold text-white letter-spacing-1">
                        <i class="fa-solid fa-envelope-open-text text-emerald-400 me-2"></i> 2. TEMPLATE PESAN & ANTI-BAN
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- TAG CHIPS -->
                    <div class="mb-2">
                        <label class="form-label small text-emerald-400 fw-bold mb-1">Sisipkan Tag Dinamis:</label>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-emerald-300 border-emerald-800" onclick="insertTag('{nama}')">
                                + {nama}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-emerald-300 border-emerald-800" onclick="insertTag('{phone}')">
                                + {phone}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-emerald-300 border-emerald-800" onclick="insertTag('{tanggal}')">
                                + {tanggal}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-emerald-300 border-emerald-800" onclick="insertTag('{jam}')">
                                + {jam}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-warning border-warning" onclick="insertTag('{Halo|Hai|Selamat siang}')" title="Pilihan kata acak">
                                + Spintax {Halo|Hai}
                            </button>
                        </div>
                    </div>

                    <!-- MESSAGE TEXTAREA -->
                    <div class="mb-3">
                        <textarea id="messageTemplate" class="form-control text-white" rows="6" placeholder="{Halo|Hai|Selamat siang} {nama},&#10;&#10;Kami menginformasikan update terbaru dari Algrow Capital...">{Halo|Hai|Selamat siang} {nama},

Terima kasih telah bergabung bersama Algrow Capital. Portofolio & transaksi Anda dapat dipantau langsung melalui dashboard kami.

Salam sukses,
Tim Algrow Capital</textarea>
                    </div>

                    <!-- ANTI-BAN SETTINGS ACCORDION / COLLAPSIBLE -->
                    <div class="p-3 rounded-3 bg-black bg-opacity-40 border border-emerald-900 border-opacity-40 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white small fw-bold">
                                <i class="fa-solid fa-sliders text-emerald-400 me-1"></i> Pengaturan Jeda Anti-Ban
                            </span>
                            <span class="badge bg-emerald-950 text-emerald-400 border border-emerald-800 small">Rekomendasi Aman</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small text-white opacity-75">Jeda Minimum (detik)</label>
                                <input type="number" id="minDelay" class="form-control form-control-sm text-white ticker-font" value="10" min="5" max="60">
                            </div>
                            <div class="col-6">
                                <label class="small text-white opacity-75">Jeda Maksimum (detik)</label>
                                <input type="number" id="maxDelay" class="form-control form-control-sm text-white ticker-font" value="25" min="10" max="120">
                            </div>
                            <div class="col-6 mt-2">
                                <label class="small text-white opacity-75">Istirahat Tiap (pesan)</label>
                                <input type="number" id="batchSize" class="form-control form-control-sm text-white ticker-font" value="15" min="5" max="100">
                            </div>
                            <div class="col-6 mt-2">
                                <label class="small text-white opacity-75">Lama Istirahat (detik)</label>
                                <input type="number" id="batchDelay" class="form-control form-control-sm text-white ticker-font" value="60" min="10" max="300">
                            </div>
                        </div>
                    </div>

                    <!-- TEST SEND ROW -->
                    <div class="row g-2 align-items-center mb-4">
                        <div class="col-12 col-sm-6">
                            <input type="text" id="testPhoneInput" class="form-control form-control-sm text-white ticker-font" value="087822421207" placeholder="Nomor Uji Coba">
                        </div>
                        <div class="col-12 col-sm-6">
                            <button type="button" class="btn btn-sm btn-outline-primary-custom w-100 py-2" onclick="sendTestMessage()">
                                <i class="fa-solid fa-paper-plane me-1"></i> Uji Coba 1 Pesan
                            </button>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button type="button" class="btn btn-primary-custom flex-grow-1 py-3 fw-bold fs-6" id="btnStartBlast" onclick="startBlastProcess()">
                            <i class="fa-solid fa-rocket me-2"></i> MULAI WHATSAPP BLAST
                        </button>
                        <button type="button" class="btn btn-danger py-3 px-4 fw-bold d-none" id="btnStopBlast" onclick="stopBlastProcess()">
                            <i class="fa-solid fa-stop me-2"></i> Hentikan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LIVE MONITOR & LOGS PANEL -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: rgba(5, 22, 12, 0.85); border: 1px solid rgba(16, 185, 129, 0.25) !important;">
                <div class="card-header bg-black bg-opacity-30 d-flex justify-content-between align-items-center py-3 card-header-mobile">
                    <span class="fw-bold text-white letter-spacing-1">
                        <i class="fa-solid fa-terminal text-emerald-400 me-2"></i> PROGRESS & LOG MONITOR PENGIRIMAN
                    </span>
                    <div id="blastStatusIndicator">
                        <span class="badge bg-black bg-opacity-40 text-white opacity-50 border border-white border-opacity-10 px-3 py-2">
                            Standby (Siap Memulai)
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- PROGRESS BAR -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-white mb-1">
                            <span id="progressTargetText">Menunggu proses...</span>
                            <span id="progressPercentText" class="ticker-font fw-bold text-emerald-400">0%</span>
                        </div>
                        <div class="progress" style="height: 12px; background-color: rgba(0,0,0,0.5); border: 1px solid rgba(16, 185, 129, 0.2);">
                            <div class="progress-bar bg-emerald-500 progress-bar-striped progress-bar-animated" id="progressBarEl" style="width: 0%;"></div>
                        </div>
                    </div>

                    <!-- STATS BADGES (RESPONSIVE 2X2 GRID ON MOBILE) -->
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded-2 bg-black bg-opacity-40 border border-white border-opacity-10">
                                <span class="small text-white opacity-50 d-block" style="font-size: 0.75rem;">Total Target</span>
                                <span class="fs-5 fw-bold text-white ticker-font" id="statTotal">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded-2 bg-black bg-opacity-40 border border-emerald-900 border-opacity-40">
                                <span class="small text-emerald-400 opacity-75 d-block" style="font-size: 0.75rem;">Berhasil Terkirim</span>
                                <span class="fs-5 fw-bold text-emerald-400 ticker-font" id="statSent">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded-2 bg-black bg-opacity-40 border border-danger border-opacity-30">
                                <span class="small text-danger opacity-75 d-block" style="font-size: 0.75rem;">Gagal</span>
                                <span class="fs-5 fw-bold text-danger ticker-font" id="statFailed">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 rounded-2 bg-black bg-opacity-40 border border-warning border-opacity-30">
                                <span class="small text-warning opacity-75 d-block" style="font-size: 0.75rem;">Sisa Antrean</span>
                                <span class="fs-5 fw-bold text-warning ticker-font" id="statRemaining">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- TERMINAL LOGS -->
                    <div class="p-3 rounded-3 bg-black border border-emerald-900 border-opacity-40" style="max-height: 250px; overflow-y: auto;" id="terminalLogContainer">
                        <div class="ticker-font text-white opacity-50 small" id="logPlaceholder">
                            [System] Log pengiriman WhatsApp akan tampil di sini saat blast dimulai...
                        </div>
                        <div id="logEntries"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PENGATURAN GATEWAY URL -->
<div class="modal fade" id="gatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #05160c; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 1rem;">
            <div class="modal-header border-bottom border-emerald-900 pb-3">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fa-solid fa-network-wired text-emerald-400 me-2"></i> Pengaturan WA Gateway
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-white opacity-75 mb-3">
                    Jika website diakses secara online (misal melalui <strong>Vercel</strong>), masukkan URL Cloudflare Tunnel publik yang dibuat oleh <code>start_wa.bat</code> di laptop Anda:
                </p>

                <label class="form-label small text-emerald-400 fw-bold">URL WA GATEWAY</label>
                <div class="input-group mb-3">
                    <span class="input-group-text bg-black bg-opacity-50 text-emerald-400 border-emerald-900">
                        <i class="fa-solid fa-link"></i>
                    </span>
                    <input type="url" id="customGatewayInput" class="form-control text-white ticker-font" 
                           placeholder="https://contoh-nama.trycloudflare.com" value="{{ $currentGatewayUrl }}">
                </div>

                <div class="p-3 rounded-2 bg-black bg-opacity-50 border border-emerald-900 mb-3 small text-white opacity-75">
                    <i class="fa-solid fa-circle-info text-emerald-400 me-1"></i> <strong>Cara Mendapatkan URL:</strong><br>
                    Jalankan <code>D:\xampp\htdocs\aplikasi_ipo\wa_service\start_wa.bat</code> di laptop Anda. Salin URL hijau yang diawali <code>https://...trycloudflare.com</code> dan tempel di atas.
                </div>
            </div>
            <div class="modal-footer border-top border-emerald-900 pt-3">
                <button type="button" class="btn btn-sm btn-outline-secondary text-white" onclick="resetGatewayLocal()">
                    Gunakan Localhost
                </button>
                <button type="button" class="btn btn-primary-custom px-4" onclick="saveGatewayUrl()">
                    <i class="fa-solid fa-save me-1"></i> Simpan & Hubungkan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT LOGIC -->
<script>
    let uploadedFileRecipients = [];
    let isPollingStatus = false;
    let pollInterval = null;
    let gatewayModalInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        // Cek apakah diakses dari Vercel / domain luar
        const isCloud = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
        const currentGw = "{{ $currentGatewayUrl }}";

        if (isCloud && (currentGw.includes('127.0.0.1') || currentGw.includes('localhost'))) {
            document.getElementById('vercelAlertBox')?.classList.remove('d-none');
        }

        checkStatus();
        setInterval(checkStatus, 6000);
        recalculateRecipients();
    });

    function openGatewayModal() {
        const modalEl = document.getElementById('gatewayModal');
        if (!gatewayModalInstance) {
            gatewayModalInstance = new bootstrap.Modal(modalEl);
        }
        gatewayModalInstance.show();
    }

    function saveGatewayUrl(urlToSave = null) {
        const url = urlToSave || document.getElementById('customGatewayInput').value.trim();
        if (!url) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'URL Gateway tidak boleh kosong!', background: '#05160c', color: '#fff' });
            return;
        }

        Swal.fire({
            title: 'Menyimpan Gateway...',
            allowOutsideClick: false,
            background: '#05160c',
            color: '#fff',
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('whatsapp.set-gateway') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url })
        })
        .then(res => res.json())
        .then(res => {
            Swal.close();
            if (res.success) {
                document.getElementById('activeGatewayText').innerText = res.url;
                if (gatewayModalInstance) gatewayModalInstance.hide();
                document.getElementById('vercelAlertBox')?.classList.add('d-none');
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, background: '#05160c', color: '#fff', showConfirmButton: false });
                checkStatus();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, background: '#05160c', color: '#fff' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Kesalahan', text: err.message, background: '#05160c', color: '#fff' });
        });
    }

    function resetGatewayLocal() {
        document.getElementById('customGatewayInput').value = 'http://127.0.0.1:5050';
        saveGatewayUrl('http://127.0.0.1:5050');
    }

    // 1. Status Polling
    function checkStatus() {
        fetch("{{ route('whatsapp.status') }}")
            .then(res => res.json())
            .then(res => {
                const badge = document.getElementById('connectionStatusBadge');
                const servicePill = document.getElementById('serviceStatusPill');
                const btnPair = document.getElementById('btnPairCode');
                const btnQr = document.getElementById('btnQrCode');
                const btnLogout = document.getElementById('btnLogout');
                const qrBox = document.getElementById('qrCodeBox');
                const pairBox = document.getElementById('pairingCodeBox');
                const gwBadge = document.getElementById('gatewayTypeBadge');

                const data = res.data || {};
                const activeGw = data.gateway_url || "{{ $currentGatewayUrl }}";
                document.getElementById('activeGatewayText').innerText = activeGw;

                if (activeGw.includes('trycloudflare') || activeGw.startsWith('https://')) {
                    gwBadge.innerText = 'ONLINE TUNNEL';
                    gwBadge.className = 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-40 small';
                } else {
                    gwBadge.innerText = 'LOCAL';
                    gwBadge.className = 'badge bg-secondary bg-opacity-25 text-white border border-white border-opacity-40 small';
                }

                if (!res.success && data.status === 'service_down') {
                    badge.innerHTML = `<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-40 px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Gateway Terputus</span>`;
                    servicePill.innerHTML = `<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Offline</span>`;
                    return;
                }

                servicePill.innerHTML = `<span class="text-emerald-400"><i class="fa-solid fa-circle-check"></i> Gateway Aktif</span>`;

                if (data.status === 'connected') {
                    const userPhone = data.user?.id ? data.user.id.split(':')[0] : 'Aktif';
                    badge.innerHTML = `<span class="badge bg-success bg-opacity-25 text-emerald-400 border border-success border-opacity-40 px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i> Terhubung (${userPhone})</span>`;
                    btnPair.classList.add('d-none');
                    btnQr.classList.add('d-none');
                    btnLogout.classList.remove('d-none');
                    qrBox.classList.add('d-none');
                    pairBox.classList.add('d-none');
                } else if (data.status === 'pairing_ready' && data.pairingCode) {
                    badge.innerHTML = `<span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-40 px-3 py-2"><i class="fa-solid fa-clock me-1"></i> Menunggu Konfirmasi HP</span>`;
                    pairBox.classList.remove('d-none');
                    document.getElementById('pairingCodeDisplay').innerText = data.pairingCode;
                    btnPair.classList.remove('d-none');
                    btnQr.classList.remove('d-none');
                    btnLogout.classList.add('d-none');
                } else if (data.status === 'qr_ready' && data.qr) {
                    badge.innerHTML = `<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-40 px-3 py-2"><i class="fa-solid fa-qrcode me-1"></i> Silakan Pindai QR</span>`;
                    qrBox.classList.remove('d-none');
                    document.getElementById('qrCodeImage').src = data.qr;
                    btnPair.classList.remove('d-none');
                    btnQr.classList.remove('d-none');
                    btnLogout.classList.add('d-none');
                } else {
                    badge.innerHTML = `<span class="badge bg-secondary bg-opacity-25 text-white border border-white border-opacity-20 px-3 py-2"><i class="fa-solid fa-unlink me-1"></i> Belum Terhubung</span>`;
                    btnPair.classList.remove('d-none');
                    btnQr.classList.remove('d-none');
                    btnLogout.classList.add('d-none');
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    // 2. Request Pairing Code
    function requestPairingCode() {
        const phone = document.getElementById('senderPhone').value.trim();
        if (!phone) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Nomor WhatsApp pengirim wajib diisi!', background: '#05160c', color: '#fff' });
            return;
        }

        Swal.fire({
            title: 'Meminta Pairing Code...',
            text: 'Menghubungkan ke WhatsApp Baileys via Gateway...',
            allowOutsideClick: false,
            background: '#05160c',
            color: '#fff',
            didOpen: () => { Swal.showLoading(); }
        });

        fetch("{{ route('whatsapp.pair') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ phone })
        })
        .then(res => res.json())
        .then(res => {
            Swal.close();
            if (res.success && res.pairingCode) {
                document.getElementById('pairingCodeBox').classList.remove('d-none');
                document.getElementById('pairingCodeDisplay').innerText = res.pairingCode;
                checkStatus();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Tidak ada pairing code diterima.', background: '#05160c', color: '#fff' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Kesalahan Gateway', text: err.message, background: '#05160c', color: '#fff' });
        });
    }

    // 3. Request QR Code
    function requestQrCode() {
        fetch("{{ route('whatsapp.connect-qr') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(res => {
            checkStatus();
        });
    }

    // 4. Logout Session
    function disconnectWhatsApp() {
        Swal.fire({
            title: 'Putuskan Sambungan?',
            text: 'Sesi WhatsApp pada server akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#10b981',
            confirmButtonText: 'Ya, Putuskan',
            cancelButtonText: 'Batal',
            background: '#05160c',
            color: '#fff'
        }).then(result => {
            if (result.isConfirmed) {
                fetch("{{ route('whatsapp.logout') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(res => {
                    checkStatus();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'WhatsApp telah diputuskan.', background: '#05160c', color: '#fff' });
                });
            }
        });
    }

    // 5. Salin Pairing Code
    function copyPairingCode() {
        const code = document.getElementById('pairingCodeDisplay').innerText;
        navigator.clipboard.writeText(code).then(() => {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Kode disalin ke clipboard!', timer: 2000, showConfirmButton: false });
        });
    }

    // 6. Recipient Manager & Live Search Filter
    function filterRecipientList(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.recipient-row');
        rows.forEach(tr => {
            const text = tr.innerText.toLowerCase();
            if (!q || text.includes(q)) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    }

    function toggleSelectAllDb(el) {
        document.querySelectorAll('.recipient-row').forEach(tr => {
            if (tr.style.display !== 'none') {
                const cb = tr.querySelector('.db-recipient-check');
                if (cb) cb.checked = el.checked;
            }
        });
        recalculateRecipients();
    }

    function recalculateRecipients() {
        const recipients = getSelectedRecipients();
        document.getElementById('recipientCountBadge').innerText = `${recipients.length} Penerima Terpilih`;
    }

    function getSelectedRecipients() {
        const activeTab = document.querySelector('#recipientTabs .nav-link.active')?.id;
        const recipients = [];

        if (activeTab === 'tab-db') {
            document.querySelectorAll('.db-recipient-check:checked').forEach(cb => {
                recipients.push({
                    phone: cb.value,
                    name: cb.dataset.name || 'Pelanggan'
                });
            });
        } else if (activeTab === 'tab-manual') {
            const lines = document.getElementById('manualRecipientsInput').value.split('\n');
            lines.forEach(line => {
                line = line.trim();
                if (!line) return;
                const parts = line.split(/[,;\t]/);
                const p = parts[0]?.trim();
                const n = parts[1]?.trim() || 'Pelanggan';
                if (p && p.length >= 8) {
                    recipients.push({ phone: p, name: n });
                }
            });
        } else if (activeTab === 'tab-file') {
            return uploadedFileRecipients;
        }

        return recipients;
    }

    document.querySelectorAll('#recipientTabs button').forEach(btn => {
        btn.addEventListener('shown.bs.tab', recalculateRecipients);
    });

    // 7. File Upload Handler
    function uploadContactFile(input) {
        if (!input.files || input.files.length === 0) return;
        const formData = new FormData();
        formData.append('file', input.files[0]);

        Swal.fire({
            title: 'Membaca File...',
            text: 'Mengekstrak nomor telepon dan nama...',
            allowOutsideClick: false,
            background: '#05160c',
            color: '#fff',
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('whatsapp.parse-file') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            Swal.close();
            if (res.success) {
                uploadedFileRecipients = res.recipients || [];
                document.getElementById('fileUploadResult').classList.remove('d-none');
                document.getElementById('fileUploadResultText').innerText = `Berhasil memuat ${uploadedFileRecipients.length} nomor dari file!`;
                recalculateRecipients();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, background: '#05160c', color: '#fff' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Kesalahan', text: err.message, background: '#05160c', color: '#fff' });
        });
    }

    // 8. Insert Tag
    function insertTag(tag) {
        const textarea = document.getElementById('messageTemplate');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.focus();
        textarea.selectionEnd = start + tag.length;
    }

    // 9. Send Test Message
    function sendTestMessage() {
        const phone = document.getElementById('testPhoneInput').value.trim();
        const message = document.getElementById('messageTemplate').value.trim();

        if (!phone || !message) {
            Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Nomor uji coba dan isi pesan wajib diisi.', background: '#05160c', color: '#fff' });
            return;
        }

        Swal.fire({
            title: 'Mengirim Pesan Uji Coba...',
            text: `Mengirim ke ${phone}...`,
            allowOutsideClick: false,
            background: '#05160c',
            color: '#fff',
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('whatsapp.test-send') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ phone, message })
        })
        .then(res => res.json())
        .then(res => {
            Swal.close();
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil Terkirim!', text: res.message, background: '#05160c', color: '#fff' });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, background: '#05160c', color: '#fff' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Kesalahan', text: err.message, background: '#05160c', color: '#fff' });
        });
    }

    // 10. Start Blast Process
    function startBlastProcess() {
        const recipients = getSelectedRecipients();
        const message = document.getElementById('messageTemplate').value.trim();
        const minDelay = document.getElementById('minDelay').value;
        const maxDelay = document.getElementById('maxDelay').value;
        const batchSize = document.getElementById('batchSize').value;
        const batchDelay = document.getElementById('batchDelay').value;

        if (recipients.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Penerima Kosong', text: 'Pilih minimal 1 nomor penerima terlebih dahulu!', background: '#05160c', color: '#fff' });
            return;
        }

        if (!message) {
            Swal.fire({ icon: 'warning', title: 'Pesan Kosong', text: 'Isi template pesan tidak boleh kosong.', background: '#05160c', color: '#fff' });
            return;
        }

        Swal.fire({
            title: 'Mulai Pengiriman Blast?',
            html: `Akan mengirim pesan ke <strong>${recipients.length} nomor</strong> dengan jeda anti-ban <strong>${minDelay}-${maxDelay} detik</strong>.<br><br><span class="small text-emerald-400">Pastikan perangkat Anda tetap terhubung selama proses berjalan.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Mulai Blast',
            cancelButtonText: 'Batal',
            background: '#05160c',
            color: '#fff'
        }).then(result => {
            if (result.isConfirmed) {
                fetch("{{ route('whatsapp.start') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        recipients,
                        message,
                        minDelay,
                        maxDelay,
                        batchSize,
                        batchDelay
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('btnStartBlast').classList.add('d-none');
                        document.getElementById('btnStopBlast').classList.remove('d-none');
                        startProgressMonitor();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal Memulai', text: res.message, background: '#05160c', color: '#fff' });
                    }
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Kesalahan', text: err.message, background: '#05160c', color: '#fff' });
                });
            }
        });
    }

    // 11. Stop Blast
    function stopBlastProcess() {
        Swal.fire({
            title: 'Hentikan Pengiriman?',
            text: 'Proses antrean pesan akan dihentikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Hentikan',
            cancelButtonText: 'Lanjutkan',
            background: '#05160c',
            color: '#fff'
        }).then(result => {
            if (result.isConfirmed) {
                fetch("{{ route('whatsapp.stop') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(res => {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: res.message, timer: 3000 });
                });
            }
        });
    }

    // 12. Progress Monitor Polling
    function startProgressMonitor() {
        if (pollInterval) clearInterval(pollInterval);

        pollInterval = setInterval(() => {
            fetch("{{ route('whatsapp.progress') }}")
                .then(res => res.json())
                .then(res => {
                    if (!res.success) return;
                    const data = res.data || {};

                    const total = data.total || 0;
                    const sent = data.sent || 0;
                    const failed = data.failed || 0;
                    const processed = sent + failed;
                    const remaining = Math.max(0, total - processed);
                    const pct = total > 0 ? Math.round((processed / total) * 100) : 0;

                    document.getElementById('statTotal').innerText = total;
                    document.getElementById('statSent').innerText = sent;
                    document.getElementById('statFailed').innerText = failed;
                    document.getElementById('statRemaining').innerText = remaining;

                    document.getElementById('progressBarEl').style.width = pct + '%';
                    document.getElementById('progressPercentText').innerText = pct + '%';

                    if (data.current) {
                        document.getElementById('progressTargetText').innerHTML = 
                            `<i class="fa-solid fa-paper-plane fa-fade text-emerald-400 me-1"></i> Sedang memproses: <strong>${data.current.name}</strong> (${data.current.phone}) [${data.current.index}/${total}]`;
                    } else if (!data.isRunning && total > 0) {
                        document.getElementById('progressTargetText').innerHTML = 
                            `<i class="fa-solid fa-flag-checkered text-emerald-400 me-1"></i> Selesai.`;
                    }

                    const indicator = document.getElementById('blastStatusIndicator');
                    if (data.isRunning) {
                        indicator.innerHTML = `<span class="badge bg-success bg-opacity-25 text-emerald-400 border border-success border-opacity-40 px-3 py-2"><i class="fa-solid fa-spinner fa-spin me-1"></i> Sedang Berjalan...</span>`;
                        document.getElementById('btnStartBlast').classList.add('d-none');
                        document.getElementById('btnStopBlast').classList.remove('d-none');
                    } else {
                        indicator.innerHTML = `<span class="badge bg-black bg-opacity-40 text-white opacity-50 border border-white border-opacity-10 px-3 py-2">Standby</span>`;
                        document.getElementById('btnStartBlast').classList.remove('d-none');
                        document.getElementById('btnStopBlast').classList.add('d-none');
                    }

                    renderLogs(data.logs || []);

                    if (!data.isRunning && total > 0) {
                        clearInterval(pollInterval);
                        pollInterval = null;
                    }
                })
                .catch(err => console.error(err));
        }, 1500);
    }

    function renderLogs(logs) {
        if (!logs || logs.length === 0) return;
        document.getElementById('logPlaceholder')?.remove();

        const container = document.getElementById('logEntries');
        container.innerHTML = '';

        logs.slice(0, 50).forEach(log => {
            let colorClass = 'text-white';
            let icon = 'fa-info-circle';

            if (log.type === 'SUCCESS') {
                colorClass = 'text-emerald-400';
                icon = 'fa-check';
            } else if (log.type === 'ERROR' || log.type === 'FAIL') {
                colorClass = 'text-danger';
                icon = 'fa-xmark';
            } else if (log.type === 'WARN') {
                colorClass = 'text-warning';
                icon = 'fa-exclamation-triangle';
            } else if (log.type === 'DEBUG') {
                colorClass = 'text-white opacity-50';
                icon = 'fa-clock';
            }

            const item = document.createElement('div');
            item.className = `ticker-font small mb-1 ${colorClass}`;
            item.innerHTML = `<span class="opacity-50 me-2">[${log.time}]</span> <i class="fa-solid ${icon} me-1"></i> ${log.message}`;
            container.appendChild(item);
        });
    }
</script>
@endsection
