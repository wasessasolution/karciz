<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];
$error = "";
$success = "";

// Ambil data promotor
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

// HANDLE SUBMIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_event = trim($_POST['nama_event']);
    $lokasi     = trim($_POST['lokasi']);
    $tanggal    = $_POST['tanggal'];
    $deskripsi  = trim($_POST['deskripsi']);
    $status     = 'aktif';

    $banner = "";

    // UPLOAD BANNER
    if (!empty($_FILES['banner']['name'])) {

        $allowed = ['jpg','jpeg','png','webp'];
        $file_name = $_FILES['banner']['name'];
        $file_tmp  = $_FILES['banner']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Format gambar harus JPG, PNG, WEBP";
        } else {

            $new_name = time() . "_" . uniqid() . "." . $ext;
            $path = "../assets/images/events/" . $new_name;

            if (move_uploaded_file($file_tmp, $path)) {
                $banner = $new_name;
            } else {
                $error = "Upload gagal";
            }
        }
    }

    // INSERT DATABASE
    if ($error == "") {

        $stmt = $conn->prepare("
            INSERT INTO events 
            (organizer_id, nama_event, lokasi, tanggal, deskripsi, banner, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issssss",
            $organizer_id,
            $nama_event,
            $lokasi,
            $tanggal,
            $deskripsi,
            $banner,
            $status
        );

        if ($stmt->execute()) {
            $success = "Event berhasil dibuat!";
        } else {
            $error = "Gagal menyimpan event.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Event - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=2">
</head>
<body>

<div class="organizer-wrapper">

    <!-- SIDEBAR -->
    <div class="organizer-sidebar">
        <h2>KarciZ Promotor</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="create-event.php" class="active">Buat Event</a>
        <a href="manage-event.php">Kelola Event</a>
        <a href="sales-report.php">Laporan Penjualan</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="organizer-main">

        <div class="organizer-topbar">
            <h3>Buat Event</h3>
            <span><?= htmlspecialchars($promotor['nama_brand']); ?></span>
        </div>

        <!-- FORM -->
        <div class="form-card">

            <?php if ($success): ?>
                <div class="success-msg"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-msg"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <label>Nama Event</label>
                <input type="text" name="nama_event" required>

                <label>Lokasi</label>
                <input type="text" name="lokasi" required>

                <label>Tanggal Event</label>
                <input type="date" name="tanggal" required>

                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="5" required></textarea>

                <label>Banner Event</label>
                <input type="file" name="banner" accept="image/*">

                <button type="submit">Simpan Event</button>

            </form>

        </div>

    </div>

</div>

</body>
</html>