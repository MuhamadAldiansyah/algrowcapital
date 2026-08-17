<?php
$src_path = __DIR__ . '/public/storage/ipos/logo.jpeg';
if (!file_exists($src_path)) {
    die("Logo not found: " . $src_path);
}
$src = imagecreatefromjpeg($src_path);
if (!$src) {
    die("Failed to create image from jpeg.");
}
$w = imagesx($src);
$h = imagesy($src);

$dst192 = imagecreatetruecolor(192, 192);
imagecopyresampled($dst192, $src, 0, 0, 0, 0, 192, 192, $w, $h);
imagepng($dst192, __DIR__ . '/public/icons/icon-192x192.png');

$dst512 = imagecreatetruecolor(512, 512);
imagecopyresampled($dst512, $src, 0, 0, 0, 0, 512, 512, $w, $h);
imagepng($dst512, __DIR__ . '/public/icons/icon-512x512.png');

echo "Icons created successfully.\n";
