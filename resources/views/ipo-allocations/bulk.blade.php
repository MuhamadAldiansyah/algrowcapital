@extends('layouts.app')

@section('title', 'Input Jatah Sekaligus: ' . $ipo->code)

@section('content')
<style>
    .btn-primary-custom {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        border: none;
        color: white;
    }
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
        color: white;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
    }
    .btn-success-custom {
        background: linear-gradient(135deg, #10b981 0%, #065f46 100%);
        border: none;
        color: white;
    }
    .btn-success-custom:hover {
        background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
        color: white;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
    }
    .hover-glow:hover {
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3) !important;
        transform: translateY(-2px);
    }
</style>
<div class="mb-4">
    <a href="{{ route('ipos.show', $ipo) }}" class="btn btn-sm btn-outline-secondary border-emerald-900 text-emerald-500 px-3 rounded-pill hover-emerald shadow-sm">
        <i class="fa-solid fa-arrow-left me-2"></i> KEMBALI KE DETAIL IPO
    </a>
</div>

<form action="{{ route('ipos.allotment-bulk.store', $ipo) }}" method="POST">
    @csrf

    <div class="card stat-node border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent border-bottom border-emerald-900 border-opacity-50 pt-4 pb-3">
            <h5 class="fw-bold mb-0 text-white ticker-font"><i class="fa-solid fa-boxes-packing me-2 text-emerald-500"></i> INPUT HASIL PENJATAHAN MASAL</h5>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-end mb-4">
                <input type="hidden" name="final_price_ipo" id="global_price" value="{{ $ipo->price }}">
                <button type="submit" class="btn btn-success-custom px-5 py-2 fw-bold shadow-lg rounded-pill hover-glow">
                    <i class="fa-solid fa-save me-2"></i>SIMPAN SEMUA PENJATAHAN
                </button>
            </div>

            <!-- Bulk Apply Panel -->
            <div class="card border border-emerald-900 border-opacity-50 shadow-sm mb-4 bg-emerald-900 bg-opacity-10" style="border-style: dashed !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-emerald-400 ticker-font"><i class="fa-solid fa-bolt me-2"></i>TANGAN ROBOT (ISI CEPAT)</h6>
                        <small class="text-emerald-500 opacity-75">Terapkan jatah Lot yang sama ke semua akun mitra</small>
                    </div>
                    <div class="row align-items-end g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-emerald-500">JATAH EST. (LOT)</label>
                            <div class="input-group">
                                <input type="number" id="bulk_lot_mini" class="form-control bg-black border-emerald-900 text-white fw-bold ticker-font" placeholder="Contoh: 3" min="0">
                                <span class="input-group-text bg-emerald-900 bg-opacity-40 text-emerald-400 border-emerald-900 fw-bold">LOT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="btn-bulk-apply-lot" class="btn btn-primary-custom fw-bold px-4 rounded-pill shadow-lg w-100 py-2">
                                TERAPKAN <i class="fa-solid fa-angles-down ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-black bg-opacity-20">
                        <tr>
                            <th class="ps-4">AKUN MITRA</th>
                            <th>MODAL PESANAN</th>
                            <th style="width: 220px;">JATAH DAPAT (LOT)</th>
                            <th>TERPAKAI (Rp)</th>
                            <th class="pe-4 text-end">SISA/REFUND (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($placements as $index => $placement)
                        <tr class="placement-row" data-index="{{ $index }}" data-capital="{{ $placement->capital_allocated }}">
                            <td class="ps-4 py-3">
                                <input type="hidden" name="allocations[{{ $index }}][placement_id]" value="{{ $placement->id }}">
                                <div class="fw-bold text-white">{{ strtoupper($placement->mitraAccount->owner_name) }}</div>
                                <span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900 border-opacity-30 small fw-normal">{{ strtoupper($placement->mitraAccount->platform) }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-white small">Rp {{ number_format($placement->capital_allocated, 0, ',', '.') }}</div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm shadow-sm" style="max-width: 180px;">
                                    <input type="number" 
                                           name="allocations[{{ $index }}][lot_allocated]" 
                                           class="form-control lot-input fw-bold bg-black bg-opacity-40 border-emerald-900 text-emerald-400 ticker-font" 
                                           id="lot_{{ $index }}"
                                           value="{{ $placement->allocation ? $placement->allocation->lot_allocated : 0 }}" 
                                           required>
                                    <span class="input-group-text bg-emerald-900 bg-opacity-20 text-emerald-500 border-emerald-900">LOT</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-danger opacity-75 small" id="used_label_{{ $index }}">Rp 0</div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="fw-bold ticker-font small text-emerald-400" id="remaining_label_{{ $index }}">Rp 0</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
    $(document).ready(function() {
        function calculateRow(index) {
            const finalPrice = parseFloat($('#global_price').val()) || 0;
            const row = $(`.placement-row[data-index="${index}"]`);
            const capital = parseFloat(row.data('capital')) || 0;
            const lot = parseFloat($(`#lot_${index}`).val()) || 0;
            
            const used = lot * 100 * finalPrice;
            const remaining = capital - used;

            $(`#used_label_${index}`).text('Rp ' + used.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $(`#remaining_label_${index}`).text('Rp ' + remaining.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            
            if(remaining < 0) {
                $(`#remaining_label_${index}`).addClass('text-danger').removeClass('text-emerald-400');
            } else {
                $(`#remaining_label_${index}`).addClass('text-emerald-400').removeClass('text-danger');
            }
        }

        // Init calculations
        $('.placement-row').each(function() {
            calculateRow($(this).data('index'));
        });

        // Event listeners
        $('.lot-input, #global_price').on('input', function() {
            $('.placement-row').each(function() {
                calculateRow($(this).data('index'));
            });
        });

        // Bulk Apply Tangan Robot
        $('#btn-bulk-apply-lot').on('click', function() {
            const bulkLot = $('#bulk_lot_mini').val();
            if (bulkLot !== '') {
                let appliedCount = 0;
                let skippedCount = 0;

                $('.lot-input').each(function() {
                    const currentValue = $(this).val();
                    // Hanya terapkan jika masih 0 atau kosong (belum diisi mitra)
                    if (currentValue === '' || currentValue === '0' || parseFloat(currentValue) === 0) {
                        $(this).val(bulkLot);
                        appliedCount++;
                    } else {
                        skippedCount++;
                    }
                });
                
                // Recalculate all rows
                $('.placement-row').each(function() {
                    calculateRow($(this).data('index'));
                });

                // Sweetalert if available
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tangan Robot Bekerja!',
                        text: `Jatah ${bulkLot} LOT berhasil diterapkan ke ${appliedCount} akun. ${skippedCount} akun dilewati karena sudah terisi.`,
                        timer: 2500,
                        showConfirmButton: false,
                        background: '#198754',
                        color: '#fff',
                        iconColor: '#fff',
                    });
                } else {
                    alert(`Jatah ${bulkLot} LOT berhasil diterapkan ke ${appliedCount} akun. ${skippedCount} akun dilewati.`);
                }
            }
        });
    });
</script>
@endsection
@endsection
