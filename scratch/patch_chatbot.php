<?php
/**
 * Patch script: Fix 2 bugs in ChatbotController.php
 * Bug 1: 'b'/'a' single-char in optionBPats/optionAPats causes false matches for longer queries
 * Bug 2: "belajar dari nol / apakah harus belajar saham" → no topic matches
 */

$file = __DIR__ . '/../app/Http/Controllers/ChatbotController.php';
$content = file_get_contents($file);

// ─── FIX 1A: Remove bare 'b' and '2' from optionBPats array ───────────────
// Before: 'b', 'opsi b', ...  '2', 'nomor 2', ...
// After: (no bare 'b' or '2') + add $isSingleB check on the if() line

$content = str_replace(
    "        // 1. Jawaban Opsi B (Daftar Bergabung sebagai Mitra Algrow Capital)\n        \$optionBPats = [\n            'b', 'opsi b', 'pilih b', 'yang b', 'pilihan b', 'jawaban b', 'b aja', 'b dong',\n            'b min', 'b kak', 'b mas', 'b bro', 'saya pilih b', 'mau yang b', 'mau b', 'ikut b',\n            '2', 'nomor 2', 'no 2', 'opsi 2', 'pilih 2', 'yang 2',\n            'daftar mitra', 'daftar jadi mitra', 'gabung mitra', 'gabung algrow', 'daftar algrow',\n        ];\n        if (in_array(\$clean, \$optionBPats) || preg_match('/^(saya\\s+)?(pilih|mau|ikut|ambil)?\\s*(opsi|yang|nomor)?\\s*b$/i', \$clean)) {",

    "        // 1. Jawaban Opsi B (Daftar Bergabung sebagai Mitra Algrow Capital)\n        // 'b' dan '2' tidak masuk array agar tidak false-match kalimat panjang\n        \$optionBPats = [\n            'opsi b', 'pilih b', 'yang b', 'pilihan b', 'jawaban b', 'b aja', 'b dong',\n            'b min', 'b kak', 'b mas', 'b bro', 'saya pilih b', 'mau yang b', 'mau b', 'ikut b',\n            'nomor 2', 'no 2', 'opsi 2', 'pilih 2', 'yang 2',\n            'daftar mitra', 'daftar jadi mitra', 'gabung mitra', 'gabung algrow', 'daftar algrow',\n        ];\n        // Single 'b' atau '2': hanya match jika query sangat pendek (<= 3 karakter)\n        \$isSingleB = (\$clean === 'b' || \$clean === '2') && strlen(\$clean) <= 3;\n        if (\$isSingleB || in_array(\$clean, \$optionBPats) || preg_match('/^(saya\\s+)?(pilih|mau|ikut|ambil)\\s*(opsi|yang|nomor)?\\s*b$/i', \$clean)) {",
    $content
);

// ─── FIX 1B: Remove bare 'a'/'1' from sudah/early return; guarded in $isSingleA ───
// The optionAPats block was already patched (no bare 'a'/'1' + $isSingleA guard)
// Verify it still has the isSingleA logic, if not, the below handles it
if (!str_contains($content, '$isSingleA')) {
    $content = str_replace(
        "        // 2. Jawaban Opsi A (Daftar / Buka Akun Sekuritas Stockbit atau Ajaib)\n        // Hanya match jika query BENAR-BENAR hanya \"a\" (dengan alias), bukan kalimat panjang yang kebetulan mulai huruf a\n        \$optionAPats = [\n            'opsi a', 'pilih a', 'yang a', 'pilihan a', 'jawaban a', 'a aja', 'a dong',\n            'a min', 'a kak', 'a mas', 'a bro', 'saya pilih a', 'mau yang a', 'mau a', 'ikut a',\n            'nomor 1', 'no 1', 'opsi 1', 'pilih 1', 'yang 1',\n            'daftar sekuritas', 'buka sekuritas', 'buat sekuritas', 'buka akun sekuritas',\n        ];\n        // Single char 'a' atau '1': hanya match jika panjang query <= 3 karakter\n        \$isSingleA = (\$clean === 'a' || \$clean === '1') && strlen(\$clean) <= 3;\n        if (\$isSingleA || in_array(\$clean, \$optionAPats) || preg_match('/^(saya\\s+)?(pilih|mau|ikut|ambil)\\s*(opsi|yang|nomor)?\\s*a$/i', \$clean)) {",
        "        // 2. Jawaban Opsi A (Daftar / Buka Akun Sekuritas Stockbit atau Ajaib)\n        \$optionAPats = [\n            'opsi a', 'pilih a', 'yang a', 'pilihan a', 'jawaban a', 'a aja', 'a dong',\n            'a min', 'a kak', 'a mas', 'a bro', 'saya pilih a', 'mau yang a', 'mau a', 'ikut a',\n            'nomor 1', 'no 1', 'opsi 1', 'pilih 1', 'yang 1',\n            'daftar sekuritas', 'buka sekuritas', 'buat sekuritas', 'buka akun sekuritas',\n        ];\n        \$isSingleA = (\$clean === 'a' || \$clean === '1') && strlen(\$clean) <= 3;\n        if (\$isSingleA || in_array(\$clean, \$optionAPats) || preg_match('/^(saya\\s+)?(pilih|mau|ikut|ambil)\\s*(opsi|yang|nomor)?\\s*a$/i', \$clean)) {",
        $content
    );
}

// ─── FIX 2: Add 'belajar' topic to $topics array ───────────────────────────
// Insert BEFORE '// TOPIC I — Greeting'
$belaPelajarTopic = <<<'TOPIC'

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
                ],
                'context_boost' => ['saham', 'ipo', 'trading', 'investasi', 'modal'],
            ],

TOPIC;

$content = str_replace(
    "            // TOPIC I — Greeting (Exact/near-exact match only)",
    $belaPelajarTopic . "            // TOPIC I — Greeting (Exact/near-exact match only)",
    $content
);

// ─── FIX 2b: Add 'belajar' response in the match() block ───────────────────
$belajarResponse = <<<'RESP'

            'belajar' => "Kabar baiknya — **kamu sama sekali tidak perlu pengalaman saham atau belajar dari nol!** 🎉\n\nAlgrow Capital sepenuhnya mengelola proses analisis, pemilihan saham IPO, dan pemesanan. Tugasmu hanya:\n\n✅ Menyediakan akun sekuritas aktif (Stockbit/Ajaib)\n✅ Memberikan akses untuk proses pemesanan IPO\n✅ Menerima hasil profit yang sudah dibagi sesuai kesepakatan\n\nTim Algrow Capital yang berpengalaman di bidang pasar modal yang mengurus semuanya — dari riset IPO, pemesanan, hingga penjualan saham di harga terbaik. Jadi meski kamu belum pernah beli saham sekalipun, **kamu tetap bisa ikut program ini!**",

RESP;

$content = str_replace(
    "            'greeting' => \"Halo! 👋 Selamat datang di **Algrow Capital**!",
    $belajarResponse . "\n            'greeting' => \"Halo! 👋 Selamat datang di **Algrow Capital**!",
    $content
);

// ─── Write & verify ─────────────────────────────────────────────────────────
$written = file_put_contents($file, $content);
echo $written ? "✅ Patch applied ({$written} bytes written)\n" : "❌ Failed to write file\n";

// Quick sanity checks
echo str_contains($content, '$isSingleB') ? "✅ isSingleB guard: present\n" : "❌ isSingleB guard: MISSING\n";
echo str_contains($content, '$isSingleA') ? "✅ isSingleA guard: present\n" : "❌ isSingleA guard: MISSING\n";
echo str_contains($content, "'belajar' =>") ? "✅ belajar topic: present\n" : "❌ belajar topic: MISSING\n";
echo str_contains($content, "'belajar saham'") ? "✅ belajar signals: present\n" : "❌ belajar signals: MISSING\n";
echo str_contains($content, "'belajar' => \"Kabar baiknya") ? "✅ belajar response: present\n" : "❌ belajar response: MISSING\n";
