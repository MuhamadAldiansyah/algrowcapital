<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\MitraAccount;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhatsAppBlastController extends Controller
{
    private string $serviceUrl = 'http://127.0.0.1:5050';

    /**
     * Display the WhatsApp Blast Dashboard
     */
    public function index()
    {
        // Get registered users with phone numbers
        $usersWithPhone = User::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name', 'asc')
            ->get();

        // All users for reference
        $allUsers = User::orderBy('name', 'asc')->get();

        return view('whatsapp.index', compact('usersWithPhone', 'allUsers'));
    }

    /**
     * Check Baileys Microservice Status
     */
    public function getStatus()
    {
        try {
            $response = Http::timeout(4)->get("{$this->serviceUrl}/api/status");
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json([
                'success' => false,
                'message' => 'Layanan WA Service merespons dengan kesalahan.',
                'data' => ['status' => 'service_down']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan WA Service offline (port 5050). Silakan jalankan start_wa.bat di wa_service.',
                'data' => ['status' => 'service_down', 'error' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Request Pairing Code with specified phone (default 087822421207)
     */
    public function pair(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        try {
            $response = Http::timeout(15)->post("{$this->serviceUrl}/api/pair", [
                'phone' => $request->phone
            ]);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi WA Service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request QR Code for scanning
     */
    public function connectQr()
    {
        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/api/connect-qr");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi WA Service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect / Logout WhatsApp Session
     */
    public function logout()
    {
        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/api/logout");
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

        try {
            $response = Http::timeout(15)->post("{$this->serviceUrl}/api/test-send", [
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

        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/api/blast/start", [
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
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/api/blast/status");
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
        try {
            $response = Http::timeout(5)->post("{$this->serviceUrl}/api/blast/stop");
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
                        // Skip header if first column has 'nama' or 'phone' or 'no'
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
                // Plain text line by line: "0878..., Budi" or "0878..."
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

