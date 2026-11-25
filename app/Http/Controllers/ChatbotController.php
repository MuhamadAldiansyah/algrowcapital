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
        $localResponse = $this->strictKnowledgeEngine($userMessage, $history);

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
        $candidatePaths = [
            resource_path('chatbot/master_prompt_algrow_chatbot.md'),
            base_path('resources/chatbot/master_prompt_algrow_chatbot.md'),
            'C:\\Users\\Julius\\.gemini\\antigravity\\brain\\883c0e05-d6c2-4b2e-bde5-0a6cec338a75\\master_prompt_algrow_chatbot.md',
        ];

        foreach ($candidatePaths as $path) {
            if (@file_exists($path)) {
                $content = @file_get_contents($path);
                if ($content !== false && strlen($content) > 100) {
                    return $content;
                }
            }
        }

        Log::warning('ChatbotController: master prompt file missing, using inline fallback.');
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
    private function strictKnowledgeEngine(string $text, array $history = []): string
    {
        $q = strtolower(trim($text));
        // Normalize common typos & slang — gunakan word-boundary (\b) agar tidak
        // mengganti substring (mis. 'udah' di dalam 'sudah')
        $slangMap = [
            '/\b(gmn|gimn|bgmn|bgmna)\b/i'   => 'gimana',
            '/\b(yg)\b/i'                      => 'yang',
            '/\b(utk|tuk)\b/i'                => 'untuk',
            '/\b(tp|tapi)\b/i'                => 'tapi',
            '/\b(kalo|klu)\b/i'               => 'kalau',
            '/\b(udah|udh|sdh)\b/i'           => 'sudah',
            '/\b(bs)\b/i'                      => 'bisa',
            '/\b(tlng)\b/i'                    => 'tolong',
            '/\b(jelasin)\b/i'                 => 'jelaskan',
            '/\b(ceritain)\b/i'                => 'cerita',
            '/\b(blm|lum)\b/i'                => 'belum',
            '/\b(gk|ga|gak|engga|enggak)\b/i' => 'tidak',
            '/\b(mau|pengen|ingin)\b/i'        => 'mau',
        ];
        $q = preg_replace(array_keys($slangMap), array_values($slangMap), $q);

        // ─── CONVERSATIONAL QUICK-REPLIES & GREETINGS / APPRECIATION ─────────────
        $clean = trim(preg_replace('/[.,!?]/', '', $q));

        // ─── TERIMA KASIH (Thanks / Gratitude) ──────────────────────────────────
        $thanksWords = [
            'terima kasih', 'terimakasih', 'makasih', 'makasi', 'mksih', 'tq',
            'thank you', 'thanks', 'thx', 'tengkyu', 'matur nuwun', 'nuhun', 'syukron'
        ];
        $isThanks = false;
        foreach ($thanksWords as $tw) {
            if (str_contains($clean, $tw)) {
                $isThanks = true;
                break;
            }
        }

        // Kata kunci pertanyaan untuk membedakan salam murni vs pertanyaan dengan salam
        $questionKeywords = [
            'cara', 'gimana', 'bagaimana', 'apa', 'apakah', 'berapa', 'kenapa',
            'bisa', 'syarat', 'modal', 'daftar', 'stockbit', 'ajaib', 'jago', 'biaya', 'bagi hasil',
            'aman', 'keamanan', 'legal', 'penipuan', 'nipu', 'kendala', 'gagal', 'ditolak', 'buram',
            'persen', 'persentase', 'durasi', 'lama', 'kapan'
        ];
        $hasQuestionSignal = false;
        foreach ($questionKeywords as $qw) {
            if (str_contains($clean, $qw)) {
                $hasQuestionSignal = true;
                break;
            }
        }

        if ($isThanks && !$hasQuestionSignal) {
            return "Sama-sama! Senang bisa membantu kamu. 😊\n\nJika masih ada hal lain yang ingin ditanyakan seputar pendaftaran mitra, akun sekuritas (Stockbit/Ajaib), atau mekanisme bagi hasil IPO Algrow Capital, langsung tanyakan saja ya. Kamu juga bisa menghubungi WhatsApp Admin resmi kami di **087822421207**.\n\nSemoga sukses selalu!";
        }

        // ─── SALAM & SAPAAN INTERAKTIF (Greetings) ──────────────────────────────
        if (!$hasQuestionSignal) {
            // 1. Salam Islami
            if (preg_match('/\b(assalamu\s*alaikum|assalamualaikum|samlikum|aslm|assalam)\b/i', $clean)) {
                return "Wa'alaikumussalam warahmatullahi wabarakatuh! 👋 Selamat datang di **Algrow Capital**.\n\nAda yang bisa saya bantu hari ini seputar program kemitraan saham IPO tanpa modal, panduan buka akun sekuritas (Stockbit / Ajaib), atau alur pendaftaran mitra?";
            }

            // 2. Salam Waktu (Pagi / Siang / Sore / Malam)
            if (preg_match('/\b(selamat\s+(pagi|siang|sore|malam)|(pagi|siang|sore|malam)\s+(min|kak|mas|bro|admin))\b/i', $clean) || in_array($clean, ['pagi', 'siang', 'sore', 'malam'])) {
                return "Halo, selamat datang di **Algrow Capital**! 👋\n\nAda yang bisa saya bantu hari ini seputar program kemitraan saham IPO tanpa modal, panduan registrasi akun sekuritas (Stockbit / Ajaib), atau syarat bergabung menjadi mitra?";
            }

            // 3. Sapaan Umum & Santai (Halo, Hai, Hey Bro, P, Min, Kak, Admin, dll.)
            $greetingWords = [
                'halo', 'hai', 'hi', 'hey', 'hei', 'hello', 'helo',
                'p', 'ping', 'tes', 'test', 'permisi', 'bro', 'min', 'kak', 'admin', 'bang', 'mas',
                'apa kabar', 'halo bro', 'hey bro', 'hai bro', 'halo min', 'hai min', 'hey min',
                'halo kak', 'hai kak', 'halo admin', 'hai admin', 'spada'
            ];
            if (in_array($clean, $greetingWords) || preg_match('/^(halo|hai|hey|hei|hello)\s*(bro|min|kak|mas|admin|semua)?$/i', $clean)) {
                return "Halo! 👋 Selamat datang di **Algrow Capital**!\n\nSaya asisten virtual resmi Algrow Capital. Ada yang bisa saya bantu hari ini? Misalnya:\n— Cara daftar mitra Algrow Capital\n— Panduan buka akun Stockbit (sepaket Bank Jago) atau Ajaib\n— Modal Rp 0 & sistem bagi hasil profit IPO\n— Syarat pendaftaran mitra\n\nSilakan tanyakan langsung ya!";
            }

            // 4. Acknowledgment Santai (Oke, Sip, Mantap, Siap, Noted)
            $ackWords = [
                'oke', 'ok', 'sip', 'mantap', 'oke sip', 'siap min', 'siap kak', 'siap bro',
                'oke min', 'ok min', 'oke kak', 'ok kak', 'oke bro', 'ok bro',
                'baik', 'baik min', 'baik kak', 'baik bro', 'noted', 'paham', 'paham min',
                'ngerti', 'ngerti min', 'siap makasih', 'oke makasih'
            ];
            if (in_array($clean, $ackWords)) {
                return "Siap, mantap! 👍 Jika ada langkah yang kurang jelas atau kamu sudah siap lanjut mendaftar, kabari langsung di sini ya. Kami siap memandu sampai akun kemitraanmu aktif!";
            }
        }

        if ($clean === 'sudah') {
            return "Bagus sekali! 🎉 Berarti akun sekuritas dan RDN kamu sudah siap. Sekarang kamu bisa langsung melanjutkan ke proses pendaftaran kemitraan Algrow Capital:\n\n1️⃣ Klik tombol **Gabung Sekarang** di website ini\n2️⃣ Isi formulir data diri dan info akun sekuritas kamu\n3️⃣ Setelah kirim formulir, kamu otomatis diarahkan ke WhatsApp Admin (**087822421207**) untuk konfirmasi approval\n\nPendaftarannya 100% gratis dan seluruh modal pemesanan saham IPO disiapkan oleh Algrow Capital. Yuk langsung selesaikan pendaftarannya!";
        }

        // 1. Jawaban Opsi B (Daftar Bergabung sebagai Mitra Algrow Capital)
        // 'b' dan '2' tidak masuk array agar tidak false-match kalimat panjang
        $optionBPats = [
            'opsi b', 'pilih b', 'yang b', 'pilihan b', 'jawaban b', 'b aja', 'b dong',
            'b min', 'b kak', 'b mas', 'b bro', 'saya pilih b', 'mau yang b', 'mau b', 'ikut b',
            'nomor 2', 'no 2', 'opsi 2', 'pilih 2', 'yang 2',
            'daftar mitra', 'daftar jadi mitra', 'gabung mitra', 'gabung algrow', 'daftar algrow',
        ];
        // Single 'b' atau '2': hanya match jika query sangat pendek (<= 3 karakter)
        $isSingleB = ($clean === 'b' || $clean === '2') && strlen($clean) <= 3;
        if ($isSingleB || in_array($clean, $optionBPats) || preg_match('/^(saya\s+)?(pilih|mau|ikut|ambil)\s*(opsi|yang|nomor)?\s*b$/i', $clean)) {
            return "Mantap! Untuk mendaftar dan bergabung sebagai **Mitra Algrow Capital**, ikuti langkah-langkah berikut:\n\n1️⃣ **Klik Tombol Gabung Sekarang**: Tekan tombol **Gabung Sekarang / Daftar** di website ini.\n2️⃣ **Pilih Pendaftaran Mitra Baru**: Isi formulir pendaftaran kemitraan dengan data diri dan informasi akun sekuritas kamu (username, password, dan PIN sekuritas yang diperlukan untuk pemesanan IPO).\n3️⃣ **Konfirmasi ke WhatsApp Admin**: Setelah formulir terkirim, sistem akan otomatis mengarahkan kamu ke WhatsApp Admin resmi kami di nomor **087822421207** untuk verifikasi dan proses persetujuan (approval).\n\n*Program ini 100% tanpa modal pribadi (Rp 0) dan bebas biaya pendaftaran.* Pastikan akun sekuritasmu (Stockbit/Ajaib) sudah aktif ya!";
        }

        // 2. Jawaban Opsi A (Daftar / Buka Akun Sekuritas Stockbit atau Ajaib)
        // Hanya match jika query BENAR-BENAR hanya "a" (dengan alias), bukan kalimat panjang yang kebetulan mulai huruf a
        $optionAPats = [
            'opsi a', 'pilih a', 'yang a', 'pilihan a', 'jawaban a', 'a aja', 'a dong',
            'a min', 'a kak', 'a mas', 'a bro', 'saya pilih a', 'mau yang a', 'mau a', 'ikut a',
            'nomor 1', 'no 1', 'opsi 1', 'pilih 1', 'yang 1',
            'daftar sekuritas', 'buka sekuritas', 'buat sekuritas', 'buka akun sekuritas',
        ];
        // Single char 'a' atau '1': hanya match jika panjang query <= 3 karakter
        $isSingleA = ($clean === 'a' || $clean === '1') && strlen($clean) <= 3;
        if ($isSingleA || in_array($clean, $optionAPats) || preg_match('/^(saya\s+)?(pilih|mau|ikut|ambil)\s*(opsi|yang|nomor)?\s*a$/i', $clean)) {
            return "Siap! Untuk membuat dan membuka **akun sekuritas**, kamu **cukup pilih salah satu**: **Stockbit** atau **Ajaib** (tidak perlu daftar dua-duanya):\n\n📱 **PILIHAN 1: Stockbit (Wajib Sepaket dengan Bank Jago Reguler)**\nJika memilih Stockbit, kamu **wajib** memiliki rekening **Bank Jago reguler (warna kuning)**, bukan syariah, karena Bank Jago terintegrasi langsung sebagai RDN (Rekening Dana Nasabah):\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Stockbit** dan **Bank Jago** di Play Store atau App Store.\n2️⃣ **Buka Bank Jago Reguler**: Buat akun Bank Jago biasa sampai selesai dan aktif.\n3️⃣ **Buka Rekening Stockbit**: Siapkan **e-KTP fisik**, ambil selfie, lalu hubungkan RDN ke Bank Jago reguler kamu.\n4️⃣ **Tunggu Verifikasi**: Verifikasi sekuritas biasanya selesai dalam **1–24 jam kerja**.\n\n📱 **PILIHAN 2: Ajaib**\nJika memilih Ajaib, kamu cukup menyiapkan e-KTP dan rekening bank pribadi kamu:\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Ajaib** di Play Store atau App Store.\n2️⃣ **Registrasi**: Siapkan e-KTP fisik, foto selfie verifikasi, dan isi formulir data diri serta profil investor.\n3️⃣ **Tunggu Verifikasi**: Verifikasi sekuritas biasanya selesai dalam **1–24 jam kerja**.\n\n💡 *Pilih salah satu saja. Setelah akun sekuritas dan RDN kamu aktif, kamu langsung siap mendaftar ke kemitraan Algrow Capital!*";
        }

        // 3. Jawaban Konfirmasi "Sudah / Selesai / Aktif"
        $sudahPats = [
            'sudah', 'udah', 'sdh', 'udh', 'sudah aktif', 'udah aktif', 'sudah selesai', 'udah selesai',
            'sudah siap', 'udah siap', 'sudah ada', 'udah ada', 'sudah punya', 'udah punya',
            'sudah min', 'sudah kak', 'udah min', 'udah kak', 'sudah bro', 'udah bro',
        ];
        if (in_array($clean, $sudahPats)) {
            return "Bagus sekali! 🎉 Berarti akun sekuritas dan RDN kamu sudah siap. Sekarang kamu bisa langsung melanjutkan ke proses pendaftaran kemitraan Algrow Capital:\n\n1️⃣ Klik tombol **Gabung Sekarang** di website ini\n2️⃣ Isi formulir data diri dan info akun sekuritas kamu\n3️⃣ Setelah kirim formulir, kamu otomatis diarahkan ke WhatsApp Admin (**087822421207**) untuk konfirmasi approval\n\nPendaftarannya 100% gratis dan seluruh modal pemesanan saham IPO disiapkan oleh Algrow Capital. Yuk langsung selesaikan pendaftarannya!";
        }

        // 4. Jawaban Konfirmasi "Belum / Belum Aktif / Belum Selesai"
        $belumPats = [
            'belum', 'blm', 'lum', 'belum aktif', 'blm aktif', 'belum selesai', 'blm selesai',
            'belum min', 'belum kak', 'blm min', 'blm kak', 'belum bro', 'blm bro',
        ];
        if (in_array($clean, $belumPats)) {
            return "Tidak masalah, santai aja! Berarti proses pembukaan rekening sekuritasnya perlu diselesaikan terlebih dahulu ya. 👍\n\nApakah saat ini kamu **masih dalam proses verifikasi** (sedang menunggu 1–24 jam kerja dari sekuritas), atau **belum mengajukan pembukaan rekening sama sekali**?";
        }

        // 5. Jawaban Status "Masih Verifikasi / Menunggu"
        $verifPats = [
            'masih verifikasi', 'lagi verifikasi', 'masih proses', 'lagi proses', 'menunggu',
            'nunggu', 'pending', 'dalam proses', 'nunggu verifikasi', 'nunggu aktif',
            'masih nunggu', 'lagi nunggu',
        ];
        if (in_array($clean, $verifPats) || str_contains($clean, 'masih verifikasi') || str_contains($clean, 'nunggu verifikasi')) {
            return "Oke, berarti pengajuan rekening efek kamu sudah dikirim dan sedang dalam proses verifikasi pihak sekuritas. Biasanya proses ini memakan waktu **1–24 jam kerja**.\n\nSambil menunggu akun aktif, pastikan kamu juga sudah menyiapkan **Bank Jago reguler (kuning)** ya. Begitu akun sekuritasmu aktif, langsung kabarin di sini atau langsung daftar ke admin WhatsApp kami!";
        }

        // 6. Jawaban Status "Belum Mengajukan / Belum Daftar Sekuritas"
        $belumAjukanPats = [
            'belum mengajukan', 'belum ajukan', 'belum daftar', 'belum buka', 'belum punya',
            'blm punya', 'blm daftar', 'blm buka', 'belum bikin', 'blm bikin',
        ];
        if (in_array($clean, $belumAjukanPats)) {
            return "Kalau belum mengajukan pembukaan rekening efek, kamu cukup pilih **salah satu**: **Stockbit** atau **Ajaib** (tidak perlu daftar dua-duanya):\n\n— **Stockbit**: Wajib sepaket dengan **Bank Jago reguler (kuning)** sebagai rekening RDN, bukan Jago Syariah.\n— **Ajaib**: Cukup gunakan rekening bank pribadi kamu.\n\nUnduh aplikasinya di Play Store / App Store, siapkan e-KTP fisik, dan ikuti registrasi pembukaan rekening saham online (gratis & 100% online). Setelah akun disetujui (1–24 jam kerja), langsung kabarin ya agar kita bantu proses pendaftaran ke kemitraan Algrow Capital!";
        }

        // ─── CONTEXT AWARENESS: Deteksi apakah user SUDAH PUNYA akun ─────────────
        // Sinyal "sudah punya / sudah ada / sudah buka" akun sekuritas
        $alreadyHasSignals = [
            // Sudah punya / ada akun
            'sudah punya', 'sudah ada', 'udah punya', 'udah ada',
            'saya punya', 'saya ada', 'aku punya', 'aku ada',
            'sudah daftar', 'sudah register', 'sudah bikin', 'sudah buat',
            'udah daftar', 'udah register', 'udah bikin', 'udah buat',
            'punya stockbit', 'ada stockbit', 'punya ajaib', 'ada ajaib',
            'stockbit saya', 'ajaib saya', 'akun saya', 'rekening saya',
            'udah ada akun', 'udah punya akun', 'sudah ada akun', 'sudah punya akun',
            'akun sudah', 'sudah aktif', 'udah aktif',
            // Sudah buka rekening saham (Step 2 di Stockbit/Ajaib)
            'sudah buka rekening', 'udah buka rekening',
            'buka rekening sudah', 'buka rekening saham sudah',
            'sudah buka akun', 'udah buka akun',
            'sudah ajukan', 'sudah mengajukan', 'sudah submit',
            'sudah kirim data', 'sudah isi data', 'sudah upload',
            'sudah lengkapi', 'sudah selesai daftar',
            'rekening saham sudah dibuka', 'rekening efek sudah dibuka',
            // Frasa pendek yang menyatakan "sudah" diikuti buka/daftar
            'sudah buka', 'udah buka',
        ];

        // Deteksi khusus: "buka rekening saham" / "buka rekening efek" dengan konteks sudah
        // Misal: "udah buka rekening saham", "buka rekening sahamnya sudah"
        $openedRekeningPhrases = [
            'buka rekening saham', 'buka rekening efek', 'buka rekening di stockbit',
            'buka rekening di ajaib', 'buka rekening sahamnya', 'buka rekening efeknya',
            'pengajuan rekening', 'ajukan rekening', 'rekening saham diajukan',
        ];
        $userOpenedRekening = false;
        foreach ($openedRekeningPhrases as $phrase) {
            if (str_contains($q, $phrase)) {
                $userOpenedRekening = true;
                break;
            }
        }

        $userAlreadyHas = $userOpenedRekening;
        $mentionedPlatform = '';
        if (!$userAlreadyHas) {
            foreach ($alreadyHasSignals as $signal) {
                if (str_contains($q, $signal)) {
                    $userAlreadyHas = true;
                    break;
                }
            }
        }
        if (str_contains($q, 'stockbit') && str_contains($q, 'ajaib')) {
            $mentionedPlatform = ''; // Menyebutkan keduanya -> panduan umum komparasi / pilihan
        } elseif (str_contains($q, 'stockbit')) {
            $mentionedPlatform = 'Stockbit';
        } elseif (str_contains($q, 'ajaib')) {
            $mentionedPlatform = 'Ajaib';
        }

        // Sinyal rekening efek / RDN sudah aktif
        $rekeningAktifSignals = [
            'rekening sudah aktif', 'rekening efek aktif', 'rdn sudah aktif', 'rdn aktif',
            'sudah aktif semua', 'sudah lengkap', 'sudah siap', 'sudah bisa trading',
            'rekening saham aktif', 'rekening efek sudah', 'sudah verified', 'sudah terverifikasi',
        ];
        $rekeningAlreadyActive = false;
        foreach ($rekeningAktifSignals as $signal) {
            if (str_contains($q, $signal)) {
                $rekeningAlreadyActive = true;
                break;
            }
        }

        // Sinyal masih proses verifikasi
        $inVerificationSignals = [
            'masih proses', 'lagi proses', 'masih verifikasi', 'lagi verifikasi',
            'belum aktif', 'belum verified', 'belum terverifikasi', 'masih menunggu',
            'masih nunggu', 'pending', 'dalam proses',
        ];
        $inVerification = false;
        foreach ($inVerificationSignals as $signal) {
            if (str_contains($q, $signal)) {
                $inVerification = true;
                break;
            }
        }

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
                    'daftar algrow', 'gabung algrow', 'ikut algrow', 'mitra algrow', 'mitra', 'pendaftaran', 'buat akun', 'bikin akun', 'buka akun',
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
                    'rugi', 'resiko', 'risiko', 'rugi ga', 'rugi gak', 'tanggung rugi', 'kerugian',
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
                    'chat langsung', 'minta tolong', 'ada orang', 'grup', 'komunitas', 'channel',
                ],
                'context_boost' => ['wa', 'whatsapp', 'admin', 'hubungi'],
            ],


            // TOPIC J — Perlu Belajar Saham / Pengalaman / Pemula
            'belajar' => [
                'signals' => [
                    'belajar', 'belajar saham', 'belajar dulu', 'perlu belajar', 'harus belajar',
                    'dari nol', 'dari 0', 'dari awal', 'nol saham', 'nol pengalaman',
                    'pemula', 'newbie', 'tidak tau saham', 'tidak tahu saham', 'gak tau saham',
                    'ga tau saham', 'belum tau saham', 'belum tahu saham', 'belum pengalaman',
                    'tidak pengalaman', 'gak pengalaman', 'ga pengalaman',
                    'tidak mengerti saham', 'tidak paham saham', 'gak ngerti saham',
                    'ga ngerti saham', 'belum ngerti', 'belum paham saham',
                    'pengalaman', 'paham saham', 'ngerti saham', 'tau saham', 'tahu saham',
                    'harus tau', 'harus tahu', 'perlu tau', 'perlu tahu',
                    'wajib ngerti', 'wajib paham',
                    // Frasa "belum pernah beli/main/pegang saham"
                    'belum pernah beli saham', 'belum pernah main saham', 'belum pernah pegang saham',
                    'belum pernah investasi', 'belum pernah trading', 'belum pernah saham',
                    'tidak pernah beli saham', 'gak pernah beli saham', 'ga pernah beli saham',
                    'tidak pernah trading', 'gak pernah trading',
                ],
                'context_boost' => ['saham', 'ipo', 'trading', 'investasi', 'modal'],
            ],
            // TOPIC I — Greeting (Exact/near-exact match only)
            'greeting' => [
                'signals' => [
                    'halo', 'hai', 'hi', 'hey', 'hei', 'hello', 'selamat pagi', 'selamat siang',
                    'selamat sore', 'selamat malam', 'assalamualaikum', 'assalamu alaikum',
                    'permisi', 'tes', 'test', 'ping', 'apa kabar', 'hey bro', 'halo bro',
                ],
                'context_boost' => ['min', 'admin', 'kak', 'bro'],
            ],

            // TOPIC K — Terima Kasih / Gratitude
            'terima_kasih' => [
                'signals' => [
                    'terima kasih', 'terimakasih', 'makasih', 'makasi', 'thank you', 'thanks',
                    'thx', 'tengkyu', 'matur nuwun', 'nuhun', 'syukron', 'sukses selalu',
                ],
                'context_boost' => ['bantu', 'bantuan', 'info', 'infonya', 'admin', 'min', 'sangat membantu'],
            ],

            // TOPIC L — Keamanan Akun, Dana RDN & Legalitas (No. 3)
            'keamanan' => [
                'signals' => [
                    'aman', 'keamanan', 'apakah aman', 'jaminan', 'penipuan', 'nipu', 'bisa nipu',
                    'legal', 'resmi', 'ojk', 'ksei', 'izin', 'bocor', 'curi', 'diambil',
                    'data aman', 'pin aman', 'password aman', 'sandi aman', 'aman tidak', 'aman ga', 'aman gak',
                    'titip akun', 'uang diambil', 'saldo diambil', 'bisa ditarik orang', 'dana hilang',
                    'uang hilang', 'apakah aman akun', 'apakah aman data', 'keamanan rdn', 'rekening aman',
                    'bisa dipercaya', 'terpercaya',
                ],
                'context_boost' => ['akun', 'rdn', 'uang', 'data', 'sekuritas', 'rekening', 'ojk'],
            ],

            // TOPIC M — Kendala Registrasi & Troubleshooting (No. 4)
            'kendala' => [
                'signals' => [
                    'kendala', 'gagal', 'ditolak', 'masalah', 'error', 'ktp buram', 'ktp ditolak',
                    'ktp rusak', 'foto ktp', 'belum aktif', 'lewat 24 jam', 'sudah 24 jam', 'lama verifikasi',
                    'terlanjur', 'terlanjur syariah', 'terlanjur buat', 'salah bank', 'salah jago', 'jago syariah terlanjur',
                    'gagal daftar', 'gagal registrasi', 'gagal verifikasi', 'verifikasi ditolak', 'nama beda',
                    'rekening ditolak', 'bantuan teknis', 'kenapa ditolak', 'kenapa belum aktif',
                ],
                'context_boost' => ['ktp', 'verifikasi', 'jago', 'rekening', 'daftar', 'akun'],
            ],

            // TOPIC N — Persentase Bagi Hasil & Durasi Per Project IPO (No. 5)
            'durasi_hasil' => [
                'signals' => [
                    'berapa persen', 'persentase', 'persen bagi hasil', 'berapa bagian', 'rasio',
                    'skema bagi hasil', 'berapa lama', 'durasi', 'berapa hari', 'kapan cair',
                    'kapan ditransfer', 'kapan dapat untung', 'jangka waktu', 'siklus ipo',
                    'berapa minggu', 'kapan cairnya', 'kapan masuk rekening', 'berapa lama proses',
                    'durasi investasi', 'lama proses', 'waktu pencairan', 'kapan terima hasil',
                    'cair', 'kapan', 'pencairan', 'kapan dapat hasil',
                ],
                'context_boost' => ['profit', 'bagi hasil', 'cair', 'rekening', 'ipo', 'untung', 'lama'],
            ],

            // TOPIC O — Profil Perusahaan & Alamat Kantor Algrow Capital (No. 1)
            'profil' => [
                'signals' => [
                    'apa itu algrow', 'siapa algrow', 'tentang algrow', 'profil algrow', 'mengenal algrow',
                    'layanan algrow', 'algrow capital itu apa', 'siapa algrow capital', 'apa sih algrow',
                    'kantor', 'alamat', 'lokasi', 'alamat kantor', 'lokasi kantor', 'kantornya', 'kantor pusat',
                    'domisili', 'tempat kantor', 'kantor di mana', 'alamat di mana', 'kantornya di mana',
                    'perusahaan apa', 'bidang apa', 'bisnis apa', 'usaha apa', 'kantor fisik', 'kantor cabang',
                ],
                'context_boost' => ['algrow', 'capital', 'kantor', 'alamat', 'perusahaan', 'lokasi', 'profil'],
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
        // Jangan paksa sekuritas jika 'aplikasi' score lebih tinggi (untuk pertanyaan "pakai apa")
        $aplikasiWinsClearly = isset($scores['aplikasi']) && $scores['aplikasi'] > ($scores['sekuritas'] ?? 0);

        if (!$aplikasiWinsClearly) {
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
        }

        // ─── MODAL OVERRIDE sebelum AMBIGUITY CHECK ────────────────────────────
        // Jika ada sinyal modal/biaya/uang yang jelas → langsung ke modal, bypass ambiguity
        $hasModalSignal = str_contains($q, 'modal') || str_contains($q, 'biaya') ||
                          str_contains($q, 'uang') || str_contains($q, 'duit') ||
                          str_contains($q, 'keluar') || str_contains($q, 'bayar') ||
                          str_contains($q, 'gratis') || str_contains($q, 'rugi') ||
                          str_contains($q, 'risiko') || str_contains($q, 'resiko');
        if ($hasModalSignal && isset($scores['modal']) && $scores['modal'] >= 2) {
            $winner = 'modal';
            $winnerScore = $scores['modal'];
        }

        // ─── KEAMANAN OVERRIDE ─────────────────────────────────────────────────
        $hasKeamananSignal = str_contains($q, 'aman') || str_contains($q, 'keamanan') ||
                             str_contains($q, 'penipuan') || str_contains($q, 'nipu') ||
                             str_contains($q, 'legal') || str_contains($q, 'resmi') ||
                             str_contains($q, 'ojk') || str_contains($q, 'bocor') ||
                             str_contains($q, 'curi') || str_contains($q, 'diambil');
        if ($hasKeamananSignal && isset($scores['keamanan']) && $scores['keamanan'] >= 2) {
            $winner = 'keamanan';
            $winnerScore = $scores['keamanan'];
        }

        // ─── KENDALA OVERRIDE ──────────────────────────────────────────────────
        $hasKendalaSignal = str_contains($q, 'kendala') || str_contains($q, 'gagal') ||
                            str_contains($q, 'ditolak') || str_contains($q, 'buram') ||
                            str_contains($q, 'masalah') || str_contains($q, 'error') ||
                            str_contains($q, 'terlanjur');
        if ($hasKendalaSignal && isset($scores['kendala']) && $scores['kendala'] >= 2) {
            $winner = 'kendala';
            $winnerScore = $scores['kendala'];
        }

        // ─── DURASI & PERSENTASE HASIL OVERRIDE ────────────────────────────────
        $hasDurasiSignal = str_contains($q, 'persen') || str_contains($q, 'persentase') ||
                           str_contains($q, 'berapa lama') || str_contains($q, 'durasi') ||
                           str_contains($q, 'kapan cair') || str_contains($q, 'kapan ditransfer') ||
                           str_contains($q, 'berapa hari') || str_contains($q, 'berapa minggu');
        if ($hasDurasiSignal && isset($scores['durasi_hasil']) && $scores['durasi_hasil'] >= 2) {
            $winner = 'durasi_hasil';
            $winnerScore = $scores['durasi_hasil'];
        }

        // ─── PROFIL & KANTOR ALGROW OVERRIDE (No. 1) ───────────────────────────
        $hasProfilSignal = str_contains($q, 'apa itu algrow') || str_contains($q, 'siapa algrow') ||
                           str_contains($q, 'tentang algrow') || str_contains($q, 'profil') ||
                           str_contains($q, 'kantor') || str_contains($q, 'alamat') ||
                           str_contains($q, 'lokasi') || str_contains($q, 'perusahaan apa') ||
                           str_contains($q, 'kantornya') || str_contains($q, 'apa sih algrow');
        if ($hasProfilSignal && isset($scores['profil']) && $scores['profil'] >= 2) {
            $winner = 'profil';
            $winnerScore = $scores['profil'];
        }

        // ─── AMBIGUITY DETECTION ───────────────────────────────────────────────
        // Hanya berlaku untuk kata generik yang bisa berarti daftar akun ATAU daftar mitra
        // "buat akun", "bikin akun", "buka akun" DIHAPUS dari sini karena lebih sering = sekuritas

        $ambiguousKeywords = ['cara daftar', 'mau daftar', 'gimana daftar', 'panduan daftar', 'daftar dong', 'mau join', 'mau gabung'];
        // "daftar" saja hanya ambigu jika berdiri sendiri (panjang query pendek)
        if (strlen(trim($q)) <= 10 && str_contains($q, 'daftar')) {
            $isAmbiguousQuery = true;
        } else {
            $isAmbiguousQuery = false;
            foreach ($ambiguousKeywords as $kw) {
                if (str_contains($q, $kw)) {
                    $isAmbiguousQuery = true;
                    break;
                }
            }
        }

        // Clear disambiguators untuk sekuritas
        $hasSekuritasHint = str_contains($q, 'sekuritas') || str_contains($q, 'stockbit') ||
                            str_contains($q, 'ajaib') || str_contains($q, 'rdn') ||
                            str_contains($q, 'rekening saham') || str_contains($q, 'rekening dana') ||
                            str_contains($q, 'akun') || str_contains($q, 'aplikasi') ||
                            str_contains($q, 'panduan') || str_contains($q, 'cara');

        // Clear disambiguators untuk join/algrow
        $hasJoinHint = str_contains($q, 'algrow') || str_contains($q, 'mitra') ||
                       str_contains($q, 'gabung') || str_contains($q, 'join') ||
                       str_contains($q, 'program') || str_contains($q, 'komunitas');

        // Ambiguous: query benar-benar tidak ada konteks sama sekali
        if ($isAmbiguousQuery && !$hasSekuritasHint && !$hasJoinHint && !$hasModalSignal) {
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

        // Modal topic check: if question asks about modal, uang, biaya, rugi
        if (isset($scores['modal']) && $scores['modal'] >= 2) {
            if (str_contains($q, 'modal') || str_contains($q, 'uang') || str_contains($q, 'duit') || str_contains($q, 'biaya') || str_contains($q, 'rugi') || str_contains($q, 'resiko') || str_contains($q, 'risiko')) {
                if ($scores['modal'] >= $winnerScore || $winner === 'join' || $winner === 'syarat') {
                    $winner = 'modal';
                    $winnerScore = $scores['modal'];
                }
            }
        }

        // Keamanan topic check (No. 3)
        if (isset($scores['keamanan']) && $scores['keamanan'] >= 2) {
            if ($scores['keamanan'] >= $winnerScore || $winner === 'modal' || $winner === 'join' || $winner === 'syarat') {
                $winner = 'keamanan';
                $winnerScore = $scores['keamanan'];
            }
        }

        // Kendala & Troubleshooting check (No. 4)
        if (isset($scores['kendala']) && $scores['kendala'] >= 2) {
            if ($scores['kendala'] >= $winnerScore || $winner === 'sekuritas' || $winner === 'join' || $winner === 'jago' || str_contains($q, 'terlanjur') || str_contains($q, 'ditolak') || str_contains($q, 'buram')) {
                $winner = 'kendala';
                $winnerScore = $scores['kendala'];
            }
        }

        // Durasi & Persentase Bagi Hasil check (No. 5)
        if (isset($scores['durasi_hasil']) && $scores['durasi_hasil'] >= 2) {
            if ($scores['durasi_hasil'] >= $winnerScore || $winner === 'bagi_hasil' || $winner === 'join' || str_contains($q, 'kapan') || str_contains($q, 'cair') || str_contains($q, 'persen')) {
                $winner = 'durasi_hasil';
                $winnerScore = $scores['durasi_hasil'];
            }
        }

        // Profil & Kantor Algrow Capital check (No. 1)
        if (isset($scores['profil']) && $scores['profil'] >= 2) {
            if ($scores['profil'] >= $winnerScore || $winner === 'join' || str_contains($q, 'kantor') || str_contains($q, 'alamat') || str_contains($q, 'profil') || str_contains($q, 'apa itu')) {
                $winner = 'profil';
                $winnerScore = $scores['profil'];
            }
        }

        // Syarat topic override: if question explicitly says syarat/persyaratan, force syarat
        if (str_contains($q, 'syarat') || str_contains($q, 'persyaratan') || str_contains($q, 'ketentuan')) {
            if (isset($scores['syarat']) && $scores['syarat'] >= 2) {
                $winner = 'syarat';
                $winnerScore = $scores['syarat'];
            }
        }

        // Belajar topic override: pemula/belajar/belum pernah saham → prioritaskan belajar di atas join
        $hasBelajarSignal = str_contains($q, 'belajar') || str_contains($q, 'pemula') ||
                            str_contains($q, 'dari nol') || str_contains($q, 'dari 0') ||
                            str_contains($q, 'pengalaman') || str_contains($q, 'ngerti') ||
                            str_contains($q, 'paham saham') || str_contains($q, 'tau saham') ||
                            str_contains($q, 'pernah beli saham') || str_contains($q, 'pernah saham') ||
                            str_contains($q, 'pernah trading') || str_contains($q, 'pernah investasi');
        if ($hasBelajarSignal && isset($scores['belajar']) && $scores['belajar'] >= 2) {
            if ($winner === 'join' || $winner === 'syarat' || $winner === 'sekuritas' || $scores['belajar'] >= $winnerScore) {
                $winner = 'belajar';
                $winnerScore = $scores['belajar'];
            }
        }

        // No topic matched → guardrail
        if ($winnerScore < 2) {
            return $this->outOfScopeResponse();
        }

        // ─── RESPONSES (Varied format: mix of paragraphs, structured, short & punchy) ──

        // ── Context-Aware: Sekuritas response tergantung status user ──────────────
        if ($winner === 'sekuritas') {

            // Rekening efek sudah aktif penuh
            if ($rekeningAlreadyActive) {
                return "Bagus! Berarti rekening efek dan RDN-nya sudah aktif. Kamu sudah siap untuk melanjutkan ke proses pendaftaran mitra Algrow Capital. Klik tombol **Gabung Sekarang** di website, isi formulir, lalu kamu akan diarahkan ke WhatsApp Admin **087822421207** untuk konfirmasi dan approval.";
            }

            // User sudah buka rekening saham (submit form), tapi status belum jelas
            if ($userOpenedRekening) {
                $platform = $mentionedPlatform ?: 'Stockbit/Ajaib';
                if ($inVerification) {
                    return "Oke, berarti pengajuan rekening saham di **{$platform}** sudah dikirim dan lagi dalam proses verifikasi. Biasanya proses ini selesai dalam **1–24 jam kerja**. Kalau sudah aktif, langsung kabarin ya — kita lanjut ke pendaftaran mitra Algrow Capital! 👍";
                }
                return "Oke, berarti kamu sudah mengajukan pembukaan rekening saham di **{$platform}**. Sekarang tinggal ditunggu proses verifikasinya — biasanya selesai dalam **1–24 jam kerja**.\n\nApakah rekeningnya sudah aktif dan bisa digunakan, atau masih dalam proses verifikasi?";
            }

            // Masih proses verifikasi (tanpa info buka rekening eksplisit)
            if ($inVerification) {
                return "Oke, berarti rekening efek kamu masih dalam proses verifikasi. Biasanya proses ini selesai dalam **1–24 jam kerja**. Sambil menunggu, kamu bisa mulai pelajari program Algrow Capital lebih lanjut. Kalau sudah aktif, langsung kabarin ya — kita lanjut ke tahap pendaftaran mitra! 👍";
            }

            // User sudah punya akun aplikasi (tapi belum jelas status rekening efek)
            if ($userAlreadyHas && $mentionedPlatform) {
                return "Oke, berarti kamu sudah punya akun **{$mentionedPlatform}** ya. Sekarang perlu dipastikan satu hal — apakah pembukaan **rekening efek dan RDN**-nya sudah selesai dan akunnya sudah aktif, atau baru akun aplikasinya saja?";
            }
            if ($userAlreadyHas) {
                return "Oke, berarti kamu sudah punya akun sekuritasnya. Sekarang yang perlu dicek: apakah **rekening efek dan RDN**-nya sudah aktif juga? Akun aplikasi dan rekening efek itu dua hal berbeda — kalau baru akun aplikasinya, perlu selesaikan proses pembukaan rekening efek dulu.";
            }

            // Khusus jika user bertanya tentang Stockbit saja
            if ($mentionedPlatform === 'Stockbit') {
                return "Berikut panduan cara daftar akun sekuritas di **Stockbit** (wajib sepaket dengan **Bank Jago reguler**):\n\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Stockbit** dan **Bank Jago** (ikon warna kuning, bukan Jago Syariah) di Play Store atau App Store.\n2️⃣ **Buka Bank Jago Reguler**: Buat akun Bank Jago biasa sampai selesai verifikasi dan rekening aktif.\n3️⃣ **Registrasi Stockbit**: Buka Stockbit, daftar dengan nomor HP dan email aktif kamu.\n4️⃣ **Buka Rekening Efek & RDN**: Siapkan **e-KTP fisik**, ambil foto selfie verifikasi, lalu hubungkan ke rekening **Bank Jago reguler** kamu sebagai RDN (Rekening Dana Nasabah).\n5️⃣ **Verifikasi**: Tunggu approval sekuritas (biasanya 1–24 jam kerja).\n\nSetelah akun Stockbit & RDN Bank Jago aktif, akunmu langsung siap didaftarkan ke kemitraan Algrow Capital!";
            }

            // Khusus jika user bertanya tentang Ajaib saja
            if ($mentionedPlatform === 'Ajaib') {
                return "Berikut panduan cara daftar akun sekuritas di **Ajaib** secara online:\n\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Ajaib** di Play Store atau App Store.\n2️⃣ **Registrasi Akun**: Buka aplikasi, lalu daftar dengan nomor HP dan email aktif kamu.\n3️⃣ **Buka Rekening Saham**: Siapkan **e-KTP fisik**, ambil foto selfie verifikasi, dan lengkapi data diri serta profil investor.\n4️⃣ **Rekening Bank Penampung**: Masukkan nomor rekening bank pribadi kamu (atas nama sendiri).\n5️⃣ **Verifikasi**: Tunggu persetujuan pihak Ajaib Sekuritas (biasanya 1–24 jam kerja).\n\nSetelah rekening efek & RDN Ajaib aktif, akunmu langsung siap didaftarkan ke kemitraan Algrow Capital!";
            }

            // Default: user belum punya akun sama sekali
            return "Berikut panduan cara daftar akun sekuritas secara online (kamu **cukup pilih salah satu**: Stockbit **atau** Ajaib, tidak perlu daftar dua-duanya):\n\n📱 **PILIHAN 1: Stockbit (Wajib Sepaket dengan Bank Jago Reguler)**\nJika memilih Stockbit, kamu **wajib** memiliki rekening **Bank Jago reguler (warna kuning)**, bukan syariah, karena Bank Jago terintegrasi langsung sebagai RDN (Rekening Dana Nasabah):\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Stockbit** dan **Bank Jago** di Play Store atau App Store.\n2️⃣ **Buka Bank Jago Reguler**: Buat akun Bank Jago biasa sampai aktif.\n3️⃣ **Buka Rekening Stockbit**: Registrasi di Stockbit, siapkan **e-KTP fisik**, lalu hubungkan RDN ke Bank Jago reguler kamu.\n4️⃣ **Tunggu Verifikasi**: Verifikasi sekuritas biasanya selesai dalam **1–24 jam kerja**.\n\n📱 **PILIHAN 2: Ajaib**\nJika memilih Ajaib, kamu cukup menyiapkan e-KTP dan rekening bank pribadi kamu:\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Ajaib** di Play Store atau App Store.\n2️⃣ **Registrasi**: Siapkan e-KTP fisik, foto selfie verifikasi, dan isi formulir pembukaan rekening saham.\n3️⃣ **Tunggu Verifikasi**: Verifikasi sekuritas biasanya selesai dalam **1–24 jam kerja**.\n\n💡 *Penting: Kamu cukup mendaftar di salah satu platform saja. Setelah akun sekuritas dan RDN kamu aktif, akun langsung siap didaftarkan ke program kemitraan Algrow Capital!*";
        }

        return match($winner) {
            'other_sekuritas' => "Pertanyaan bagus! Sekuritas seperti IPOT, Mirae Asset, Mandiri Sekuritas (MOST), BNI Sekuritas (BIONS), hingga BCA Sekuritas memang besar dan terpercaya di pasar modal Indonesia.\n\nTapi untuk program kemitraan IPO di Algrow Capital, saat ini kami hanya mendukung **Stockbit** dan **Ajaib**. Alasannya simpel — dua platform ini 100% online (tanpa berkas fisik), tanpa biaya admin RDN bulanan, verifikasi cepat, dan paling mulus diintegrasikan ke sistem alokasi IPO kolektif kami. Sekuritas lain punya alur teknis berbeda yang belum kami support. Ke depannya mungkin bisa berkembang, tapi untuk sekarang tetap pakai Stockbit atau Ajaib ya!",

            'jago' => "⚠️ **Perhatian penting soal Bank Jago!**\n\nBank Jago punya **2 versi aplikasi** yang beda:\n— **Jago Biasa** → ikon **kuning** ✅ (ini yang dipakai)\n— **Jago Syariah** → ikon **hijau** ❌ (tidak kompatibel)\n\nPastikan kamu download dan daftar **Jago yang ikonnya kuning (reguler)**, bukan yang syariah/hijau. Kalau salah pilih, akun tidak bisa digunakan untuk program kemitraan IPO ini.",

            'modal' => "Tenang, **kamu tidak perlu keluar modal sama sekali — Rp 0.** 🎉\n\nSeluruh dana untuk pemesanan saham IPO disiapkan 100% oleh Algrow Capital. Kamu cuma perlu menyediakan akun sekuritas (Stockbit/Ajaib) yang aktif dan terverifikasi. Setelah saham listing dan dijual di harga terbaik, profit bersihnya langsung dibagi hasil ke rekening kamu secara transparan. Tidak ada risiko kehilangan uang pribadi sama sekali.",

            'join' => "Untuk panduan buat akun dan bergabung di **Algrow Capital**, alurnya terdiri dari 2 langkah mudah:\n\n1️⃣ **Buat Akun Sekuritas (Pilih Salah Satu: Stockbit atau Ajaib)**\nKamu cukup mendaftar di salah satu platform saja:\n— **Stockbit**: Wajib sepaket dengan **Bank Jago reguler (kuning)** sebagai rekening RDN, bukan Jago Syariah.\n— **Ajaib**: Bisa menggunakan rekening bank pribadi kamu.\nDownload aplikasinya di Play Store / App Store, siapkan e-KTP fisik, dan ikuti alur pembukaan rekening efek & RDN (gratis, verifikasi 1–24 jam).\n\n2️⃣ **Daftar Kemitraan Algrow Capital**\nSetelah akun sekuritas aktif, klik tombol **Gabung Sekarang** di website ini, lengkapi formulir pendaftaran Mitra Baru, lalu kamu otomatis diarahkan ke WhatsApp Admin (**087822421207**) untuk verifikasi data & approval.\n\n*Seluruh proses pendaftaran dan program ini 100% gratis tanpa modal uang pribadi.* Jika kamu sudah punya akun Stockbit/Ajaib yang aktif, bisa langsung lanjut ke langkah ke-2!",

            'sekuritas' => "Berikut panduan cara daftar akun sekuritas secara online (kamu **cukup pilih salah satu**: Stockbit **atau** Ajaib, tidak perlu daftar dua-duanya):\n\n📱 **PILIHAN 1: Stockbit (Wajib Sepaket dengan Bank Jago Reguler)**\nJika memilih Stockbit, kamu **wajib** memiliki rekening **Bank Jago reguler (warna kuning)**, bukan syariah, karena Bank Jago terintegrasi langsung sebagai RDN (Rekening Dana Nasabah):\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Stockbit** dan **Bank Jago** di Play Store atau App Store.\n2️⃣ **Buka Bank Jago Reguler**: Buat akun Bank Jago biasa sampai aktif.\n3️⃣ **Buka Rekening Stockbit**: Registrasi di Stockbit, siapkan **e-KTP fisik**, lalu hubungkan RDN ke Bank Jago reguler kamu.\n4️⃣ **Tunggu Verifikasi**: Verifikasi sekuritas biasanya selesai dalam **1–24 jam kerja**.\n\n📱 **PILIHAN 2: Ajaib**\nJika memilih Ajaib, kamu cukup menyiapkan e-KTP dan rekening bank pribadi kamu:\n1️⃣ **Download Aplikasi**: Unduh aplikasi **Ajaib** di Play Store atau App Store.\n2️⃣ **Registrasi**: Siapkan e-KTP fisik, foto selfie verifikasi, dan isi formulir pembukaan rekening saham.\n3️⃣ **Tunggu Verifikasi**: Verifikasi sekuritas biasanya selesai dalam **1–24 jam kerja**.\n\n💡 *Penting: Kamu cukup mendaftar di salah satu platform saja. Setelah akun sekuritas dan RDN kamu aktif, akun langsung siap didaftarkan ke program kemitraan Algrow Capital!*",

            'aplikasi' => "Algrow Capital saat ini resmi menggunakan **2 platform sekuritas**:\n\n📱 **Stockbit** — PT Stockbit Sekuritas Digital\n📱 **Ajaib** — PT Ajaib Sekuritas Asia\n\nKeduanya terdaftar & diawasi OJK dan IDX, tersedia di Android dan iOS, pendaftaran 100% online, dan mendukung fitur e-IPO untuk pemesanan saham perdana. Untuk rekening penampung transaksi, pastikan menggunakan **Bank Jago reguler (kuning)** ya, bukan Jago Syariah.",

            'syarat' => "Syarat jadi mitra Algrow Capital simpel banget:\n\n✅ WNI minimal **17 tahun** + punya e-KTP\n✅ Akun sekuritas aktif di **Stockbit atau Ajaib**\n✅ Rekening bank pribadi atas nama sendiri\n✅ WhatsApp & email aktif\n\nSudah punya semua? Langsung daftar aja sekarang!",

            'bagi_hasil' => "Begini cara kerjanya:\n\nSetelah akun sekuritas kamu terdaftar dan disetujui, Algrow Capital menyiapkan **100% modal** untuk memesan saham IPO potensial melalui akun kamu. Ketika saham resmi listing di bursa dan mencapai harga optimal, saham dijual dan **profit bersihnya langsung ditransfer ke rekening bank kamu** sesuai rasio bagi hasil yang sudah disepakati — tanpa potongan tersembunyi, tanpa risiko modal pribadi.",

            'keamanan' => "Akun dan dana kamu **dijamin 100% aman** karena ekosistem pasar modal Indonesia diawasi ketat oleh regulasi resmi:\n\n🔒 **1. Dana RDN Tidak Bisa Ditarik Siapapun**\nRekening Dana Nasabah (RDN) dibuat atas nama e-KTP kamu sendiri. Sesuai regulasi OJK & KSEI, penarikan dana dari RDN **HANYA BISA dicairkan ke rekening bank pribadi milikmu sendiri**. Pihak manapun (termasuk Algrow Capital) tidak bisa mentransfer dana keluar ke rekening pihak lain.\n\n🔒 **2. Broker Resmi Berizin OJK & IDX**\nPlatform yang digunakan (**Stockbit & Ajaib**) adalah perusahaan sekuritas legal yang terdaftar dan diawasi langsung oleh **OJK (Otoritas Jasa Keuangan)** serta **IDX (Bursa Efek Indonesia)**.\n\n🔒 **3. Penggunaan Akun Khusus Transaksi IPO**\nAkses akun hanya digunakan tim profesional Algrow Capital untuk memesan saham e-IPO dan mengeksekusi penjualan saat saham listing di bursa.\n\n*Kamu memegang kendali penuh atas akun bank pribadimu, dan seluruh modal disiapkan 100% oleh Algrow Capital (Rp 0 modal pribadi).* 👍",

            'kendala' => "Jika kamu mengalami kendala saat proses pendaftaran sekuritas, berikut panduan solusinya:\n\n📸 **1. Foto e-KTP Ditolak / Buram**\n— Gunakan fisik e-KTP asli (bukan fotokopi/scan/file PDF).\n— Foto di tempat dengan pencahayaan terang dan pastikan tidak ada pantulan cahaya (flash) yang menutupi NIK atau foto wajah.\n— Pastikan seluruh sudut e-KTP masuk penuh ke dalam frame kamera.\n\n⏳ **2. Verifikasi Belum Selesai (> 24 Jam Kerja)**\n— Verifikasi sekuritas diproses pada **hari & jam kerja bursa (Senin–Jumat)**. Pendaftaran di akhir pekan akan diproses hari kerja berikutnya.\n— Cek kotak masuk atau folder spam email kamu untuk melihat apakah ada notifikasi perbaikan data dari pihak sekuritas.\n\n🟡 **3. Terlanjur Buka Bank Jago Syariah**\n— Bank Jago Syariah tidak bisa digunakan sebagai RDN Stockbit.\n— Solusinya: Kamu bisa mendaftar akun **Bank Jago Reguler (ikon kuning)**, atau beralih mendaftar di **Ajaib** yang mendukung rekening bank umum lainnya.\n\n📲 Butuh panduan teknis langsung? Chat WhatsApp Admin kami di **087822421207** agar langsung dibantu tim kami!",

            'durasi_hasil' => "Berikut detail mengenai **sistem bagi hasil dan durasi per project IPO** di Algrow Capital:\n\n⏱️ **1. Durasi Per Siklus IPO (3–7 Hari Kerja)**\nProses pemesanan saham perdana (IPO) berlangsung cepat dan dinamis:\n— **Masa Penawaran (Offering)**: 2–3 hari kerja\n— **Penjatahan (Allotment)**: 1–2 hari kerja\n— **Listing & Penjualan**: Saham langsung dieksekusi jual di harga optimal pada hari pertama resmi listing di Bursa Efek Indonesia (BEI).\n\n💰 **2. Pembagian Hasil & Pencairan Profit**\n— Setelah saham berhasil dijual dan menghasilkan profit bersih, keuntungan langsung dibagi sesuai rasio bagi hasil yang disepakati bersama secara transparan.\n— Uang profit bagian kamu langsung ditransfer ke rekening bank pribadi kamu pada **hari yang sama atau maksimal H+1** setelah transaksi penjualan selesai.\n\n🛡️ **3. 100% Tanpa Risiko Kerugian Modal Pribadi**\nSeluruh dana pemesanan disiapkan oleh Algrow Capital. Jika ada fluktuasi harga pasar saham, risiko modal ditanggung oleh Algrow — saldo dan uang pribadi kamu tidak akan pernah berkurang!",

            'kontak' => "Butuh bantuan langsung? 👋\n\n📲 **WhatsApp Admin:** 087822421207\n\nTim kami siap memandu dari nol sampai akun kemitraan kamu aktif!",

            'profil' => "**Algrow Capital** adalah platform kemitraan investasi saham perdana (e-IPO) di Indonesia yang menghubungkan masyarakat pemilik akun sekuritas dengan tim analis pasar modal profesional:\n\n🏢 **Profil & Layanan Kami**\nAlgrow Capital membantu masyarakat meraih peluang profit dari momentum penawaran umum saham perdana (e-IPO) di Bursa Efek Indonesia (BEI) **tanpa perlu keluar modal uang pribadi (Rp 0)**. Seluruh dana pemesanan saham IPO disiapkan oleh Algrow Capital melalui akun sekuritas resmi milik Mitra (Stockbit / Ajaib).\n\n📍 **Lokasi & Operasional Kantor**\nOperasional Algrow Capital berbasis di Indonesia dan melayani mitra di seluruh wilayah Indonesia secara **100% online**. Seluruh interaksi, konsultasi, dan pendaftaran resmi dilakukan secara terpusat melalui website resmi kami (https://algrowcapital.vercel.app/) serta layanan WhatsApp Admin di **087822421207**.\n\n*Jika ingin berkonsultasi langsung atau bertanya seputar kemitraan, silakan hubungi WhatsApp Admin kami ya!*",


            'belajar' => "Kabar baiknya — **kamu sama sekali tidak perlu pengalaman saham atau belajar dari nol!** 🎉\n\nAlgrow Capital sepenuhnya mengelola proses analisis, pemilihan saham IPO, dan pemesanan. Tugasmu hanya:\n\n✅ Menyediakan akun sekuritas aktif (Stockbit/Ajaib)\n✅ Memberikan akses untuk proses pemesanan IPO\n✅ Menerima hasil profit yang sudah dibagi sesuai kesepakatan\n\nTim Algrow Capital yang berpengalaman di bidang pasar modal yang mengurus semuanya — dari riset IPO, pemesanan, hingga penjualan saham di harga terbaik. Jadi meski kamu belum pernah beli saham sekalipun, **kamu tetap bisa ikut program ini!**",

            'terima_kasih' => "Sama-sama! Senang bisa membantu kamu. 😊\n\nJika masih ada yang ingin ditanyakan seputar pendaftaran mitra, akun sekuritas (Stockbit/Ajaib), atau mekanisme bagi hasil IPO Algrow Capital, jangan ragu tanyakan lagi ya. Kamu juga bisa chat WhatsApp Admin resmi kami di **087822421207**.\n\nSukses selalu untuk investasimu! 🚀",

            'greeting' => "Halo! 👋 Selamat datang di **Algrow Capital**!\n\nAda yang bisa saya bantu hari ini? Misalnya:\n— Cara gabung / daftar mitra\n— Panduan buka akun sekuritas (Stockbit/Ajaib)\n— Sistem bagi hasil & modal Rp 0\n— Syarat kemitraan\n\nSilakan tanyakan langsung ya!",

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
        return "Hm, boleh saya tanyakan dulu nih biar bisa bantu dengan tepat 😊\n\nMaksudnya **\"daftar\"** di sini yang mana:\n\n**A.** Daftar / buka **akun sekuritas** (Stockbit atau Ajaib) — untuk kamu yang belum punya rekening saham\n**B.** Daftar **bergabung sebagai Mitra** ke program Algrow Capital — untuk kamu yang akun sekuritasnya sudah aktif\n\n💡 *Catatan penting: Jika ingin mendaftar sebagai Mitra (opsi B), kamu memang wajib mendahulukan pembukaan akun sekuritas & RDN terlebih dahulu (opsi A) karena akun sekuritas aktif diperlukan saat pendaftaran kemitraan.*\n\nKira-kira kondisi kamu sekarang ada di **A** atau **B**? Tulis aja ya!";
    }
}
