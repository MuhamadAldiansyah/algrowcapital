<?php

namespace App\Http\Controllers;

use App\Models\MitraAccount;
use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MitraAccountController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'semua');
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user && $user->role === 'user') {
            abort(403, 'Akses ditolak. Halaman Kelola Akun Mitra hanya untuk Admin dan Investor.');
        }

        if ($request->ajax()) {
            $query = MitraAccount::query();

            if ($user && $user->role === 'investor') {
                $query->whereHas('placements.fundings.investor', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } elseif ($user && $user->role === 'user') {
                $query->where(function($q) use ($user) {
                    $q->where('username', $user->username)
                      ->orWhere('owner_name', $user->name);
                });
            }

            // Status Filter
            if ($status === 'aktif') {
                $query->where('status', 'aktif');
            } elseif ($status === 'nonaktif') {
                $query->where('status', 'nonaktif');
            }

            // Search
            $searchValue = $request->input('search.value');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('owner_name', 'ilike', "%{$searchValue}%")
                      ->orWhere('username', 'ilike', "%{$searchValue}%")
                      ->orWhere('platform', 'ilike', "%{$searchValue}%")
                      ->orWhere('investor_name', 'ilike', "%{$searchValue}%");
                });
            }

            $recordsTotal = MitraAccount::count();
            $recordsFiltered = clone $query;
            $recordsFilteredCount = $recordsFiltered->count();

            // Sorting
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');
            $columns = ['platform', 'owner_name', 'username', 'password', 'pin', 'bank_rdn', 'rdn_account', 'device'];
            
            if (isset($columns[$orderColumnIndex])) {
                $query->orderBy($columns[$orderColumnIndex], $orderDir);
            } else {
                $query->orderBy('id', 'desc');
            }

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $accounts = $query->with('user')->get();

            // Format Data
            $data = [];
            foreach ($accounts as $account) {
                // If linked to a user profile, use their profile data, otherwise fallback to MitraAccount data
                $platform = $account->user && $account->user->sekuritas ? $account->user->sekuritas : $account->platform;
                $rawPassword = $account->user && $account->user->password_sekuritas ? $account->user->password_sekuritas : null;
                $rawPin = $account->user && $account->user->pin_sekuritas ? $account->user->pin_sekuritas : null;
                $bankName = $account->user && $account->user->bank ? $account->user->bank : $account->bank_rdn;
                $noRek = $account->user && $account->user->no_rek ? $account->user->no_rek : $account->rdn_account;

                // Decrypt logic for MitraAccount fallback
                $decryptedPassword = '';
                $decryptedPin = '';
                if ($rawPassword) {
                    $decryptedPassword = $rawPassword; // From user profile (plain text)
                } else {
                    try {
                        $decryptedPassword = $account->password ? Crypt::decryptString($account->password) : '';
                    } catch (\Exception $e) { $decryptedPassword = $account->password; }
                }
                
                if ($rawPin) {
                    $decryptedPin = $rawPin; // From user profile (plain text)
                } else {
                    try {
                        $decryptedPin = $account->pin ? Crypt::decryptString($account->pin) : '';
                    } catch (\Exception $e) { $decryptedPin = $account->pin; }
                }

                $platformBadge = '<span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900 border-opacity-30 small fw-normal">' . strtoupper($platform) . '</span>';
                
                $ownerText = '<a href="' . route('mitra-accounts.show', $account) . '" class="text-white text-decoration-none fw-bold hover-emerald d-block">' . strtoupper($account->owner_name) . '</a>';
                if ($account->status === 'nonaktif') {
                    $ownerText .= '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 mt-1" style="font-size: 0.65rem;">NONAKTIF</span>';
                }

                $editableClass = ($user && $user->role === 'investor') ? '' : 'editable-cell';
                $bankText = '<div class="fw-bold ' . $editableClass . '" data-id="' . $account->id . '" data-field="bank_rdn">' . ($bankName ?: '-') . '</div>';
                
                if ($user && $user->role === 'investor') {
                    $actionBtns = '<span class="text-muted small"><i class="fa-solid fa-lock"></i> Read Only</span>';
                } else {
                    $actionBtns = '
                        <div class="d-flex gap-2">
                            <a href="' . route('mitra-accounts.edit', $account) . '" class="btn btn-sm text-white border-0 rounded-circle" title="Edit">
                                <i class="fa-solid fa-edit"></i>
                            </a>
                            <form action="' . route('mitra-accounts.destroy', $account) . '" method="POST" onsubmit="return confirm(\'Yakin ingin menghapus akun ini?\');">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                }

                $data[] = [
                    $platformBadge,
                    $ownerText,
                    '<span class="ticker-font text-white opacity-75 ' . $editableClass . '" data-id="' . $account->id . '" data-field="username">' . $account->username . '</span>',
                    '<span class="ticker-font ' . $editableClass . '" data-id="' . $account->id . '" data-field="password">' . $decryptedPassword . '</span>',
                    '<span class="ticker-font ' . $editableClass . '" data-id="' . $account->id . '" data-field="pin">' . $decryptedPin . '</span>',
                    $bankText,
                    '<span class="ticker-font ' . $editableClass . '" data-id="' . $account->id . '" data-field="rdn_account">' . ($noRek ?: '-') . '</span>',
                    '<span class="' . $editableClass . '" data-id="' . $account->id . '" data-field="device">' . ($account->device ?: '-') . '</span>',
                    $actionBtns
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFilteredCount,
                'data' => $data
            ]);
        }
        
        // Count for the status buttons
        $countsQuery = MitraAccount::query();
        if ($user && $user->role === 'investor') {
            $countsQuery->whereHas('placements.fundings.investor', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $counts = [
            'semua' => (clone $countsQuery)->count(),
            'aktif' => (clone $countsQuery)->where('status', 'aktif')->count(),
            'nonaktif' => (clone $countsQuery)->where('status', 'nonaktif')->count(),
        ];

        $tenants = [];
        if ($user && $user->role === 'developer') {
            $tenants = \App\Models\Tenant::orderBy('name')->get();
        }

        return view('mitra-accounts.index', compact('status', 'counts', 'tenants'));
    }

    public function grid(Request $request)
    {
        $search = $request->input('search');
        $query = MitraAccount::when($search, function($q, $search) {
            return $q->where(function($subQ) use ($search) {
                $subQ->where('owner_name', 'like', "%{$search}%")
                     ->orWhere('username', 'like', "%{$search}%")
                     ->orWhere('platform', 'like', "%{$search}%")
                     ->orWhere('device', 'like', "%{$search}%");
            });
        });

        $mitraAccountsByDevice = $query->get()->groupBy(function($item) {
            return $item->device ? strtoupper($item->device) : 'TANPA DEVICE';
        })->sortBy(function($accounts, $device) {
            return $device === 'TANPA DEVICE' ? 1 : 0;
        });

        return view('mitra-accounts.grid', compact('mitraAccountsByDevice', 'search'));
    }

    public function create()
    {
        return view('mitra-accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'platform' => 'required|string',
            'username' => 'required|string|unique:mitra_accounts,username',
            'password' => 'required|string',
            'pin' => 'nullable|string',
            'nik' => 'nullable|string',
            'bank_rdn' => 'nullable|string',
            'rdn_account' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'device' => 'nullable|string',
        ]);

        // Encrypt sensitive data
        $validated['password'] = Crypt::encryptString($request->password);
        if ($request->pin) {
            $validated['pin'] = Crypt::encryptString($request->pin);
        }

        MitraAccount::create($validated);

        return redirect()->route('mitra-accounts.index')->with('success', 'Akun Mitra berhasil ditambahkan.');
    }

    public function show(MitraAccount $mitraAccount)
    {
        $mitraAccount->load(['placements.ipo', 'placements.allocation', 'placements.sale']);
        
        $chartDataRaw = [];
        
        $placements = $mitraAccount->placements->sortBy('id');
        
        foreach ($placements as $placement) {
            $allocation = $placement->allocation;
            $sale = $placement->sale;
            
            if ($allocation && $sale) {
                $mitraShare = $sale->net_profit * 0.5; // Mitra gets 50%
                
                $chartDataRaw[] = [
                    'label' => $placement->ipo->code,
                    'profit' => $mitraShare,
                    'date' => $sale->created_at->format('Y-m-d')
                ];
            }
        }
        
        return view('mitra-accounts.show', compact('mitraAccount', 'chartDataRaw'));
    }

    public function edit(MitraAccount $mitraAccount)
    {
        return view('mitra-accounts.edit', compact('mitraAccount'));
    }

    public function update(Request $request, MitraAccount $mitraAccount)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'platform' => 'required|string',
            'username' => 'required|string|unique:mitra_accounts,username,' . $mitraAccount->id,
            'status' => 'required|in:aktif,nonaktif',
            'device' => 'nullable|string',
        ]);

        if ($request->password) {
            $validated['password'] = Crypt::encryptString($request->password);
        }
        if ($request->pin) {
            $validated['pin'] = Crypt::encryptString($request->pin);
        }

        $mitraAccount->update($validated);

        return redirect()->route('mitra-accounts.index')->with('success', 'Akun Mitra berhasil diperbarui.');
    }

    public function destroy(MitraAccount $mitraAccount)
    {
        $mitraAccount->delete();
        return redirect()->route('mitra-accounts.index')->with('success', 'Akun Mitra berhasil dihapus.');
    }

    /**
     * Bulk Import from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
            'tenant_id' => 'nullable|exists:tenants,id'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xlsx', 'xls'])) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Harap upload file CSV atau Excel.');
        }

        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $rows = [];
                if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
                    $firstLine = fgets($handle);
                    $delimiter = ',';
                    if ($firstLine !== false) {
                        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                            $delimiter = ';';
                        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                            $delimiter = "\t";
                        }
                    }
                    rewind($handle);
                    while (($data = fgetcsv($handle, 4000, $delimiter)) !== FALSE) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            } else {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (count($rows) <= 1) {
            return redirect()->back()->with('error', 'File tidak memiliki data atau kosong.');
        }

        $count = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // Skip header row
            }

            // Trim cell values safely and convert to string
            $row = array_map(function($val) {
                return $val !== null ? trim((string)$val) : '';
            }, $row);

            // Ensure we have at least 11 elements to avoid undefined offset
            $row = array_pad($row, 11, '');

            // Mapping:
            // 0: ID
            // 1: PLATFORM
            // 2: Nama Pemilik (Owner Name)
            // 3: Username
            // 4: password
            // 5: PIN
            // 6: BANK RDN
            // 7: Rekening RDN
            // 8: Status
            // 9: Device
            // 10: Handler (optional)
            $platform   = $row[1];
            $ownerName  = $row[2];
            $username   = $row[3];
            $password   = $row[4];
            $pin        = $row[5];
            $bankRdn    = $row[6];
            $rdnAccount = $row[7];
            $status     = $row[8];
            $device     = $row[9];
            $handlerName = $row[10];

            if (empty($ownerName) || empty($username)) {
                continue;
            }

            $existingAccount = MitraAccount::where('username', $username)->first();

            if ($existingAccount) {
                // Decrypt existing password to compare
                $existingPassword = '';
                try {
                    $existingPassword = Crypt::decryptString($existingAccount->password);
                } catch (\Exception $e) {
                    $existingPassword = $existingAccount->password;
                }

                // Compare username & password
                // If username and password are the same, skip inserting/updating
                if ($existingPassword === $password) {
                    $skippedCount++;
                    continue;
                }

                // Otherwise, if password is different, update the existing record
                $updateData = [
                    'owner_name' => $ownerName,
                    'platform' => $platform ?: $existingAccount->platform,
                    'password' => Crypt::encryptString($password),
                    'pin' => !empty($pin) ? Crypt::encryptString($pin) : null,
                    'bank_rdn' => $bankRdn ?: null,
                    'rdn_account' => $rdnAccount ?: null,
                    'status' => (strtolower($status) == 'nonaktif' || strtolower($status) == 'non-aktif') ? 'nonaktif' : 'aktif',
                    'device' => $device ?: null,
                    'handler_name' => $handlerName ?: $existingAccount->handler_name,
                ];

                if (auth()->user()->role === 'developer' && $request->tenant_id) {
                    $updateData['tenant_id'] = $request->tenant_id;
                }

                $existingAccount->update($updateData);
                $updatedCount++;
            } else {
                // If username does not exist, insert it
                $insertData = [
                    'owner_name' => $ownerName,
                    'platform' => $platform ?: 'Stockbit',
                    'username' => $username,
                    'password' => Crypt::encryptString($password),
                    'pin' => !empty($pin) ? Crypt::encryptString($pin) : null,
                    'bank_rdn' => $bankRdn ?: null,
                    'rdn_account' => $rdnAccount ?: null,
                    'status' => (strtolower($status) == 'nonaktif' || strtolower($status) == 'non-aktif') ? 'nonaktif' : 'aktif',
                    'device' => $device ?: null,
                    'handler_name' => $handlerName ?: null,
                ];

                if (auth()->user()->role === 'developer' && $request->tenant_id) {
                    $insertData['tenant_id'] = $request->tenant_id;
                }

                MitraAccount::create($insertData);
                $count++;
            }
        }

        $message = "$count akun baru berhasil di-import dari file.";
        if ($updatedCount > 0 || $skippedCount > 0) {
            $parts = [];
            if ($count > 0) {
                $parts[] = "$count akun baru ditambahkan";
            }
            if ($updatedCount > 0) {
                $parts[] = "$updatedCount akun diperbarui";
            }
            if ($skippedCount > 0) {
                $parts[] = "$skippedCount akun dilewati (karena username & password sama)";
            }
            $message = implode(', ', $parts) . " dari file.";
        }

        return redirect()->route('mitra-accounts.index')->with('success', $message);
    }


    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [
            'PLATFORM', 'Nama Pemilik', 'Username', 'password', 'PIN',
            'BANK RDN', 'Rekening RDN', 'Status', 'Device', 'HANDLER'
        ];

        // Set Headers
        $col = 'A';
        foreach ($columns as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Set Example Row
        $exampleRow = [
            'Stockbit', 'John Doe', 'johndoe_sb', 'P@ssw0rd123', '123456',
            'BCA', '1234567890', 'Aktif', 'iPhone 13', 'Budi Santoso'
        ];
        
        $col = 'A';
        foreach ($exampleRow as $value) {
            $sheet->setCellValue($col . '2', $value);
            $col++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_import_akun_mitra.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function export()
    {
        $accounts = MitraAccount::orderBy('id', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'ID',
            'PLATFORM',
            'OWNER NAME',
            'USERNAME',
            'PASSWORD',
            'PIN',
            'BANK RDN',
            'REKENING RDN',
            'STATUS',
            'DEVICE',
            'HANDLER'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $rowNum = 2;
        foreach ($accounts as $account) {
            try {
                $password = \Illuminate\Support\Facades\Crypt::decryptString($account->password);
            } catch (\Exception $e) {
                $password = $account->password;
            }

            try {
                $pin = $account->pin ? \Illuminate\Support\Facades\Crypt::decryptString($account->pin) : '';
            } catch (\Exception $e) {
                $pin = $account->pin;
            }

            $rowData = [
                $account->id,
                strtoupper($account->platform),
                $account->owner_name,
                $account->username,
                $password,
                $pin,
                $account->bank_rdn,
                $account->rdn_account,
                strtoupper($account->status),
                $account->device,
                $account->handler_name
            ];

            $col = 'A';
            foreach ($rowData as $val) {
                $sheet->setCellValueExplicit($col . $rowNum, (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $col++;
            }
            $rowNum++;
        }

        $fileName = 'Daftar_Mitra_' . date('Y-m-d') . '.xlsx';

        $callback = function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function updateField(Request $request, MitraAccount $mitraAccount)
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string|max:255'
        ]);

        $field = $request->field;
        $value = $request->value;

        $allowedFields = ['owner_name', 'handler_name', 'username', 'bank_rdn', 'rdn_account', 'device', 'password', 'pin'];
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Field not allowed.'], 403);
        }

        // Encrypt password and pin if they are being updated
        if (in_array($field, ['password', 'pin'])) {
            $value = \Illuminate\Support\Facades\Crypt::encryptString($value);
        }

        $mitraAccount->update([$field => $value]);

        return response()->json(['success' => true]);
    }
}
