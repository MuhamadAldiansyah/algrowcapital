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
        return $this->loadMasterPrompt();
    }

    /**
     * Load full master prompt from the artifact markdown file.
     * Falls back to a minimal inline prompt if the file is unavailable.
     */
    private function loadMasterPrompt(): string
    {
        $path = 'C:\\Users\\Julius\\.gemini\\antigravity\\brain\\883c0e05-d6c2-4b2e-bde5-0a6cec338a75\\master_prompt_algrow_chatbot.md';
        $content = @file_get_contents($path);
        if ($content !== false && strlen($content) > 100) {
            return $content;
        }
        Log::error('ChatbotController: master prompt file missing or empty at: ' . $path);
        // Minimal fallback
        return <<<FALLBACK
Anda adalah "Algrow AI Assistant", asisten resmi Algrow Capital.
Bantu pengguna seputar: pendaftaran kemitraan, Stockbit, Ajaib, Bank Jago reguler, dan mekanisme saham IPO.
Jangan jawab pertanyaan di luar topik tersebut. Balas: "Maaf, saya tidak bisa menjawab ini."
Jangan pernah meminta PIN, password, atau OTP pengguna.
Kontak admin: WhatsApp 087822421207.
FALLBACK;
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

        // ─── SEKURITAS OVERRIDE (HIGHEST PRIORITY) ─────────────────────────────
        // If the word 'sekuritas' appears explicitly in the query, ALWAYS route to sekuritas topic
        // This prevents "cara daftar sekuritas" being wrongly sent to 'join'
        if (str_contains($q, 'sekuritas') || str_contains($q, 'rdn') || str_contains($q, 'rekening dana nasabah')) {
            if (isset($scores['sekuritas']) && $scores['sekuritas'] >= 2) {
                $winner = 'sekuritas';
                $winnerScore = $scores['sekuritas'];
            }
        }

        // If stockbit or ajaib is explicitly mentioned with how-to context, route to sekuritas
        if ((str_contains($q, 'stockbit') || str_contains($q, 'ajaib')) &&
            (str_contains($q, 'cara') || str_contains($q, 'gimana') || str_contains($q, 'daftar') || str_contains($q, 'buka') || str_contains($q, 'buat') || str_contains($q, 'download') || str_contains($q, 'install'))) {
            if (isset($scores['sekuritas']) && $scores['sekuritas'] >= 2) {
                $winner = 'sekuritas';
                $winnerScore = $scores['sekuritas'];
            }
        }

        // ─── AMBIGUITY DETECTION ───────────────────────────────────────────────
        // Detect inherently ambiguous queries: words that could mean "daftar akun sekuritas"
        // OR "daftar bergabung ke algrow" with no clear context either way.

        // Ambiguous trigger keywords (generic, context-free)
        $ambiguousKeywords = ['daftar', 'buat akun', 'bikin akun', 'buka akun', 'cara daftar', 'mau daftar', 'gimana daftar', 'cara bikin akun', 'cara buat akun', 'panduan daftar'];
        $isAmbiguousQuery = false;
        foreach ($ambiguousKeywords as $kw) {
            if (str_contains($q, $kw)) {
                $isAmbiguousQuery = true;
                break;
            }
        }

        // Clear disambiguators for sekuritas
        $hasSekuritasHint = str_contains($q, 'sekuritas') || str_contains($q, 'stockbit') ||
                            str_contains($q, 'ajaib') || str_contains($q, 'rdn') ||
                            str_contains($q, 'rekening saham') || str_contains($q, 'rekening dana');

        // Clear disambiguators for join/algrow
        $hasJoinHint = str_contains($q, 'algrow') || str_contains($q, 'mitra') ||
                       str_contains($q, 'gabung') || str_contains($q, 'join') ||
                       str_contains($q, 'program') || str_contains($q, 'komunitas');

        // Ambiguous: query has generic daftar/akun keyword but no hint which direction
        if ($isAmbiguousQuery && !$hasSekuritasHint && !$hasJoinHint) {
            return $this->ambiguousResponse();
        }

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

        // ─── RESPONSES (Varied format: mix of paragraphs, structured, short & punchy) ──
        return match($winner) {
            'other_sekuritas' => "Pertanyaan bagus! Sekuritas seperti IPOT, Mirae Asset, Mandiri Sekuritas (MOST), BNI Sekuritas (BIONS), hingga BCA Sekuritas memang besar dan terpercaya di pasar modal Indonesia.\n\nTapi untuk program kemitraan IPO di Algrow Capital, saat ini kami hanya mendukung **Stockbit** dan **Ajaib**. Alasannya simpel — dua platform ini 100% online (tanpa berkas fisik), tanpa biaya admin RDN bulanan, verifikasi cepat, dan paling mulus diintegrasikan ke sistem alokasi IPO kolektif kami. Sekuritas lain punya alur teknis berbeda yang belum kami support. Ke depannya mungkin bisa berkembang, tapi untuk sekarang tetap pakai Stockbit atau Ajaib ya!",

            'jago' => "⚠️ **Perhatian penting soal Bank Jago!**\n\nBank Jago punya **2 versi aplikasi** yang beda:\n— **Jago Biasa** → ikon **kuning** ✅ (ini yang dipakai)\n— **Jago Syariah** → ikon **hijau** ❌ (tidak kompatibel)\n\nPastikan kamu download dan daftar **Jago yang ikonnya kuning (reguler)**, bukan yang syariah/hijau. Kalau salah pilih, akun tidak bisa digunakan untuk program kemitraan IPO ini.",

            'modal' => "Tenang, **kamu tidak perlu keluar modal sama sekali — Rp 0.** 🎉\n\nSeluruh dana untuk pemesanan saham IPO disiapkan 100% oleh Algrow Capital. Kamu cuma perlu menyediakan akun sekuritas (Stockbit/Ajaib) yang aktif dan terverifikasi. Setelah saham listing dan dijual di harga terbaik, profit bersihnya langsung dibagi hasil ke rekening kamu secara transparan. Tidak ada risiko kehilangan uang pribadi sama sekali.",

            'join' => "Cara gabung ke **Algrow Capital** itu gampang banget:\n\n1️⃣ Klik tombol **Gabung Sekarang** di website ini\n2️⃣ Pilih **Mitra Baru Algrow Capital**\n3️⃣ Isi formulir data diri + info akun sekuritas (username, password, PIN)\n4️⃣ Setelah submit, kamu langsung diarahkan ke **WhatsApp Admin 087822421207** untuk konfirmasi & proses approval\n\n*Seluruh proses gratis, tanpa biaya pendaftaran apapun.* 👍",

            'sekuritas' => "Belum punya akun sekuritas? Tenang, prosesnya gampang!\n\nDownload **Stockbit** atau **Ajaib** di Play Store / App Store, siapkan e-KTP dan rekening bank pribadi kamu, lalu ikuti panduan buka rekening saham di dalam aplikasinya — isi data diri, foto selfie dengan e-KTP, pilih bank RDN (gratis, tanpa saldo minimum). Proses verifikasi biasanya selesai dalam **1–24 jam kerja**. Setelah akun aktif, langsung bisa didaftarkan ke kemitraan Algrow Capital!",

            'aplikasi' => "Algrow Capital saat ini resmi menggunakan **2 platform sekuritas**:\n\n📱 **Stockbit** — PT Stockbit Sekuritas Digital\n📱 **Ajaib** — PT Ajaib Sekuritas Asia\n\nKeduanya terdaftar & diawasi OJK dan IDX, tersedia di Android dan iOS, pendaftaran 100% online, dan mendukung fitur e-IPO untuk pemesanan saham perdana.",

            'syarat' => "Syarat jadi mitra Algrow Capital simpel banget:\n\n✅ WNI minimal **17 tahun** + punya e-KTP\n✅ Akun sekuritas aktif di **Stockbit atau Ajaib**\n✅ Rekening bank pribadi atas nama sendiri\n✅ WhatsApp & email aktif\n\nSudah punya semua? Langsung daftar aja sekarang!",

            'bagi_hasil' => "Begini cara kerjanya:\n\nSetelah akun sekuritas kamu terdaftar dan disetujui, Algrow Capital menyiapkan **100% modal** untuk memesan saham IPO potensial melalui akun kamu. Ketika saham resmi listing di bursa dan mencapai harga optimal, saham dijual dan **profit bersihnya langsung ditransfer ke rekening bank kamu** sesuai rasio bagi hasil yang sudah disepakati — tanpa potongan tersembunyi, tanpa risiko modal pribadi.",

            'kontak' => "Butuh bantuan langsung? 👋\n\n📲 **WhatsApp Admin:** 087822421207\n\nTim kami siap memandu dari nol sampai akun kemitraan kamu aktif!",

            'greeting' => "Halo! 👋 Selamat datang di **Algrow Capital**!\n\nAda yang bisa saya bantu hari ini? Misalnya:\n— Cara gabung / daftar mitra\n— Panduan buka akun sekuritas (Stockbit/Ajaib)\n— Sistem bagi hasil & modal\n— Syarat kemitraan\n\nSilakan tanyakan langsung ya!",

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

    /**
     * Clarifying question when intent is ambiguous (join vs sekuritas).
     */
    private function ambiguousResponse(): string
    {
        return "Hm, boleh saya tanyakan dulu nih biar bisa jawab yang tepat 😊\n\nMaksudnya **\"daftar\"** di sini yang mana:\n\n**A.** Daftar / buka **akun sekuritas** (Stockbit atau Ajaib) — untuk yang belum punya rekening saham\n**B.** Daftar **bergabung sebagai Mitra** ke program Algrow Capital\n\nTulis aja **A** atau **B**, atau ceritain lebih detail ya!";
    }
}
