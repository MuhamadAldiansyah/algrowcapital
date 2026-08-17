<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #042f22; text-align: center;">Algrow Capital</h2>
        <p style="color: #333333; font-size: 16px;">Halo,</p>
        <p style="color: #333333; font-size: 16px;">Terima kasih telah mendaftar di Algrow Capital. Untuk memverifikasi alamat email Anda, silakan gunakan kode OTP berikut:</p>
        <div style="background-color: #042f22; color: #34d399; font-size: 32px; font-weight: bold; text-align: center; padding: 20px; letter-spacing: 5px; margin: 20px 0; border-radius: 8px;">
            {{ $otp }}
        </div>
        <p style="color: #333333; font-size: 14px;">Kode ini hanya berlaku selama 10 menit. Jika Anda tidak mendaftar di Algrow Capital, abaikan email ini.</p>
        <p style="color: #333333; font-size: 14px; margin-top: 30px;">Salam,<br>Tim Algrow Capital</p>
    </div>
</body>
</html>
