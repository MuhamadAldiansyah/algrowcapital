<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app(\App\Http\Controllers\ChatbotController::class);
$engine = new ReflectionMethod($controller, 'strictKnowledgeEngine');
$engine->setAccessible(true);

$tests = [
    'bank jago apa bedanya dengan jago syariah?',
    'apa itu RDN?',
    'bagaimana cara kerja bagi hasil?',
    'apa syarat daftar mitra algrow?',
    'saya belum pernah beli saham bisa daftar',
];

foreach ($tests as $t) {
    $r = $engine->invoke($controller, $t, []);
    echo "---INPUT: {$t}\n";
    echo substr($r, 0, 150) . "\n\n";
}
