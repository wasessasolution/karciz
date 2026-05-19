<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

$stmt = $conn->prepare("
    SELECT promotor.* 
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username=? 
      AND promotor.status='approved'
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Akun promotor belum terverifikasi.");
}

$organizer_id = $promotor['id'];

function getTotal($conn, $query, $organizer_id) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

$totalEvent = getTotal(
    $conn,
    "SELECT COUNT(*) AS total FROM events WHERE organizer_id=?",
    $organizer_id
);

$eventAktif = getTotal(
    $conn,
    "SELECT COUNT(*) AS total FROM events WHERE organizer_id=? AND status='aktif'",
    $organizer_id
);

$eventSelesai = getTotal(
    $conn,
    "SELECT COUNT(*) AS total FROM events WHERE organizer_id=? AND status='selesai'",
    $organizer_id
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Promotor - KarciZ</title>

    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=6">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Dashboard Promotor</h3>
                <p>
                    Selamat datang kembali,
                    <strong><?= htmlspecialchars($promotor['nama_brand']); ?></strong>
                </p>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h4>Total Event</h4>
                <p><?= $totalEvent; ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Event Aktif</h4>
                <p><?= $eventAktif; ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Event Selesai</h4>
                <p><?= $eventSelesai; ?></p>
            </div>
        </div>

        <div class="form-card">
            <h3>Quick Action</h3>
            <p style="color:#64748b;margin-bottom:22px;">
                Kelola event dan aktivitas promotor dengan cepat.
            </p>

            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="create-event.php" class="btn-add">+ Buat Event Baru</a>
                <a href="manage-event.php" class="btn-view">Kelola Event</a>
            </div>
        </div>

        <div class="dashboard-cards" style="margin-top:28px;">
            <div class="dashboard-card">
                <h4>Status Akun</h4>
                <p style="font-size:18px;">Approved</p>
            </div>

            <div class="dashboard-card">
                <h4>Brand Promotor</h4>
                <p style="font-size:18px;"><?= htmlspecialchars($promotor['nama_brand']); ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Tips</h4>
                <p style="font-size:16px;line-height:1.5;">
                    Gunakan banner event menarik untuk meningkatkan pembelian tiket.
                </p>
            </div>
        </div>

    </div>

</div>

</body>
</html>