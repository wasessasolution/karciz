<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

$stmt = $conn->prepare("
    SELECT promotor.* 
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username=? AND promotor.status='approved'
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    echo "Akun promotor belum terverifikasi.";
    exit;
}

$organizer_id = $promotor['id'];

$totalEvent = 0;
$eventAktif = 0;
$eventSelesai = 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM events WHERE organizer_id=?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$totalEvent = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM events WHERE organizer_id=? AND status='aktif'");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$eventAktif = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM events WHERE organizer_id=? AND status='selesai'");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$eventSelesai = $stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Promotor - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <div class="organizer-sidebar">
        <h2>KarciZ Promotor</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="create-event.php">Buat Event</a>
        <a href="manage-event.php">Kelola Event</a>
        <a href="sales-report.php">Laporan Penjualan</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Dashboard Promotor</h3>
                <p>Selamat datang, <?= htmlspecialchars($promotor['nama_brand']); ?></p>
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
            <br>
            <a href="create-event.php" class="btn-add">+ Buat Event Baru</a>
            <a href="manage-event.php" class="btn-view">Kelola Event</a>
        </div>

    </div>

</div>

</body>
</html>