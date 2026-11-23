<?php
/**
 * Test: Verifikasi semua bug fix sudah benar
 * Format: [input] => expected_snippet (substring dari response yang diharapkan)
 */
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app(\App\Http\Controllers\ChatbotController::class);
$engine = new ReflectionMethod($controller, 'strictKnowledgeEngine');
$engine->setAccessible(true);

// [input => expected_substring_in_response]
$tests = [
    // ── Bug 1: False-match setelah pilih A/B → pertanyaan lain harus jawab benar ──
    'bank jago apa bedanya dengan jago syariah?'      => 'Jago Biasa',
    'boleh pakai rekening bank lain?'                 => null, // apapun asal bukan outOfScope/optionB
    'apa itu RDN?'                                    => 'Stockbit',
    'bagaimana cara kerja bagi hasil?'                => 'Begini cara kerjanya',
    'apa syarat daftar mitra algrow?'                 => 'Syarat jadi mitra',

    // ── Bug 2: Belajar / pemula / belum pernah saham ─────────────────────────────
    'apakah saya harus belajar dari 0?'               => 'tidak perlu pengalaman saham',
    'apakah saya harus belajar saham dulu?'           => 'tidak perlu pengalaman saham',
    'saya pemula, apakah perlu belajar saham dulu?'   => 'tidak perlu pengalaman saham',
    'saya tidak tau saham sama sekali, bisa ikut?'    => 'tidak perlu pengalaman saham',
    'apakah wajib ngerti trading?'                    => 'tidak perlu pengalaman saham',
    'saya belum pernah beli saham, bisa daftar?'      => 'tidak perlu pengalaman saham',
    'saya tidak pengalaman di saham'                  => 'tidak perlu pengalaman saham',

    // ── Regresi: A/B short replies masih harus jalan ─────────────────────────────
    'b'                                               => 'Mitra Algrow Capital',
    'a'                                               => 'cukup pilih salah satu',
    'B'                                               => 'Mitra Algrow Capital',
    'A'                                               => 'cukup pilih salah satu',
    'pilih b'                                         => 'Mitra Algrow Capital',
    'opsi a'                                          => 'cukup pilih salah satu',

    // ── Regresi: Preset questions chatbot ────────────────────────────────────────
    'Panduan buat akun gimana?'                       => 'Stockbit',
    'Pakai aplikasi sekuritas apa saja?'              => 'Stockbit',
    'Apakah butuh modal sendiri untuk daftar?'        => 'Rp 0',
    'Cara daftar akun sekuritas Stockbit atau Ajaib gimana?' => 'Stockbit',
    'cara daftar stockbit'                            => 'Bank Jago',
    'cara daftar ajaib'                               => 'Ajaib',
];

$pass = 0;
$fail = 0;

foreach ($tests as $input => $expectedSnippet) {
    $result = $engine->invoke($controller, $input, []);

    if ($expectedSnippet === null) {
        // Hanya pastikan bukan outOfScope
        $ok = !str_contains($result, 'Mohon maaf ya, sebagai asisten khusus');
    } else {
        $ok = str_contains($result, $expectedSnippet);
    }

    $short = mb_substr($result, 0, 100) . (mb_strlen($result) > 100 ? '...' : '');

    if ($ok) {
        echo "✅ INPUT: {$input}\n";
        $pass++;
    } else {
        echo "❌ INPUT: {$input}\n";
        echo "   EXP contains: \"{$expectedSnippet}\"\n";
        echo "   GOT: {$short}\n";
        $fail++;
    }
}

echo "\n========================================\n";
echo "PASS: {$pass}  FAIL: {$fail}  TOTAL: " . ($pass + $fail) . "\n";
echo ($fail === 0) ? "🎉 All tests passed!\n" : "⚠️  {$fail} test(s) FAILED\n";
