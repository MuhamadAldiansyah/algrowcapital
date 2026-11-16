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
     * High-Precision Local Knowledge Base Guardrail Engine.
     * Ensures instant, deterministic, and accurate responses even without third-party API keys.
     */
    private function strictKnowledgeEngine(string $text): string
    {
        $q = strtolower(trim($text));

        // 1. CARA DAFTAR SEKURITAS (STOCKBIT / AJAIB) - Evaluasi sebelum pendaftaran umum
        if (
            str_contains($q, 'daftar sekuritas') ||
            str_contains($q, 'daftar stockbit') ||
            str_contains($q, 'daftar ajaib') ||
            str_contains($q, 'bikin akun sekuritas') ||
            str_contains($q, 'buka rekening sekuritas') ||
            str_contains($q, 'cara bikin stockbit') ||
            str_contains($q, 'cara bikin ajaib') ||
            str_contains($q, 'registrasi sekuritas') ||
            (str_contains($q, 'stockbit') && (str_contains($q, 'daftar') || str_contains($q, 'bikin') || str_contains($q, 'buat'))) ||
            (str_contains($q, 'ajaib') && (str_contains($q, 'daftar') || str_contains($q, 'bikin') || str_contains($q, 'buat')))
        ) {
            return "📝 **Panduan Cara Daftar Akun Sekuritas (Stockbit / Ajaib):**\n\n" .
                   "1️⃣ **Download Aplikasi:** Unduh **Stockbit** atau **Ajaib** di Google Play Store atau App Store.\n" .
                   "2️⃣ **Siapkan Dokumen:** e-KTP asli dan nomor rekening bank pribadi (BCA/Mandiri/BRI/dll).\n" .
                   "3️⃣ **Isi Data Diri:** Buka aplikasi, pilih 'Buka Rekening Saham', isi form identitas dan foto selfie bersama e-KTP.\n" .
                   "4️⃣ **Pembuatan RDN (Rekening Dana Nasabah):** Pilih bank RDN yang tersedia di aplikasi (gratis, tanpa saldo minimum).\n" .
                   "5️⃣ **Tunggu Verifikasi:** Pihak sekuritas akan memverifikasi akun Anda dalam 1–24 jam kerja.\n" .
                   "6️⃣ **Selesai!** Setelah akun aktif dan RDN jadi, akun sekuritas Anda siap ditautkan ke Algrow Capital.";
        }

        // 2. CARA JOIN / CARA DAFTAR KE ALGROW
        if (
            str_contains($q, 'cara join') ||
            str_contains($q, 'cara daftar') ||
            str_contains($q, 'cara gabung') ||
            str_contains($q, 'gimana join') ||
            str_contains($q, 'gimana daftar') ||
            str_contains($q, 'langkah daftar') ||
            str_contains($q, 'mau join') ||
            str_contains($q, 'mau daftar') ||
            str_contains($q, 'join ke algrow') ||
            str_contains($q, 'daftar algrow')
        ) {
            return "🚀 **Cara Bergabung ke Algrow Capital sangat mudah:**\n\n" .
                   "1️⃣ Klik tombol **[Gabung Sekarang]** di pojok kanan atas website ini.\n" .
                   "2️⃣ Pilih peran Anda sebagai **Mitra** (jika ingin bekerjasama menggunakan akun sekuritas) atau **Asset Manager** (jika mengelola grup investor).\n" .
                   "3️⃣ Pilih nama perusahaan induk (**Algrow Capital**) dan tautkan nama akun mitra Anda.\n" .
                   "4️⃣ Masukkan email aktif dan buat kata sandi akun Anda.\n" .
                   "5️⃣ Masukkan kode OTP verifikasi yang dikirim ke email.\n" .
                   "6️⃣ Akun Anda langsung aktif dan siap dipasangkan dengan modal alokasi saham IPO!\n\n" .
                   "💡 *Pendaftaran 100% gratis tanpa dipungut biaya apapun.*";
        }

        // 3. PAKE APLIKASI APA / SEKURITAS APA
        if (
            str_contains($q, 'pake aplikasi') ||
            str_contains($q, 'pakai aplikasi') ||
            str_contains($q, 'aplikasi apa') ||
            str_contains($q, 'sekuritas apa') ||
            str_contains($q, 'platform apa') ||
            str_contains($q, 'broker apa') ||
            str_contains($q, 'apk apa') ||
            str_contains($q, 'download apa') ||
            str_contains($q, 'aplikasinya apa') ||
            str_contains($q, 'menggunakan apa')
        ) {
            return "📱 **Aplikasi Sekuritas yang Digunakan di Algrow Capital:**\n\n" .
                   "Saat ini Algrow Capital resmi mendukung 2 aplikasi sekuritas terpercaya yang terdaftar di **OJK & IDX**:\n\n" .
                   "1. **Stockbit** (PT Stockbit Sekuritas Digital)\n" .
                   "2. **Ajaib** (PT Ajaib Sekuritas Asia)\n\n" .
                   "Kedua aplikasi ini sangat mudah digunakan di HP (Android & iOS), pendaftarannya 100% online, dan memiliki fitur pemesanan saham e-IPO yang cepat.";
        }

        // 4. SOAL MODAL (APAKAH BUTUH MODAL SENDIRI / KELUAR UANG)
        if (
            str_contains($q, 'modal') ||
            str_contains($q, 'biaya') ||
            str_contains($q, 'bayar') ||
            str_contains($q, 'keluar uang') ||
            str_contains($q, 'setor') ||
            str_contains($q, 'depo') ||
            str_contains($q, 'deposit') ||
            str_contains($q, 'gratis') ||
            str_contains($q, 'tarif')
        ) {
            return "💰 **Apakah Butuh Modal Sendiri?**\n\n" .
                   "**SAMA SEKALI TIDAK! (Modal Rp 0)** 🎉\n\n" .
                   "Di Algrow Capital, konsepnya adalah kemitraan investasi saham tanpa modal:\n" .
                   "• **100% Modal Penjatahan Saham IPO disiapkan oleh Algrow Capital.**\n" .
                   "• Anda sebagai Mitra **hanya menyediakan akun sekuritas** (Stockbit/Ajaib).\n" .
                   "• Anda tidak perlu menyetorkan uang sepeserpun.\n" .
                   "• Ketika saham IPO listing dan menghasilkan keuntungan, profit bersih langsung dibagi hasil secara transparan!";
        }

        // 5. SYARAT JADI MITRA
        if (
            str_contains($q, 'syarat') ||
            str_contains($q, 'persyaratan') ||
            str_contains($q, 'ketentuan') ||
            str_contains($q, 'kriteria') ||
            str_contains($q, 'dokumen')
        ) {
            return "📋 **Persyaratan Menjadi Mitra Algrow Capital:**\n\n" .
                   "1. Warga Negara Indonesia (WNI) berusia minimal 17 tahun dan memiliki e-KTP.\n" .
                   "2. Memiliki akun sekuritas aktif di **Stockbit** atau **Ajaib** yang sudah terverifikasi.\n" .
                   "3. Memiliki nomor rekening bank pribadi yang valid untuk penerimaan bagi hasil profit.\n" .
                   "4. Memiliki email & nomor WhatsApp aktif untuk menerima notifikasi alokasi saham IPO.\n\n" .
                   "Jika syarat di atas sudah lengkap, Anda bisa langsung mendaftar sekarang juga!";
        }

        // 6. CARA KERJA & BAGI HASIL
        if (
            str_contains($q, 'bagi hasil') ||
            str_contains($q, 'profit') ||
            str_contains($q, 'cara kerja') ||
            str_contains($q, 'gimana kerjanya') ||
            str_contains($q, 'untung') ||
            str_contains($q, 'keuntungan') ||
            str_contains($q, 'sistem kerja')
        ) {
            return "🤝 **Cara Kerja & Sistem Bagi Hasil di Algrow Capital:**\n\n" .
                   "1. **Pendaftaran:** Anda mendaftar sebagai Mitra dan mendaftarkan akun sekuritas (Stockbit/Ajaib).\n" .
                   "2. **Penyediaan Dana:** Algrow Capital menyiapkan 100% modal untuk pemesanan saham IPO melalui akun sekuritas Anda.\n" .
                   "3. **Penjatahan Saham:** Saat masa listing IPO saham berlangsung, pesanan saham dieksekusi oleh sistem.\n" .
                   "4. **Realisasi Profit:** Saham dijual pada momentum optimal kenaikan harga.\n" .
                   "5. **Pencairan:** Keuntungan bersih langsung dibagikan ke rekening pribadi Anda sesuai rasio kesepakatan mitra.";
        }

        // 7. GREETINGS (Halo, Hai, Pagi, Siang, dll)
        if (
            $q === 'halo' || $q === 'hai' || $q === 'hi' || $q === 'hey' ||
            str_contains($q, 'selamat pagi') || str_contains($q, 'selamat siang') ||
            str_contains($q, 'selamat sore') || str_contains($q, 'selamat malam') ||
            str_contains($q, 'assalamualaikum') || $q === 'p' || $q === 'tes'
        ) {
            return "Halo! 👋 Selamat datang di **Algrow Capital**.\n\n" .
                   "Saya adalah asisten virtual khusus yang siap memandu Anda seputar:\n" .
                   "• **Cara bergabung / join ke Algrow Capital**\n" .
                   "• **Aplikasi sekuritas apa yang digunakan** (Stockbit & Ajaib)\n" .
                   "• **Panduan pendaftaran akun sekuritas**\n" .
                   "• **Sistem investasi saham IPO tanpa modal**\n\n" .
                   "Ada yang ingin ditanyakan seputar pendaftaran, Kak?";
        }

        // 8. HUBUNGI ADMIN / CS
        if (
            str_contains($q, 'admin') ||
            str_contains($q, 'cs') ||
            str_contains($q, 'whatsapp') ||
            str_contains($q, 'wa') ||
            str_contains($q, 'kontak') ||
            str_contains($q, 'nomor')
        ) {
            return "📞 **Hubungi Tim Admin & Support Algrow Capital:**\n\n" .
                   "Jika butuh bantuan langsung atau pendampingan saat mendaftar, tim kami siap membantu melalui WhatsApp:\n\n" .
                   "👉 **WhatsApp Support:** `087822421207`\n\n" .
                   "Atau Anda bisa langsung mengklik tombol **[Gabung Sekarang]** untuk registrasi instan.";
        }

        // 9. STRICT GUARDRAIL - OUT OF SCOPE REJECTION
        return "Mohon maaf ya, saya adalah **Asisten Khusus Pendaftaran Algrow Capital**. 🙏\n\n" .
               "Lingkup jawaban saya dikhususkan untuk menjawab pertanyaan seputar:\n" .
               "1️⃣ **Cara join & mendaftar ke Algrow Capital**\n" .
               "2️⃣ **Aplikasi sekuritas yang dipakai** (*Stockbit & Ajaib*)\n" .
               "3️⃣ **Panduan pendaftaran akun sekuritas & RDN**\n" .
               "4️⃣ **Syarat kemitraan & sistem bagi hasil tanpa modal**\n\n" .
               "Silakan tanyakan hal seputar pendaftaran di atas, saya akan bantu jelaskan dengan senang hati! 😊";
    }
}
