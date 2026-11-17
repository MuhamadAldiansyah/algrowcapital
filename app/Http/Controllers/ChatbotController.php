<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Handle incoming chatbot message from landing page.
     */
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $userMessage = trim($request->input('message'));
        $history = $request->input('history', []);

        // 1. Try Live Generative AI (Gemini / OpenAI) if API Key configured
        $geminiKey = env('GEMINI_API_KEY');
        $openaiKey = env('OPENAI_API_KEY');

        if ($geminiKey) {
            $aiResponse = $this->askGemini($userMessage, $history, $geminiKey);
            if ($aiResponse) {
                return response()->json([
                    'success' => true,
                    'source' => 'gemini_ai',
                    'reply' => $aiResponse,
                ]);
            }
        } elseif ($openaiKey) {
            $aiResponse = $this->askOpenAI($userMessage, $history, $openaiKey);
            if ($aiResponse) {
                return response()->json([
                    'success' => true,
                    'source' => 'openai',
                    'reply' => $aiResponse,
                ]);
            }
        }

        // 2. High-Precision Local Knowledge Base Guardrail Engine (Strict & Instant Fallback)
        $localResponse = $this->strictKnowledgeEngine($userMessage);

        return response()->json([
            'success' => true,
            'source' => 'local_ai_guardrail',
            'reply' => $localResponse,
        ]);
    }

    /**
     * Call Google Gemini API with strict system instructions
     */
    private function askGemini(string $message, array $history, string $apiKey): ?string
    {
        try {
            $systemInstruction = $this->getSystemPrompt();

            // Format contents for Gemini
            $contents = [];
            foreach (array_slice($history, -6) as $item) {
                if (isset($item['role'], $item['content'])) {
                    $role = $item['role'] === 'user' ? 'user' : 'model';
                    $contents[] = [
                        'role' => $role,
                        'parts' => [['text' => $item['content']]]
                    ];
                }
            }

            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

            $response = Http::timeout(10)->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $systemInstruction]]
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 600,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($reply) {
                    return trim($reply);
                }
            } else {
                Log::warning('Gemini API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Gemini Request Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Call OpenAI API with strict system instructions
     */
    private function askOpenAI(string $message, array $history, string $apiKey): ?string
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt()]
            ];

            foreach (array_slice($history, -6) as $item) {
                if (isset($item['role'], $item['content'])) {
                    $messages[] = [
                        'role' => $item['role'] === 'user' ? 'user' : 'assistant',
                        'content' => $item['content']
                    ];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::timeout(10)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => 0.3,
                'max_tokens' => 600,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return trim($data['choices'][0]['message']['content'] ?? '');
            }
        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Strict System Prompt for AI
     */
    private function getSystemPrompt(): string
    {
        return <<<PROMPT
Anda adalah "Algrow AI", asisten pintar resmi pendaftaran dan kemitraan Algrow Capital.

LINGKUP PENGETAHUAN & ATURAN MENJAWAB:
1. FORMAT JAWABAN (SANGAT PENTING):
   - Berikan jawaban dalam bentuk PARAGRAF yang mengalir, natural, luwes, santai, dan meyakinkan.
   - HINDARI dan JANGAN gunakan bullet points / poin-poin kaku yang tidak perlu. Tulis penjelasan seperti berbicara ramah dengan calon mitra.
2. PENGETAHUAN SEKURITAS DI INDONESIA:
   - Pahami lanskap sekuritas di Indonesia seperti IPOT (Indo Premier Sekuritas), Mirae Asset Sekuritas, Mandiri Sekuritas (MOST), BNI Sekuritas (BIONS), BCA Sekuritas, dsb.
   - Jika ada yang bertanya kenapa tidak memakai IPOT atau sekuritas lain: Jelaskan bahwa sekuritas tersebut memang besar dan bagus, namun saat ini Algrow Capital memfokuskan kemitraan pada Stockbit dan Ajaib karena antarmuka e-IPO di HP sangat praktis, verifikasi akun 100% online tanpa berkas fisik, tanpa biaya administrasi bulanan RDN, dan paling stabil terintegrasi dengan sistem manajemen alokasi saham IPO kolektif kami. Sekuritas lain belum didukung karena alur teknisnya berbeda, namun ke depan tidak menutup kemungkinan dikembangkan.
3. SKEMA MODAL (TEGAS & JELAS):
   - Jika ditanya "apakah saya ngeluarin modal?", "perlu keluar uang?", dsb: Jelaskan dengan tegas dan ramah dalam bentuk paragraf bahwa mitra 100% TIDAK PERLU KELUAR MODAL (Rp 0). Seluruh modal pemesanan dan pembelian saham IPO disiapkan oleh Algrow Capital. Mitra hanya menyediakan akun sekuritas aktif untuk menerima alokasi saham. Profit bersih dari penjualan saham langsung dibagi hasil secara transparan tanpa ada risiko modal bagi mitra.
4. CARA BERGABUNG & SYARAT:
   - Syarat: WNI minimal 17 tahun, punya e-KTP, punya akun sekuritas aktif (Stockbit / Ajaib), dan rekening bank pribadi untuk pencairan bagi hasil.
   - Cara bergabung: Klik tombol "Daftar / Gabung Sekarang" di website, pilih Mitra Baru Algrow Capital, isi data diri dan akun sekuritas, lalu diarahkan ke WhatsApp Admin 087822421207 untuk konfirmasi approval.
5. BANK JAGO (SANGAT PENTING — WAJIB DISAMPAIKAN):
   - Bank Jago memiliki DUA versi aplikasi yang berbeda: Jago Biasa (ikon warna KUNING, reguler) dan Jago Syariah (ikon warna HIJAU).
   - Untuk kemitraan Algrow Capital, mitra HARUS menggunakan Jago Biasa (kuning/reguler), BUKAN Jago Syariah. RDN saham IPO hanya kompatibel dengan Jago versi reguler.
   - Jika ada yang bertanya tentang Bank Jago, selalu ingatkan dengan tegas bahwa harus pilih Jago Biasa (kuning), bukan Jago Syariah.
6. BATASAN TOPIK:
   - Tetap fokus pada pendaftaran kemitraan Algrow Capital, sekuritas saham IPO, skema modal Rp 0, dan bagi hasil. Tolak topik di luar investasi/pendaftaran (politik, resep masakan, cuaca, dll) dengan ramah.
PROMPT;
    }

    /**
     * Intent Scoring NLP Engine — Semantic Understanding (No API Key Required).
     * Scores each topic by counting matching intent signals in the user's message.
     * Returns response for the highest-scoring topic. Far more robust than exact keyword matching.
     */
    private function strictKnowledgeEngine(string $text): string
    {
        $q = strtolower(trim($text));
        // Normalize common typos & slang
        $q = str_replace(['gmn', 'gimn', 'bgmn', 'bgmna', 'yg', 'utk', 'tuk', 'tp', 'tapi', 'kalo', 'kalau', 'klu', 'kl ', 'udah', 'udh', 'sdh', 'udh', 'bisa', 'bs ', 'bantu', 'tolong', 'tlng', 'jelaskan', 'jelasin', 'cerita', 'ceritain'], ['gimana', 'gimana', 'gimana', 'gimana', 'yang', 'untuk', 'untuk', 'tapi', 'tapi', 'kalau', 'kalau', 'kalau', 'kalau ', 'sudah', 'sudah', 'sudah', 'sudah', 'bisa', 'bisa ', 'bantu', 'tolong', 'tolong', 'jelaskan', 'jelaskan', 'cerita', 'cerita'], $q);

        // ─── TOPIC SCORING ────────────────────────────────────────────────────────
        // Each topic has a list of "intent signals". Score = count of signals found.
        // The topic with the HIGHEST score (≥ 1) wins.

        $topics = [

            // TOPIC A — Sekuritas Lain di Indonesia (IPOT, Mirae, Mandiri, BCA, dll) & Alasan Pemilihan Broker
            'other_sekuritas' => [
                'signals' => [
                    'ipot', 'indo premier', 'indopremier', 'mirae', 'mirae asset', 'mandiri sekuritas',
                    'most', 'bions', 'bni sekuritas', 'bca sekuritas', 'phillip', 'poems',
                    'trimegah', 'panin', 'sekuritas lain', 'broker lain',
                    'kenapa gak', 'kenapa ga', 'kenapa engga', 'kenapa tidak', 'kenapa bukan',
                    'kenapa stockbit', 'kenapa ajaib', 'kenapa harus stockbit', 'kenapa harus ajaib',
                    'selain stockbit', 'selain ajaib', 'bisa ipot', 'bisa mirae',
                ],
                'context_boost' => ['ipot', 'sekuritas', 'saham', 'broker', 'aplikasi', 'mirae'],
            ],

            // TOPIC B — Bank Jago (Biasa/Kuning vs Syariah)
            'jago' => [
                'signals' => [
                    'jago', 'bank jago', 'jago biasa', 'jago syariah', 'jago kuning', 'jago hijau',
                    'buka jago', 'daftar jago', 'pakai jago', 'pake jago', 'bikin jago',
                    'rdn jago', 'rekening jago', 'jago rdn',
                ],
                'context_boost' => ['jago', 'bank', 'rekening', 'rdn', 'syariah'],
            ],

            // TOPIC C — Daftar / Panduan Akun Sekuritas (Stockbit / Ajaib / RDN)
            'sekuritas' => [
                'signals' => [
                    'stockbit', 'ajaib', 'sekuritas', 'rdn', 'rekening dana nasabah',
                    'rekening saham', 'buka rekening', 'buka akun sekuritas',
                    'daftar sekuritas', 'bikin sekuritas', 'buat sekuritas',
                    'cara stockbit', 'cara ajaib', 'download stockbit', 'download ajaib',
                    'install stockbit', 'install ajaib',
                    'jago', 'bank jago',
                ],
                'context_boost' => ['daftar', 'buat', 'bikin', 'cara', 'gimana', 'panduan', 'langkah', 'proses', 'syarat', 'buka'],
            ],

            // TOPIC C — Cara Join / Daftar ke Algrow Capital
            'join' => [
                'signals' => [
                    'join', 'gabung', 'daftar', 'registrasi', 'register', 'masuk', 'ikut',
                    'cara masuk', 'cara bergabung', 'cara ikut', 'mau join', 'mau daftar',
                    'algrow', 'mitra', 'pendaftaran', 'buat akun', 'bikin akun', 'buka akun',
                    'daftar akun', 'panduan akun', 'panduan daftar', 'panduan buat',
                    'cara buat', 'cara bikin', 'cara daftar', 'cara join', 'cara gabung',
                    'gimana join', 'gimana daftar', 'gimana buat', 'gimana bikin',
                    'gimana cara', 'langkah daftar', 'langkah join', 'langkah masuk',
                    'apa yang harus', 'mulai dari mana', 'dari mana', 'mulai gimana',
                    'mulai darimana', 'harus ngapain', 'ngapain dulu', 'pertama apa',
                    'pertama gimana', 'prosedur', 'tahapan', 'alur daftar', 'alur join',
                ],
                'context_boost' => [],
            ],

            // TOPIC D — Aplikasi / Platform Sekuritas yang Dipakai
            'aplikasi' => [
                'signals' => [
                    'aplikasi', 'platform', 'broker', 'apk', 'app', 'aplikasinya',
                    'pakai apa', 'pake apa', 'pake aplikasi', 'pakai aplikasi',
                    'aplikasi apa', 'platform apa', 'broker apa', 'apk apa',
                    'download apa', 'install apa', 'menggunakan apa', 'digunakan apa',
                    'rekomendasikan', 'direkomendasikan', 'disarankan', 'sarannya',
                ],
                'context_boost' => ['sekuritas', 'trading', 'saham', 'ipo'],
            ],

            // TOPIC E — Modal / Biaya / Keluar Uang / Risiko
            'modal' => [
                'signals' => [
                    'modal', 'biaya', 'bayar', 'keluar uang', 'setor', 'deposit', 'depo',
                    'gratis', 'tarif', 'harga', 'ongkos', 'free', 'berbayar',
                    'butuh uang', 'perlu uang', 'pakai uang', 'pake uang', 'pake duit', 'keluar duit',
                    'uang sendiri', 'modal sendiri', 'keluar modal', 'ngeluarin modal', 'keluarin modal',
                    'butuh modal', 'ada biaya', 'dikenakan biaya', 'berapa biaya', 'berapa modal', 'berapaan',
                    'rugi', 'resiko', 'risiko', 'rugi ga', 'rugi gak', 'aman ga', 'aman gak', 'penipuan', 'nipu',
                ],
                'context_boost' => ['uang', 'modal', 'dana', 'biaya', 'bayar', 'keluar'],
            ],

            // TOPIC F — Syarat / Persyaratan Jadi Mitra
            'syarat' => [
                'signals' => [
                    'syarat', 'persyaratan', 'ketentuan', 'kriteria', 'dokumen',
                    'perlu apa', 'butuh apa', 'dibutuhkan apa', 'diperlukan apa',
                    'ktp', 'e-ktp', 'ktp elektronik', 'rekening bank', 'nomor rekening',
                    'yang diperlukan', 'apa saja yang', 'apa aja yang',
                ],
                'context_boost' => ['mitra', 'daftar', 'join', 'algrow'],
            ],

            // TOPIC G — Cara Kerja & Bagi Hasil / Profit
            'bagi_hasil' => [
                'signals' => [
                    'bagi hasil', 'profit', 'untung', 'keuntungan', 'penghasilan',
                    'cara kerja', 'kerjanya gimana', 'gimana kerjanya', 'sistem kerja',
                    'mekanisme', 'prosesnya', 'alurnya', 'hasilnya', 'hasilnya gimana',
                    'dapat berapa', 'dapet berapa', 'komisi', 'fee', 'imbal hasil',
                    'return', 'saham naik', 'ipo listing', 'jual saham', 'listing saham',
                    'penjatahan', 'allotment',
                ],
                'context_boost' => [],
            ],

            // TOPIC H — Kontak / Admin / CS / WhatsApp
            'kontak' => [
                'signals' => [
                    'admin', 'cs', 'customer service', 'layanan', 'support', 'bantuan',
                    'whatsapp', 'wa', 'kontak', 'nomor', 'hubungi', 'tanya langsung',
                    'chat langsung', 'minta tolong', 'ada orang',
                ],
                'context_boost' => [],
            ],

            // TOPIC I — Greeting (Exact/near-exact match only)
            'greeting' => [
                'signals' => [
                    'halo', 'hai', 'hi', 'hey', 'hei', 'selamat pagi', 'selamat siang',
                    'selamat sore', 'selamat malam', 'assalamualaikum', 'permisi', 'tes', 'test',
                ],
                'context_boost' => [],
            ],
        ];

        // ─── SCORE CALCULATION ────────────────────────────────────────────────────
        $scores = [];
        foreach ($topics as $topicKey => $topicData) {
            $score = 0;
            foreach ($topicData['signals'] as $signal) {
                if (str_contains($q, $signal)) {
                    $score += 2; // primary signal worth 2
                }
            }
            // Context boost: if both a primary signal AND a context word appear, add bonus
            if (!empty($topicData['context_boost']) && $score > 0) {
                foreach ($topicData['context_boost'] as $ctx) {
                    if (str_contains($q, $ctx)) {
                        $score += 1;
                    }
                }
            }
            $scores[$topicKey] = $score;
        }

        // Find winning topic
        arsort($scores);
        $winner = array_key_first($scores);
        $winnerScore = $scores[$winner];

        // Special: greeting must win outright only if score >= 2 (avoid false greeting on e.g. "halo cara daftar")
        // If greeting ties with another, prefer the other topic
        if ($winner === 'greeting' && $winnerScore <= 2) {
            $otherScores = array_filter($scores, fn($k) => $k !== 'greeting', ARRAY_FILTER_USE_KEY);
            $maxOther = max($otherScores);
            if ($maxOther >= 2) {
                arsort($otherScores);
                $winner = array_key_first($otherScores);
                $winnerScore = $maxOther;
            }
        }

        // Jago topic: if question mentions jago, prioritize it over sekuritas
        if (isset($scores['jago']) && $scores['jago'] >= 2) {
            if (str_contains($q, 'jago')) {
                $winner = 'jago';
                $winnerScore = $scores['jago'];
            }
        }

        // Other sekuritas (IPOT, Mirae, Mandiri, etc.) must win if broker names or "kenapa" appear
        if (isset($scores['other_sekuritas']) && $scores['other_sekuritas'] >= 2) {
            if (str_contains($q, 'ipot') || str_contains($q, 'mirae') || str_contains($q, 'mandiri') || str_contains($q, 'bions') || str_contains($q, 'bca sekuritas') || str_contains($q, 'sekuritas lain') || str_contains($q, 'kenapa')) {
                if ($scores['other_sekuritas'] >= ($scores['sekuritas'] ?? 0) && $scores['other_sekuritas'] >= ($scores['aplikasi'] ?? 0)) {
                    $winner = 'other_sekuritas';
                    $winnerScore = $scores['other_sekuritas'];
                }
            }
        }

        // Sekuritas topic must beat join topic if it has specific app-name signals
        // (avoid "cara daftar stockbit" being routed to 'join')
        if ($winner === 'join' && isset($scores['sekuritas']) && $scores['sekuritas'] >= 2) {
            if (str_contains($q, 'stockbit') || str_contains($q, 'ajaib') || str_contains($q, 'sekuritas') || str_contains($q, 'rdn')) {
                $winner = 'sekuritas';
                $winnerScore = $scores['sekuritas'];
            }
        }

        // Modal topic check: if question asks about modal, uang, biaya, rugi, aman
        if (isset($scores['modal']) && $scores['modal'] >= 2) {
            if (str_contains($q, 'modal') || str_contains($q, 'uang') || str_contains($q, 'duit') || str_contains($q, 'biaya') || str_contains($q, 'rugi') || str_contains($q, 'resiko') || str_contains($q, 'risiko') || str_contains($q, 'aman')) {
                if ($scores['modal'] >= $winnerScore || $winner === 'join' || $winner === 'syarat') {
                    $winner = 'modal';
                    $winnerScore = $scores['modal'];
                }
            }
        }

        // No topic matched → guardrail
        if ($winnerScore < 2) {
            return $this->outOfScopeResponse();
        }

        // ─── RESPONSES (Clean, natural paragraphs without stiff bullet points) ──────
        return match($winner) {
            'other_sekuritas' => "Pertanyaan yang sangat bagus! Sekuritas seperti IPOT (Indo Premier Sekuritas), Mirae Asset Sekuritas, Mandiri Sekuritas (MOST), BNI Sekuritas (BIONS), hingga BCA Sekuritas memang nama-nama besar dan sangat terpercaya di pasar modal Indonesia.\n\nNamun untuk program kemitraan IPO saham di Algrow Capital saat ini, kami secara khusus memfokuskan kerja sama pada platform **Stockbit** dan **Ajaib**. Hal ini dikarenakan proses pembukaan rekening di kedua platform tersebut 100% online tanpa berkas fisik, tanpa saldo minimal awal dan bebas biaya administrasi bulanan RDN, serta fitur antarmuka e-IPO di smartphone-nya sangat cepat dan terintegrasi mulus dengan sistem alokasi kolektif kami. Sekuritas lain seperti IPOT memiliki antarmuka dan alur verifikasi internal yang berbeda sehingga saat ini belum kami integrasikan. Jadi untuk saat ini, kamu tetap disarankan menggunakan akun Stockbit atau Ajaib ya agar bisa langsung berpartisipasi!",

            'jago' => "Bank Jago memang bisa menjadi salah satu pilihan rekening bank pendamping, namun ada satu hal **sangat penting** yang perlu kamu perhatikan: Bank Jago punya **dua versi aplikasi** yang berbeda — yaitu **Jago Biasa (ikon kuning)** dan **Jago Syariah (ikon hijau)**.\n\nUntuk kemitraan di Algrow Capital, kamu **wajib menggunakan Jago Biasa (kuning)**, bukan Jago Syariah. Ini karena RDN dan mekanisme pemesanan saham IPO hanya kompatibel dengan Jago versi reguler. Jadi pastikan saat download atau daftar, kamu pilih aplikasi **Jago** yang berlogo kuning ya, bukan yang versi syariah/hijau. Kalau salah pilih, akun tidak bisa digunakan untuk program kemitraan IPO ini.",

            'modal' => "Kamu sama sekali **tidak perlu mengeluarkan modal sepeserpun alias 100% gratis (Rp 0)**! Seluruh kebutuhan dana untuk pemesanan dan pembelian saham IPO akan disediakan dan ditanggung sepenuhnya oleh pihak Algrow Capital.\n\nPeran kamu sebagai mitra cukup menyediakan akun sekuritas (Stockbit atau Ajaib) yang sudah aktif dan terverifikasi untuk menerima alokasi penjatahan saham IPO tersebut. Ketika saham yang dipesan sudah resmi melantai di bursa efek (listing) dan dijual pada momentum harga terbaik, seluruh keuntungan bersihnya langsung kita bagi hasil secara transparan tanpa ada risiko kerugian modal pribadi untuk kamu. Jadi kamu bisa ikut serta dengan aman, nyaman, dan tenang tanpa perlu khawatir boncos!",

            'join' => "Untuk bergabung menjadi mitra Algrow Capital caranya sangat mudah dan cepat! Kamu cukup menekan tombol **Gabung Sekarang** yang ada di website ini, kemudian pilih pendaftaran Mitra Baru Algrow Capital. Setelah itu, lengkapi formulir pendaftaran dengan data diri dan informasi akun sekuritas kamu (Stockbit atau Ajaib) seperti username, password, dan PIN sekuritas yang diperlukan untuk pemesanan IPO.\n\nBegitu formulir berhasil dikirim, sistem akan secara otomatis mengarahkan kamu ke WhatsApp Admin resmi kami di nomor **087822421207** untuk konfirmasi data dan proses persetujuan (approval) oleh admin. Seluruh proses pendaftaran ini sepenuhnya gratis tanpa dipungut biaya apapun.",

            'sekuritas' => "Bagi kamu yang belum memiliki akun sekuritas, proses pembuatannya sangat mudah dan bisa diselesaikan langsung dari smartphone kamu. Pertama, unduh aplikasi **Stockbit** atau **Ajaib** melalui Google Play Store atau App Store. Siapkan e-KTP dan nomor rekening bank pribadi kamu, lalu ikuti instruksi pembukaan rekening saham di dalam aplikasi dengan mengisi identitas dan mengunggah foto selfie bersama e-KTP.\n\nPilih bank Rekening Dana Nasabah (RDN) yang gratis dan tanpa saldo minimum. Setelah proses verifikasi oleh pihak sekuritas selesai (biasanya memerlukan waktu 1 hingga 24 jam kerja), akun sekuritas kamu sudah aktif dan siap didaftarkan ke kemitraan Algrow Capital.",

            'aplikasi' => "Saat ini kemitraan Algrow Capital resmi menggunakan dua aplikasi sekuritas terkemuka di Indonesia, yaitu **Stockbit** (PT Stockbit Sekuritas Digital) dan **Ajaib** (PT Ajaib Sekuritas Asia). Kedua perusahaan sekuritas ini telah berizin resmi dan diawasi langsung oleh Otoritas Jasa Keuangan (OJK) serta Bursa Efek Indonesia (IDX), sehingga keamanan data maupun legalitasnya terjamin.\n\nKami memilih Stockbit dan Ajaib karena aplikasinya sangat ramah pengguna, proses pembuatan RDN serba online, dan fitur e-IPO di smartphone sangat cepat untuk eksekusi alokasi saham perdana.",

            'syarat' => "Persyaratan untuk menjadi mitra di Algrow Capital sangat praktis dan tidak memberatkan. Kamu hanya perlu merupakan Warga Negara Indonesia (WNI) berusia minimal 17 tahun dan memiliki e-KTP yang valid. Selain itu, kamu wajib memiliki akun sekuritas yang sudah aktif dan terverifikasi di Stockbit atau Ajaib, nomor rekening bank pribadi atas nama sendiri untuk pencairan dana bagi hasil profit, serta nomor WhatsApp dan email aktif untuk kelancaran komunikasi dan konfirmasi alokasi saham IPO. Jika semua syarat tersebut sudah kamu miliki, kamu bisa langsung mendaftar sekarang juga!",

            'bagi_hasil' => "Sistem bagi hasil dan mekanisme kerja di Algrow Capital dirancang secara transparan, adil, dan saling menguntungkan. Setelah akun sekuritas kamu terhubung dan disetujui, tim profesional kami akan menyalurkan modal dana penuh untuk memesan saham IPO berpotensi tinggi melalui akun kamu.\n\nKetika emiten resmi mencatatkan sahamnya di bursa efek (listing) dan mencapai kenaikan harga optimal, saham akan direalisasikan keuntungannya. Profit bersih yang didapatkan akan langsung ditransfer ke rekening bank pribadi kamu sesuai dengan persentase bagi hasil yang telah disepakati bersama tanpa potongan tersembunyi.",

            'kontak' => "Jika kamu membutuhkan bantuan langsung, ingin berkonsultasi, atau memerlukan pendampingan saat mendaftar, tim Customer Support Algrow Capital siap membantu kamu dengan senang hati. Kamu bisa langsung menghubungi Admin Support resmi kami melalui WhatsApp di nomor **087822421207**. Kami siap memandu kamu dari awal hingga akun kemitraan kamu aktif dan siap menghasilkan profit bersama!",

            'greeting' => "Halo! Selamat datang di layanan asisten resmi **Algrow Capital**! Saya siap membantu dan menjawab semua pertanyaan kamu seputar panduan mendaftar kemitraan, pilihan sekuritas seperti Stockbit dan Ajaib, penjelasan industri sekuritas di Indonesia, maupun sistem kemitraan IPO 100% tanpa modal pribadi. Ada yang bisa saya bantu atau jelaskan untuk kamu hari ini?",

            default => $this->outOfScopeResponse(),
        };
    }

    /**
     * Out-of-scope guardrail response (Clean paragraph).
     */
    private function outOfScopeResponse(): string
    {
        return "Mohon maaf ya, sebagai asisten khusus Algrow Capital, tugas saya adalah membantu menjawab pertanyaan seputar pendaftaran kemitraan, pilihan sekuritas seperti Stockbit dan Ajaib, informasi industri sekuritas di Indonesia, serta mekanisme saham IPO tanpa modal. Apabila kamu memiliki pertanyaan seputar cara mendaftar, syarat kemitraan, atau ingin berkonsultasi lebih lanjut, silakan tanyakan langsung di sini atau hubungi WhatsApp Admin kami di nomor **087822421207** ya!";
    }
}
