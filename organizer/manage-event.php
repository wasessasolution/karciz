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

$stmt = $conn->prepare("
    SELECT promotor.id, promotor.nama_brand
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username = ?
    AND promotor.status = 'approved'
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

if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $event_id, $organizer_id);

    if ($stmt->execute()) {
        $success = "Event berhasil dihapus.";
    } else {
        $error = "Gagal menghapus event.";
    }
}

$stmt = $conn->prepare("
    SELECT * FROM events
    WHERE organizer_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Event - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=3">
</head>
<body>

<div class="organizer-wrapper">

    <div class="organizer-sidebar">
        <h2>KarciZ Promotor</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="create-event.php">Buat Event</a>
        <a href="manage-event.php" class="active">Kelola Event</a>
        <a href="sales-report.php">Laporan Penjualan</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <h3>Kelola Event</h3>
            <span><?= htmlspecialchars($promotor['nama_brand']); ?></span>
        </div>

        <?php if ($success): ?>
            <div class="success-msg"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="table-container">

            <div class="table-action">
                <a href="create-event.php" class="btn-add">+ Buat Event Baru</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Banner</th>
                        <th>Nama Event</th>
                        <th>Lokasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($events->num_rows > 0) { ?>
                        <?php $no = 1; while ($row = $events->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?php if (!empty($row['banner'])) { ?>
                                        <img 
                                            src="/Karciz/assets/images/events/<?= htmlspecialchars($row['banner']); ?>" 
                                            alt="Banner"
                                            class="event-thumb"
                                        >
                                    <?php } else { ?>
                                        <span>-</span>
                                    <?php } ?>
                                </td>

                                <td><?= htmlspecialchars($row['nama_event']); ?></td>
                                <td><?= htmlspecialchars($row['lokasi']); ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>

                                <td>
                                    <?php if ($row['status'] == 'aktif') { ?>
                                        <span class="badge-active">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge-finished">Selesai</span>
                                    <?php } ?>
                                </td>

                                <td><?= date('d M Y', strtotime($row['created_at'])); ?></td>

                                <td>
                                    <a href="../customer/event-detail.php?id=<?= $row['id']; ?>" class="btn-view">Lihat</a>
                                    <a href="edit-event.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a 
                                        href="manage-event.php?delete=<?= $row['id']; ?>" 
                                        class="btn-delete"
                                        onclick="return confirm('Yakin ingin menghapus event ini?')"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8" class="empty-table">Belum ada event.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>