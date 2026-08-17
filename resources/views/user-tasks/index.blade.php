@extends('layouts.app')

@section('title', 'Eksekusi IPO')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card stat-node border-0 shadow-lg overflow-hidden">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-3 border-bottom border-emerald-900 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="fa-solid fa-bolt me-2 text-muted"></i> Daftar Eksekusi Menunggu</h5>
                    <small class="text-emerald-500 opacity-75">Silakan isi data penjatahan atau penjualan untuk akun-akun Anda.</small>
                </div>
            </div>
            <div class="card-body p-4">
                @if(empty($tasks))
                    <div class="text-center py-5">
                        <i class="fa-solid fa-clipboard-check text-emerald-500 opacity-25 mb-3" style="font-size: 4rem;"></i>
                        <h5 class="text-white fw-bold">Semua Eksekusi Selesai!</h5>
                        <p class="text-emerald-500 opacity-75">Saat ini tidak ada emiten IPO yang membutuhkan aksi eksekusi dari Anda.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($tasks as $task)
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-black bg-opacity-30 border border-{{ $task['color'] }} border-opacity-50 rounded-3 shadow-sm h-100 position-relative overflow-hidden">
                                <div class="position-absolute top-0 end-0 bg-{{ $task['color'] }} text-black px-2 py-1 rounded-bl-3 fw-bold small" style="font-size: 0.7rem; border-bottom-left-radius: 8px;">
                                    {{ $task['count'] }} AKUN
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    @if($task['ipo']->image_path)
                                        <img src="{{ Storage::url($task['ipo']->image_path) }}" alt="{{ $task['ipo']->code }}" class="rounded bg-white p-1 me-3" style="width: 45px; height: 45px; object-fit: contain;">
                                    @else
                                        <div class="rounded bg-{{ $task['color'] }} bg-opacity-25 text-{{ $task['color'] }} d-flex align-items-center justify-content-center fw-bold me-3" style="width: 45px; height: 45px; border: 1px solid rgba(255,255,255,0.1);">
                                            {{ substr($task['ipo']->code, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h5 class="mb-0 fw-bold ticker-font text-white">{{ $task['ipo']->code }}</h5>
                                        <small class="text-muted">{{ $task['ipo']->name }}</small>
                                    </div>
                                </div>
                                
                                <p class="text-{{ $task['color'] }} mb-3 fw-bold small">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $task['label'] }}
                                </p>

                                @if($task['type'] === 'allotment')
                                    <a href="{{ route('user-tasks.allotment', $task['ipo']->id) }}" class="btn btn-{{ $task['color'] }} w-100 fw-bold rounded-pill text-black">
                                        <i class="fa-solid fa-edit me-1"></i> Isi Penjatahan
                                    </a>
                                @else
                                    @if(isset($task['disabled']) && $task['disabled'])
                                        <button disabled class="btn btn-{{ $task['color'] }} w-100 fw-bold rounded-pill opacity-75" style="cursor: not-allowed;">
                                            <i class="fa-solid fa-clock me-1"></i> Belum Waktunya
                                        </button>
                                    @else
                                        <a href="{{ route('user-tasks.sale', $task['ipo']->id) }}" class="btn btn-{{ $task['color'] }} w-100 fw-bold rounded-pill text-black">
                                            <i class="fa-solid fa-money-bill-wave me-1"></i> Realisasi Jual
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
