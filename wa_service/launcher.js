import { spawn } from 'child_process';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const CLOUDFLARED_PATH = path.join(__dirname, 'cloudflared.exe');
const TUNNEL_URL_FILE = path.join(__dirname, 'tunnel_url.txt');
const VERCEL_TARGET = 'https://algrowcapital.vercel.app/whatsapp-blast/set-gateway';

console.clear();
console.log('=====================================================================');
console.log('   MEMULAI WHATSAPP SERVICE & CLOUDFLARE TUNNEL (DRIVE D:)');
console.log('=====================================================================\n');

// 1. Jalankan Node Server (Baileys)
const serverProcess = spawn('node', ['server.js'], {
    cwd: __dirname,
    stdio: 'inherit'
});

serverProcess.on('error', (err) => {
    console.error('[SERVER ERROR]:', err);
});

// 2. Jalankan Cloudflare Tunnel
if (!fs.existsSync(CLOUDFLARED_PATH)) {
    console.warn('[WARNING] cloudflared.exe tidak ditemukan di:', CLOUDFLARED_PATH);
} else {
    console.log('[TUNNEL] Membuka Cloudflare Quick Tunnel ke http://127.0.0.1:5050 ...');

    const tunnelProcess = spawn(CLOUDFLARED_PATH, ['tunnel', '--url', 'http://127.0.0.1:5050'], {
        cwd: __dirname
    });

    let detectedUrl = null;

    function handleTunnelOutput(data) {
        const text = data.toString();
        // Cari pattern https://*.trycloudflare.com
        const match = text.match(/https:\/\/[a-zA-Z0-9-]+\.trycloudflare\.com/);
        if (match && !detectedUrl) {
            detectedUrl = match[0];
            fs.writeFileSync(TUNNEL_URL_FILE, detectedUrl);

            console.log('\n=====================================================================');
            console.log('  \x1b[32m%s\x1b[0m', '✅ CLOUDFLARE TUNNEL BERHASIL TERHUBUNG!');
            console.log('  \x1b[36m%s\x1b[0m', `🌐 URL GATEWAY: ${detectedUrl}`);
            console.log('=====================================================================');
            console.log('  Buka menu WhatsApp Blast di Vercel:');
            console.log('  \x1b[33m%s\x1b[0m', 'https://algrowcapital.vercel.app/whatsapp-blast');
            console.log('  Lalu masukkan URL Gateway di atas pada tombol "Ubah Gateway".\n');

            // Coba auto-register ke Vercel jika rute tersedia
            tryAutoRegister(detectedUrl);
        }
    }

    tunnelProcess.stdout.on('data', handleTunnelOutput);
    tunnelProcess.stderr.on('data', handleTunnelOutput);

    tunnelProcess.on('error', (err) => {
        console.error('[TUNNEL ERROR]:', err);
    });
}

async function tryAutoRegister(url) {
    try {
        console.log('[AUTO-SYNC] Mencoba mendaftarkan URL ke Vercel...');
        const res = await fetch(VERCEL_TARGET, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url })
        });
        const json = await res.json();
        if (json.success) {
            console.log('  \x1b[32m%s\x1b[0m', '✅ URL Gateway berhasil otomatis disinkronkan ke Vercel!');
        }
    } catch (e) {
        console.log('[AUTO-SYNC] Vercel auto-register notice: Silakan simpan manual di web Vercel.');
    }
}

