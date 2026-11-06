<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhatsAppBlastController extends Controller
{
    /**
     * Resolve the active WA Service URL dynamically
     * Supports: Database setting, Cache, Session, .env, or localhost
     */
    public function getServiceUrl(): string
    {
        // 1. Session check
        if (session()->has('wa_service_url') && !empty(session('wa_service_url'))) {
            return rtrim(session('wa_service_url'), '/');
        }

        // 2. Database check
        try {
            if (Schema::hasTable('app_settings')) {
                $dbVal = DB::table('app_settings')->where('key', 'wa_service_url')->value('value');
                if (!empty($dbVal)) {
                    return rtrim($dbVal, '/');
                }
            }
        } catch (\Exception $e) {}

        // 3. Cache check
        $cacheVal = Cache::get('wa_service_url');
        if (!empty($cacheVal)) {
            return rtrim($cacheVal, '/');
        }

        // 4. Environment variable check
        $envVal = env('WA_SERVICE_URL');
        if (!empty($envVal)) {
            return rtrim($envVal, '/');
        }

        // 5. Default local fallback
        return 'http://127.0.0.1:5050';
    }

    /**
     * Display the WhatsApp Blast Dashboard
     */
    public function index()
    {
        // 1. Get recipients from mitra_accounts (bypass global tenant scopes so all contacts appear)
        $allMitra = \App\Models\MitraAccount::withoutGlobalScopes()
            ->with(['user' => function($q) {
                $q->withoutGlobalScopes();
            }])
            ->orderBy('owner_name', 'asc')
            ->get();

        $recipientsList = collect();

        foreach ($allMitra as $m) {
            // Check direct phone on mitra_account or fallback to user profile
            $rawPhone = $m->getRawOriginal('phone') ?: ($m->user ? $m->user->phone : null);
            if ($rawPhone) {
                $cleanPhone = trim($rawPhone);
                $digits = preg_replace('/[^0-9]/', '', $cleanPhone);
                if ($cleanPhone !== '' && $cleanPhone !== '-' && strlen($digits) >= 8) {
                    $recipientsList->push((object) [
                        'name' => $m->owner_name ?: ($m->user ? $m->user->name : $m->username),
                        'phone' => $cleanPhone,
                        'role' => 'Mitra',
                        'source' => 'mitra',
                    ]);
                }
            }
        }

        // 2. Also get standalone users (without global scopes)
        $usersWithPhoneRaw = User::withoutGlobalScopes()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('phone', '!=', '-')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($usersWithPhoneRaw as $u) {
            $cleanPhone = trim($u->phone);
            $digits = preg_replace('/[^0-9]/', '', $cleanPhone);
            if ($cleanPhone !== '' && $cleanPhone !== '-' && strlen($digits) >= 8) {
                $recipientsList->push((object) [
                    'name' => $u->name ?: $u->username,
                    'phone' => $cleanPhone,
                    'role' => $u->role ? ucfirst($u->role) : 'User',
                    'source' => 'user',
                ]);
            }
        }

        // 3. Normalize & Deduplicate by phone number
        $seenNumbers = [];
        $usersWithPhone = $recipientsList->filter(function ($item) use (&$seenNumbers) {
            $numeric = preg_replace('/[^0-9]/', '', $item->phone);
            if (str_starts_with($numeric, '0')) {
                $normalized = '62' . substr($numeric, 1);
            } elseif (str_starts_with($numeric, '62')) {
                $normalized = $numeric;
            } else {
                $normalized = '62' . $numeric;
            }
            if (isset($seenNumbers[$normalized])) {
                return false;
            }
            $seenNumbers[$normalized] = true;
            return true;
        })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $allUsers = User::withoutGlobalScopes()->orderBy('name', 'asc')->get();
        $currentGatewayUrl = $this->getServiceUrl();

        return view('whatsapp.index', compact('usersWithPhone', 'allUsers', 'currentGatewayUrl'));
    }

    /**
     * Save/Update the WA Gateway URL (e.g. Cloudflare Tunnel URL when hosted on Vercel)
     */
    public function setGateway(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = rtrim($request->input('url'), '/');

        // Save to Session
        session(['wa_service_url' => $url]);

        // Save to Cache
        try {
            Cache::put('wa_service_url', $url, now()->addDays(30));
        } catch (\Exception $e) {}

        // Save to Database
        try {
            if (!Schema::hasTable('app_settings')) {
                Schema::create('app_settings', function ($table) {
                    $table->string('key')->primary();
                    $table->text('value')->nullable();
                    $table->timestamps();
                });
            }
            DB::table('app_settings')->updateOrInsert(
                ['key' => 'wa_service_url'],
                ['value' => $url, 'updated_at' => now()]
            );
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'URL Gateway WhatsApp berhasil disimpan!'
        ]);
    }

    /**
     * Check Baileys Microservice Status via the active Gateway
     */
    public function getStatus()
    {
        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(6)->get("{$gatewayUrl}/api/status");
            if ($response->successful()) {
                $data = $response->json();
                $data['gateway_url'] = $gatewayUrl;
                return response()->json($data);
            }
            return response()->json([
                'success' => false,
                'message' => "Layanan WA Gateway merespons dengan kesalahan (HTTP {$response->status()}).",
                'data' => ['status' => 'service_down', 'gateway_url' => $gatewayUrl]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal terhubung ke Gateway ({$gatewayUrl}): " . $e->getMessage(),
                'data' => ['status' => 'service_down', 'gateway_url' => $gatewayUrl, 'error' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Request Pairing Code with specified phone (e.g. 087822421207)
     */
    public function pair(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(18)->post("{$gatewayUrl}/api/pair", [
                'phone' => $request->phone
            ]);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal menghubungi WA Gateway ({$gatewayUrl}): " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request QR Code for scanning
     */
    public function connectQr()
    {
        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(10)->post("{$gatewayUrl}/api/connect-qr");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal menghubungi WA Gateway ({$gatewayUrl}): " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect / Logout WhatsApp Session
     */
    public function logout()
    {
        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(10)->post("{$gatewayUrl}/api/logout");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test send to single recipient
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(18)->post("{$gatewayUrl}/api/test-send", [
                'phone' => $request->phone,
                'message' => $request->message
            ]);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal kirim uji coba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start WhatsApp Blast Process
     */
    public function startBlast(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*.phone' => 'required|string',
            'message' => 'required|string',
            'minDelay' => 'nullable|numeric|min:3',
            'maxDelay' => 'nullable|numeric|min:5',
            'batchSize' => 'nullable|numeric|min:1',
            'batchDelay' => 'nullable|numeric|min:5'
        ]);

        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(15)->post("{$gatewayUrl}/api/blast/start", [
                'recipients' => $request->recipients,
                'message' => $request->message,
                'minDelay' => $request->input('minDelay', 10),
                'maxDelay' => $request->input('maxDelay', 25),
                'batchSize' => $request->input('batchSize', 15),
                'batchDelay' => $request->input('batchDelay', 60)
            ]);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai blast: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Blast Progress & Logs
     */
    public function getBlastProgress()
    {
        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(6)->get("{$gatewayUrl}/api/blast/status");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil progres: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop ongoing Blast
     */
    public function stopBlast()
    {
        $gatewayUrl = $this->getServiceUrl();

        try {
            $response = Http::timeout(6)->post("{$gatewayUrl}/api/blast/stop");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan blast: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse File (Excel, CSV, or Text) to extract phone numbers & names
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $recipients = [];

        try {
            if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                foreach ($rows as $rowIndex => $row) {
                    if ($rowIndex === 0) {
                        $headerText = strtolower(implode(' ', array_filter($row)));
                        if (str_contains($headerText, 'nama') || str_contains($headerText, 'phone') || str_contains($headerText, 'telepon') || str_contains($headerText, 'hp')) {
                            continue;
                        }
                    }

                    $name = '';
                    $phone = '';

                    foreach ($row as $cell) {
                        $cellVal = trim((string)$cell);
                        if (preg_match('/^(08|\+628|628)\d{7,12}$/', preg_replace('/[^0-9+]/', '', $cellVal))) {
                            $phone = $cellVal;
                        } elseif (!empty($cellVal) && empty($name) && !is_numeric($cellVal)) {
                            $name = $cellVal;
                        }
                    }

                    if (!empty($phone)) {
                        $recipients[] = [
                            'name' => $name ?: 'Pelanggan',
                            'phone' => $phone
                        ];
                    }
                }
            } else {
                $content = file_get_contents($file->getRealPath());
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $parts = preg_split('/[,;\t]/', $line);
                    $p = trim($parts[0]);
                    $n = isset($parts[1]) ? trim($parts[1]) : 'Pelanggan';
                    if (preg_match('/\d{9,15}/', preg_replace('/[^0-9]/', '', $p))) {
                        $recipients[] = [
                            'name' => $n,
                            'phone' => $p
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'count' => count($recipients),
                'recipients' => $recipients
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage()
            ], 422);
        }
    }
}
