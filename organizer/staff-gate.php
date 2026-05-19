<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'staff_gate') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../lang/lang.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Staff Gate - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="verify-mobile-page">
    <div class="verify-card">
        <h2>Staff Gate</h2>
        <p>Login sebagai: <strong><?= htmlspecialchars($_SESSION['user']); ?></strong></p>

        <div class="success-msg">
            Akun staff aktif. Silakan scan QR tiket customer menggunakan kamera HP.
        </div>

        <p style="margin-top:16px;">
            Setelah QR discan, sistem akan membuka halaman validasi tiket.
        </p>

        <a href="../logout.php" class="btn-add full-btn">
            Logout
        </a>
    </div>
</div>

</body>
</html>