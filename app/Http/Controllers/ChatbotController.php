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
Anda adalah "Algrow AI", asisten pintar resmi pendaftaran Algrow Capital.

LINGKUP TUGAS EKSKLUSIF ANDA HANYA SEPUTAR PENDAFTARAN & KEMITRAAN:
1. Cara bergabung / mendaftar ke Algrow Capital (pilih peran Mitra atau Asset Manager).
2. Aplikasi sekuritas apa saja yang digunakan (saat ini resmi mendukung STOCKBIT dan AJAIB).
3. Panduan cara daftar akun sekuritas di aplikasi Stockbit & Ajaib (syarat KTP, RDN, verifikasi akun).
4. Skema modal: Mitra 100% TIDAK KELUAR MODAL (Rp 0). Seluruh modal penjatahan saham IPO disiapkan oleh Algrow Capital.
5. Skema bagi hasil: Keuntungan bersih dari penjualan saham IPO dibagikan sesuai kesepakatan secara transparan.
6. Persyaratan pendaftaran Mitra: Memiliki akun sekuritas aktif (Stockbit / Ajaib), e-KTP, email aktif, nomor rekening bank untuk pencairan profit.

ATURAN KETAT / STRICT GUARDRAIL (WAJIB DIPATUHI):
- DILARANG KERAS menjawab topik apapun di luar pendaftaran Algrow Capital, sekuritas Stockbit/Ajaib, dan kemitraan IPO (seperti politik, tugas sekolah, resep masakan, cuaca, programming, curhat, crypto, pinjaman online, dll).
- Jika ada pertanyaan di luar topik pendaftaran, tolak secara tegas, sopan, dan ramah:
  "Maaf ya, saya adalah asisten khusus pendaftaran Algrow Capital. Saya hanya dapat menjawab seputar cara bergabung, aplikasi sekuritas yang digunakan (Stockbit & Ajaib), dan panduan pendaftaran akun. Ada yang bisa saya bantu seputar pendaftaran Algrow Capital?"
- Gunakan bahasa Indonesia yang santai tapi profesional, ramah, to the point, dan gunakan format bullet point agar mudah dibaca di layar HP.
- Selalu ingatkan bahwa proses pendaftaran bisa langsung dilakukan melalui tombol "Daftar / Gabung Sekarang" di website.
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

            // TOPIC A — Daftar / Panduan Akun Sekuritas (Stockbit / Ajaib / RDN)
            'sekuritas' => [
                'signals' => [
                    'stockbit', 'ajaib', 'sekuritas', 'rdn', 'rekening dana nasabah',
                    'rekening saham', 'buka rekening', 'buka akun sekuritas',
                    'daftar sekuritas', 'bikin sekuritas', 'buat sekuritas',
                    'cara stockbit', 'cara ajaib', 'download stockbit', 'download ajaib',
                    'install stockbit', 'install ajaib',
                ],
                'context_boost' => ['daftar', 'buat', 'bikin', 'cara', 'gimana', 'panduan', 'langkah', 'proses', 'syarat', 'buka'],
            ],

            // TOPIC B — Cara Join / Daftar ke Algrow Capital
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

            // TOPIC C — Aplikasi / Platform Sekuritas yang Dipakai
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

            // TOPIC D — Modal / Biaya / Keluar Uang
            'modal' => [
                'signals' => [
                    'modal', 'biaya', 'bayar', 'keluar uang', 'setor', 'deposit', 'depo',
                    'gratis', 'tarif', 'harga', 'ongkos', 'free', 'berbayar',
                    'butuh uang', 'perlu uang', 'pakai uang', 'pake uang',
                    'uang sendiri', 'modal sendiri', 'keluar modal', 'ada biaya',
                    'dikenakan biaya', 'berapa biaya', 'berapa modal', 'berapaan',
                ],
                'context_boost' => [],
            ],

            // TOPIC E — Syarat / Persyaratan Jadi Mitra
            'syarat' => [
                'signals' => [
                    'syarat', 'persyaratan', 'ketentuan', 'kriteria', 'dokumen',
                    'perlu apa', 'butuh apa', 'dibutuhkan apa', 'diperlukan apa',
                    'ktp', 'e-ktp', 'ktp elektronik', 'rekening bank', 'nomor rekening',
                    'yang diperlukan', 'apa saja yang', 'apa aja yang',
                ],
                'context_boost' => ['mitra', 'daftar', 'join', 'algrow'],
            ],

            // TOPIC F — Cara Kerja & Bagi Hasil / Profit
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

            // TOPIC G — Kontak / Admin / CS / WhatsApp
            'kontak' => [
                'signals' => [
                    'admin', 'cs', 'customer service', 'layanan', 'support', 'bantuan',
                    'whatsapp', 'wa', 'kontak', 'nomor', 'hubungi', 'tanya langsung',
                    'chat langsung', 'minta tolong', 'ada orang',
                ],
                'context_boost' => [],
            ],

            // TOPIC H — Greeting (Exact/near-exact match only)
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

        // Sekuritas topic must beat join topic if it has specific app-name signals
        // (avoid "cara daftar stockbit" being routed to 'join')
        if ($winner === 'join' && isset($scores['sekuritas']) && $scores['sekuritas'] >= 2) {
            if (str_contains($q, 'stockbit') || str_contains($q, 'ajaib') || str_contains($q, 'sekuritas') || str_contains($q, 'rdn')) {
                $winner = 'sekuritas';
                $winnerScore = $scores['sekuritas'];
            }
        }

        // No topic matched → guardrail
        if ($winnerScore < 2) {
            return $this->outOfScopeResponse();
        }

        // ─── RESPONSES ───────────────────────────────────────────────────────────
        return match($winner) {
            'sekuritas' => "📝 **Panduan Cara Daftar Akun Sekuritas (Stockbit / Ajaib):**\n\n" .
                "1️⃣ **Download Aplikasi:** Unduh **Stockbit** atau **Ajaib** di Google Play Store atau App Store.\n" .
                "2️⃣ **Siapkan Dokumen:** e-KTP asli dan nomor rekening bank pribadi (BCA/Mandiri/BRI/dll).\n" .
                "3️⃣ **Isi Data Diri:** Buka aplikasi, pilih 'Buka Rekening Saham', isi form identitas dan foto selfie bersama e-KTP.\n" .
                "4️⃣ **Pembuatan RDN:** Pilih bank RDN yang tersedia di aplikasi (gratis, tanpa saldo minimum).\n" .
                "5️⃣ **Tunggu Verifikasi:** Pihak sekuritas memverifikasi akun Anda dalam 1–24 jam kerja.\n" .
                "6️⃣ **Selesai!** Akun aktif & RDN jadi → siap ditautkan ke Algrow Capital.",

            'join' => "🚀 **Panduan Lengkap Cara Bergabung ke Algrow Capital:**\n\n" .
                "**Langkah 1 — Daftar Akun di Platform Algrow Capital**\n" .
                "1️⃣ Klik tombol **[Gabung Sekarang]** di website ini.\n" .
                "2️⃣ Pilih peran Anda sebagai **Mitra**.\n" .
                "3️⃣ Pilih nama perusahaan induk (**Algrow Capital**).\n" .
                "4️⃣ Masukkan email aktif dan buat kata sandi akun Anda.\n" .
                "5️⃣ Masukkan kode OTP verifikasi yang dikirim ke email Anda.\n\n" .
                "**Langkah 2 — Siapkan Akun Sekuritas (Stockbit / Ajaib)**\n" .
                "• Pastikan sudah punya akun sekuritas aktif di **Stockbit** atau **Ajaib**.\n" .
                "• Akun ini untuk penjatahan saham IPO — **100% modal disiapkan Algrow Capital, modal Anda Rp 0!**\n\n" .
                "💡 *Pendaftaran 100% gratis, tanpa biaya apapun.*",

            'aplikasi' => "📱 **Aplikasi Sekuritas yang Digunakan di Algrow Capital:**\n\n" .
                "Algrow Capital resmi mendukung 2 aplikasi sekuritas terpercaya terdaftar di **OJK & IDX**:\n\n" .
                "1. **Stockbit** (PT Stockbit Sekuritas Digital)\n" .
                "2. **Ajaib** (PT Ajaib Sekuritas Asia)\n\n" .
                "Keduanya tersedia di Android & iOS, pendaftaran 100% online, dan mendukung fitur pemesanan saham **e-IPO**.",

            'modal' => "💰 **Apakah Butuh Modal Sendiri?**\n\n" .
                "**TIDAK SAMA SEKALI! (Modal Rp 0)** 🎉\n\n" .
                "• **100% modal penjatahan saham IPO disiapkan oleh Algrow Capital.**\n" .
                "• Anda sebagai Mitra **hanya menyediakan akun sekuritas** (Stockbit/Ajaib).\n" .
                "• Tidak perlu setor uang sepeserpun.\n" .
                "• Profit dari penjualan saham IPO langsung dibagi hasil secara transparan!",

            'syarat' => "📋 **Persyaratan Menjadi Mitra Algrow Capital:**\n\n" .
                "1. WNI berusia minimal 17 tahun dengan e-KTP.\n" .
                "2. Akun sekuritas aktif di **Stockbit** atau **Ajaib** yang sudah terverifikasi.\n" .
                "3. Nomor rekening bank pribadi untuk pencairan bagi hasil profit.\n" .
                "4. Email & nomor WhatsApp aktif untuk notifikasi alokasi saham IPO.\n\n" .
                "Jika sudah lengkap, langsung daftar sekarang!",

            'bagi_hasil' => "🤝 **Cara Kerja & Sistem Bagi Hasil Algrow Capital:**\n\n" .
                "1. **Daftar** sebagai Mitra & hubungkan akun sekuritas (Stockbit/Ajaib).\n" .
                "2. **Algrow Capital** siapkan 100% modal untuk memesan saham IPO via akun Anda.\n" .
                "3. **Penjatahan saham** berlangsung saat masa listing IPO.\n" .
                "4. **Saham dijual** di momentum optimal kenaikan harga.\n" .
                "5. **Profit bersih** langsung ditransfer ke rekening pribadi Anda sesuai rasio kesepakatan.",

            'kontak' => "📞 **Hubungi Tim Admin & Support Algrow Capital:**\n\n" .
                "Butuh panduan langsung atau pendampingan mendaftar? Tim kami siap membantu:\n\n" .
                "👉 **WhatsApp Support:** `087822421207`\n\n" .
                "Atau klik tombol **[Gabung Sekarang]** untuk registrasi instan.",

            'greeting' => "Halo! 👋 Selamat datang di **Algrow Capital**.\n\n" .
                "Saya asisten virtual khusus yang siap memandu Anda seputar:\n" .
                "• **Cara bergabung / join ke Algrow Capital**\n" .
                "• **Aplikasi sekuritas yang digunakan** (Stockbit & Ajaib)\n" .
                "• **Panduan pendaftaran akun sekuritas**\n" .
                "• **Sistem investasi saham IPO tanpa modal**\n\n" .
                "Ada yang ingin ditanyakan seputar pendaftaran, Kak?",

            default => $this->outOfScopeResponse(),
        };
    }

    /**
     * Out-of-scope guardrail response.
     */
    private function outOfScopeResponse(): string
    {
        return "Mohon maaf ya, saya adalah **Asisten Khusus Pendaftaran Algrow Capital**. 🙏\n\n" .
               "Lingkup jawaban saya dikhususkan untuk:\n" .
               "1️⃣ **Cara join & mendaftar ke Algrow Capital**\n" .
               "2️⃣ **Aplikasi sekuritas yang dipakai** (*Stockbit & Ajaib*)\n" .
               "3️⃣ **Panduan pendaftaran akun sekuritas & RDN**\n" .
               "4️⃣ **Syarat kemitraan & sistem bagi hasil tanpa modal**\n\n" .
               "Silakan tanyakan hal seputar pendaftaran di atas ya! 😊";
    }
}
