<?php
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['organizer', 'staff_gate'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Scan Tiket - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>

<div class="verify-mobile-page">
    <div class="verify-card">
        <h2>Scan Tiket</h2>
        <p>Arahkan kamera ke QR tiket customer.</p>

        <div id="reader" style="width:100%; margin-top:18px;"></div>

        <a href="ticket-tracking.php" class="btn-view full-btn" style="margin-top:18px;">
            Kembali
        </a>
    </div>
</div>

<script>
function onScanSuccess(decodedText) {
    window.location.href = decodedText;
}

const html5QrCode = new Html5Qrcode("reader");

html5QrCode.start(
    { facingMode: "environment" },
    {
        fps: 10,
        qrbox: 250
    },
    onScanSuccess
).catch(err => {
    alert("Kamera tidak bisa dibuka. Pastikan izin kamera aktif dan halaman menggunakan HTTPS atau localhost.");
});
</script>

</body>
</html>