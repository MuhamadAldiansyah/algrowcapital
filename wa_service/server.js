import makeWASocket, {
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    delay,
    Browsers,
    proto
} from '@whiskeysockets/baileys';
import NodeCache from 'node-cache';
import express from 'express';
import cors from 'cors';
import pino from 'pino';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const SESSION_DIR = path.join(__dirname, 'session');

// Create session folder if not exists
if (!fs.existsSync(SESSION_DIR)) {
    fs.mkdirSync(SESSION_DIR, { recursive: true });
}

const app = express();
app.use(cors());
app.use(express.json());

const PORT = 5050;
const logger = pino({ level: 'silent' });

// Cache for retry receipts
const msgRetryCounterCache = new NodeCache({ stdTTL: 3600, checkperiod: 120 });

let sock = null;
let connectionState = {
    status: 'disconnected', // 'disconnected', 'connecting', 'qr_ready', 'pairing_ready', 'connected'
    qr: null,
    pairingCode: null,
    user: null,
    error: null
};

let blastState = {
    isRunning: false,
    total: 0,
    sent: 0,
    failed: 0,
    current: null,
    logs: [],
    abortRequested: false,
    startTime: null,
    finishTime: null
};

// Spintax helper: replaces {Option1|Option2|Option3} with a random pick
function parseSpintax(text) {
    if (!text) return '';
    return text.replace(/\{([^{}]+)\}/g, (match, choices) => {
        const options = choices.split('|');
        return options[Math.floor(Math.random() * options.length)].trim();
    });
}

// Clean phone format to WhatsApp JID (e.g., 087822421207 -> 6287822421207@s.whatsapp.net)
function formatToJid(phone) {
    if (!phone) return null;
    let clean = phone.toString().replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (clean.startsWith('8')) {
        clean = '62' + clean;
    }
    return clean + '@s.whatsapp.net';
}

function formatCleanNumber(phone) {
    if (!phone) return '';
    let clean = phone.toString().replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (clean.startsWith('8')) {
        clean = '62' + clean;
    }
    return clean;
}

// In-memory message store for Signal Protocol retry receipts
// Resolves the "Waiting for this message. This may take a while" issue
const recentMessageStore = new Map();

function storeSentMessage(key, msg) {
    if (!key || !key.id) return;
    recentMessageStore.set(key.id, msg);
    if (recentMessageStore.size > 1000) {
        const oldestKey = recentMessageStore.keys().next().value;
        recentMessageStore.delete(oldestKey);
    }
}

// Initialize WhatsApp connection
async function initWhatsApp(pairingNumber = null) {
    try {
        connectionState.status = 'connecting';
        connectionState.qr = null;
        connectionState.pairingCode = null;
        connectionState.error = null;

        const { state, saveCreds } = await useMultiFileAuthState(SESSION_DIR);
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger)
            },
            browser: Browsers.windows('Desktop'),
            generateHighQualityLinkPreview: false,
            syncFullHistory: false,
            msgRetryCounterCache,
            retryRequestDelayMs: 250,
            getMessage: async (key) => {
                if (key?.id && recentMessageStore.has(key.id)) {
                    return recentMessageStore.get(key.id);
                }
                return proto.Message.fromObject({});
            }
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('messages.upsert', async ({ messages }) => {
            for (const msg of messages) {
                if (msg.key && msg.key.id && msg.message) {
                    storeSentMessage(msg.key, msg.message);
                }
            }
        });

        // Pairing Code Handler
        if (pairingNumber && !sock.authState.creds.registered) {
            const cleanPhone = formatCleanNumber(pairingNumber);
            setTimeout(async () => {
                try {
                    const code = await sock.requestPairingCode(cleanPhone);
                    // Format code into ABCD-EFGH for readability
                    const formattedCode = code?.match(/.{1,4}/g)?.join('-') || code;
                    connectionState.pairingCode = formattedCode;
                    connectionState.status = 'pairing_ready';
                    console.log(`[WA] Pairing code generated: ${formattedCode}`);
                } catch (err) {
                    console.error('[WA] Pairing code error:', err);
                    connectionState.error = 'Gagal membuat pairing code: ' + err.message;
                    connectionState.status = 'disconnected';
                }
            }, 3000);
        }

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr && !pairingNumber) {
                try {
                    connectionState.qr = await QRCode.toDataURL(qr);
                    connectionState.status = 'qr_ready';
                } catch (e) {
                    console.error('Error generating QR DataURL', e);
                }
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                console.log(`[WA] Connection closed. Code: ${statusCode}, Reconnecting: ${shouldReconnect}`);

                connectionState.status = 'disconnected';
                connectionState.user = null;
                connectionState.qr = null;
                connectionState.pairingCode = null;

                if (statusCode === DisconnectReason.loggedOut) {
                    // Wipe session
                    try {
                        fs.rmSync(SESSION_DIR, { recursive: true, force: true });
                        fs.mkdirSync(SESSION_DIR, { recursive: true });
                    } catch (e) {}
                } else if (shouldReconnect) {
                    setTimeout(() => initWhatsApp(), 5000);
                }
            } else if (connection === 'open') {
                console.log('[WA] Connected successfully!');
                connectionState.status = 'connected';
                connectionState.user = sock.user;
                connectionState.qr = null;
                connectionState.pairingCode = null;
                connectionState.error = null;
            }
        });

    } catch (error) {
        console.error('[WA] Initialization failed:', error);
        connectionState.status = 'disconnected';
        connectionState.error = error.message;
    }
}

// Auto-init on startup if existing session exists
if (fs.existsSync(path.join(SESSION_DIR, 'creds.json'))) {
    console.log('[WA] Found existing credentials, connecting...');
    initWhatsApp();
}

// ======================== API ROUTES ========================

// Status
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        data: connectionState
    });
});

// Request Pairing Code (e.g. 087822421207)
app.post('/api/pair', async (req, res) => {
    const { phone } = req.body;
    if (!phone) {
        return res.status(400).json({ success: false, message: 'Nomor telepon wajib diisi.' });
    }

    try {
        if (sock) {
            try { sock.end(); } catch (e) {}
            sock = null;
        }
        await initWhatsApp(phone);
        // Wait up to 5 seconds for pairing code
        let waitCount = 0;
        while (!connectionState.pairingCode && waitCount < 10) {
            await delay(500);
            waitCount++;
        }

        res.json({
            success: true,
            pairingCode: connectionState.pairingCode,
            status: connectionState.status
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Request QR Code
app.post('/api/connect-qr', async (req, res) => {
    try {
        if (sock) {
            try { sock.end(); } catch (e) {}
            sock = null;
        }
        await initWhatsApp(null);
        res.json({ success: true, message: 'Menunggu QR Code...' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Logout / Reset Session
app.post('/api/logout', async (req, res) => {
    try {
        console.log('[WA] Logout / reset session requested.');
        if (sock) {
            try { await sock.logout(); } catch (e) {}
            try { sock.end(); } catch (e) {}
            sock = null;
        }
        recentMessageStore.clear();
        if (msgRetryCounterCache) {
            try { msgRetryCounterCache.flushAll(); } catch (e) {}
        }
        try {
            fs.rmSync(SESSION_DIR, { recursive: true, force: true });
            fs.mkdirSync(SESSION_DIR, { recursive: true });
        } catch (e) {}

        connectionState = {
            status: 'disconnected',
            qr: null,
            pairingCode: null,
            user: null,
            error: null
        };

        res.json({ success: true, message: 'Sesi WhatsApp berhasil di-reset. Silakan hubungkan ulang.' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Test Message to a single number
app.post('/api/test-send', async (req, res) => {
    const { phone, message, sendType = 'text', pollQuestion = '', pollOptions = [] } = req.body;
    if (connectionState.status !== 'connected' || !sock) {
        return res.status(400).json({ success: false, message: 'WhatsApp belum terhubung!' });
    }
    if (!phone || (!message && !pollQuestion)) {
        return res.status(400).json({ success: false, message: 'Nomor dan pesan/pertanyaan polling wajib diisi.' });
    }

    try {
        const jid = formatToJid(phone);
        console.log(`[TEST-SEND] Mengirim ke ${phone} (${jid}) [type: ${sendType}]...`);
        await sock.sendPresenceUpdate('composing', jid);
        await delay(1500);

        let sentMsg;
        if (sendType === 'poll') {
            const questionText = parseSpintax(pollQuestion || message);
            const values = Array.isArray(pollOptions) && pollOptions.length >= 2
                ? pollOptions.map(o => o.trim()).filter(Boolean)
                : ['YA (Lanjut)', 'TIDAK (Batal)'];

            sentMsg = await sock.sendMessage(jid, {
                poll: {
                    name: questionText,
                    values: values,
                    selectableCount: 1
                }
            });
        } else {
            sentMsg = await sock.sendMessage(jid, { text: parseSpintax(message) });
        }

        if (sentMsg?.key && sentMsg?.message) {
            storeSentMessage(sentMsg.key, sentMsg.message);
        }

        await sock.sendPresenceUpdate('paused', jid);
        console.log(`[TEST-SEND] Berhasil terkirim ke ${phone}`);

        res.json({ success: true, message: `Pesan uji coba (${sendType === 'poll' ? 'Polling Interaktif' : 'Teks'}) berhasil terkirim ke ${phone}` });
    } catch (err) {
        console.error(`[TEST-SEND] Gagal kirim ke ${phone}:`, err);
        res.status(500).json({ success: false, message: err.message });
    }
});

// Start Blast Job
app.post('/api/blast/start', async (req, res) => {
    const {
        recipients = [],
        message = '',
        sendType = 'text',
        pollQuestion = '',
        pollOptions = ['YA (Lanjut)', 'TIDAK (Batal)'],
        minDelay = 10,
        maxDelay = 25,
        batchSize = 15,
        batchDelay = 60
    } = req.body;

    if (connectionState.status !== 'connected' || !sock) {
        return res.status(400).json({ success: false, message: 'WhatsApp belum terhubung!' });
    }

    if (blastState.isRunning) {
        return res.status(400).json({ success: false, message: 'Proses blast sedang berjalan!' });
    }

    if (!Array.isArray(recipients) || recipients.length === 0) {
        return res.status(400).json({ success: false, message: 'Daftar penerima kosong.' });
    }

    if (!message && !pollQuestion) {
        return res.status(400).json({ success: false, message: 'Isi template pesan atau pertanyaan polling tidak boleh kosong.' });
    }

    // Reset blast state
    blastState = {
        isRunning: true,
        total: recipients.length,
        sent: 0,
        failed: 0,
        current: null,
        logs: [],
        abortRequested: false,
        startTime: new Date().toISOString(),
        finishTime: null
    };

    // Run async in background
    runBlastJob(
        recipients,
        message,
        Number(minDelay),
        Number(maxDelay),
        Number(batchSize),
        Number(batchDelay),
        sendType,
        pollQuestion,
        pollOptions
    );

    res.json({
        success: true,
        message: `Blast dimulai untuk ${recipients.length} penerima.`,
        data: blastState
    });
});

// Background Blast Runner
async function runBlastJob(
    recipients,
    templateMessage,
    minDelay,
    maxDelay,
    batchSize,
    batchDelaySec,
    sendType = 'text',
    pollQuestion = '',
    pollOptions = ['YA (Lanjut)', 'TIDAK (Batal)']
) {
    const minD = Math.max(minDelay, 5) * 1000;
    const maxD = Math.max(maxDelay, minDelay) * 1000;
    const batchCooldownMs = Math.max(batchDelaySec, 10) * 1000;

    const blastLabel = sendType === 'poll' ? 'Polling Interaktif (Tombol YA/TIDAK)' : 'Pesan Teks';
    addBlastLog('INFO', `Memulai blast [${blastLabel}] ke ${recipients.length} kontak...`);

    let consecutiveCount = 0;

    for (let i = 0; i < recipients.length; i++) {
        if (blastState.abortRequested) {
            addBlastLog('WARN', 'Proses blast dihentikan oleh pengguna.');
            break;
        }

        const item = recipients[i];
        const rawPhone = item.phone || '';
        const name = item.name || 'Pelanggan';

        if (!rawPhone) {
            blastState.failed++;
            addBlastLog('FAIL', `[${i + 1}/${recipients.length}] Lewati: nomor kosong (${name})`);
            continue;
        }

        const jid = formatToJid(rawPhone);
        blastState.current = { name, phone: rawPhone, index: i + 1 };

        // Parse custom tags
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', { dateStyle: 'long' });
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        const recipientCompany = item.company || 'Algrow Capital';
        const recipientSaham = item.saham || 'IPO';

        let personalized = (templateMessage || pollQuestion)
            .replace(/\{nama\}/gi, name)
            .replace(/\{name\}/gi, name)
            .replace(/\{phone\}/gi, rawPhone)
            .replace(/\{perusahaan\}/gi, recipientCompany)
            .replace(/\{saham\}/gi, recipientSaham)
            .replace(/\{tanggal\}/gi, dateStr)
            .replace(/\{jam\}/gi, timeStr);

        // Apply Spintax
        personalized = parseSpintax(personalized);

        try {
            // Anti-ban step 1: Presence Composing (Simulasi mengetik 2-3 detik)
            await sock.sendPresenceUpdate('available');
            await delay(1000);
            await sock.sendPresenceUpdate('composing', jid);
            await delay(2500);

            // Send as Poll (Interactive Buttons) or Text
            let sentMsg;
            if (sendType === 'poll') {
                let qText = (pollQuestion || personalized)
                    .replace(/\{nama\}/gi, name)
                    .replace(/\{name\}/gi, name)
                    .replace(/\{perusahaan\}/gi, recipientCompany)
                    .replace(/\{saham\}/gi, recipientSaham);
                qText = parseSpintax(qText);

                const values = Array.isArray(pollOptions) && pollOptions.length >= 2
                    ? pollOptions.map(o => o.trim()).filter(Boolean)
                    : ['YA (Lanjut)', 'TIDAK (Batal)'];

                sentMsg = await sock.sendMessage(jid, {
                    poll: {
                        name: qText,
                        values: values,
                        selectableCount: 1
                    }
                });
            } else {
                sentMsg = await sock.sendMessage(jid, { text: personalized });
            }

            if (sentMsg?.key && sentMsg?.message) {
                storeSentMessage(sentMsg.key, sentMsg.message);
            }

            await sock.sendPresenceUpdate('paused', jid);

            blastState.sent++;
            consecutiveCount++;
            addBlastLog('SUCCESS', `[${i + 1}/${recipients.length}] Terkirim ke ${name} (${rawPhone}) [${sendType === 'poll' ? 'Polling' : 'Teks'}]`);

            // Anti-ban step 2: Batch Cooldown
            if (consecutiveCount >= batchSize && i < recipients.length - 1) {
                consecutiveCount = 0;
                addBlastLog('INFO', `Jeda istirahat batch (${batchDelaySec} detik) untuk perlindungan anti-ban...`);
                
                // Sleep with abort checks every second
                const sleepIntervals = Math.floor(batchCooldownMs / 1000);
                for (let s = 0; s < sleepIntervals; s++) {
                    if (blastState.abortRequested) break;
                    await delay(1000);
                }
            } else if (i < recipients.length - 1) {
                // Anti-ban step 3: Randomized Delay
                const randomDelayMs = Math.floor(Math.random() * (maxD - minD + 1)) + minD;
                const delaySec = (randomDelayMs / 1000).toFixed(1);
                addBlastLog('DEBUG', `Jeda anti-ban acak ${delaySec}s sebelum pesan berikutnya...`);

                const intervals = Math.floor(randomDelayMs / 1000);
                for (let s = 0; s < intervals; s++) {
                    if (blastState.abortRequested) break;
                    await delay(1000);
                }
            }

        } catch (err) {
            blastState.failed++;
            addBlastLog('ERROR', `[${i + 1}/${recipients.length}] Gagal kirim ke ${name} (${rawPhone}): ${err.message}`);
            await delay(2000);
        }
    }

    blastState.isRunning = false;
    blastState.current = null;
    blastState.finishTime = new Date().toISOString();
    addBlastLog('INFO', `Blast selesai. Berhasil: ${blastState.sent}, Gagal: ${blastState.failed}`);
}

function addBlastLog(type, message) {
    const time = new Date().toLocaleTimeString('id-ID');
    blastState.logs.unshift({ type, message, time });
    if (blastState.logs.length > 200) {
        blastState.logs.pop();
    }
}

// Progress status
app.get('/api/blast/status', (req, res) => {
    res.json({
        success: true,
        data: blastState
    });
});

// Stop Blast
app.post('/api/blast/stop', (req, res) => {
    if (!blastState.isRunning) {
        return res.json({ success: true, message: 'Tidak ada proses blast yang berjalan.' });
    }
    blastState.abortRequested = true;
    res.json({ success: true, message: 'Perintah penghentian blast telah dikirim.' });
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`WhatsApp Blast Service running on http://127.0.0.1:${PORT}`);
});

