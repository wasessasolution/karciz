<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

/*
|--------------------------------------------------------------------------
| AUTH ORGANIZER
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| AMBIL DATA PROMOTOR
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT 
        promotor.id,
        promotor.nama_brand
    FROM promotor
    JOIN users 
        ON promotor.user_id = users.id
    WHERE users.username = ?
      AND promotor.status = 'approved'
    LIMIT 1
");

$stmt->bind_param("s", $username);
$stmt->execute();

$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Akun promotor belum terverifikasi.");
}

$organizer_id = $promotor['id'];

/*
|--------------------------------------------------------------------------
| DELETE EVENT
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $delete_id = intval($_GET['delete']);

    // cek event milik organizer
    $stmt = $conn->prepare("
        SELECT id
        FROM events
        WHERE id = ?
        AND organizer_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("ii", $delete_id, $organizer_id);
    $stmt->execute();

    $eventCheck = $stmt->get_result()->fetch_assoc();

    if (!$eventCheck) {
        header("Location: manage-event.php?error=notfound");
        exit;
    }

    // cek transaksi
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM transactions
        WHERE event_id = ?
    ");

    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    $trxCount = $stmt->get_result()->fetch_assoc()['total'];

    if ($trxCount > 0) {
        header("Location: manage-event.php?error=has_transaction");
        exit;
    }

    $conn->begin_transaction();

    try {

        // hapus tiket
        $stmt = $conn->prepare("
            DELETE FROM tickets
            WHERE event_id = ?
        ");

        $stmt->bind_param("i", $delete_id);
        $stmt->execute();

        // hapus event
        $stmt = $conn->prepare("
            DELETE FROM events
            WHERE id = ?
            AND organizer_id = ?
        ");

        $stmt->bind_param("ii", $delete_id, $organizer_id);
        $stmt->execute();

        $conn->commit();

        header("Location: manage-event.php?success=deleted");
        exit;

    } catch (Exception $e) {

        $conn->rollback();

        header("Location: manage-event.php?error=delete_failed");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| NOTIFICATION
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'deleted') {
        $success = "Event berhasil dihapus.";
    }

    elseif ($_GET['success'] === 'created') {
        $success = "Event berhasil dibuat.";
    }
}

if (isset($_GET['error'])) {

    if ($_GET['error'] === 'has_transaction') {
        $error = "Event tidak bisa dihapus karena sudah memiliki transaksi.";
    }

    elseif ($_GET['error'] === 'notfound') {
        $error = "Event tidak ditemukan atau bukan milik Anda.";
    }

    elseif ($_GET['error'] === 'delete_failed') {
        $error = "Gagal menghapus event.";
    }
}

/*
|--------------------------------------------------------------------------
| GET EVENTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT 
        events.*,

        COALESCE(ticket_summary.total_stok, 0) AS total_stok,

        COALESCE(transaction_summary.total_terjual, 0) AS total_terjual,

        COALESCE(transaction_summary.total_transaksi, 0) AS total_transaksi

    FROM events

    LEFT JOIN (

        SELECT 
            event_id,
            SUM(stok) AS total_stok
        FROM tickets
        GROUP BY event_id

    ) AS ticket_summary
        ON ticket_summary.event_id = events.id

    LEFT JOIN (

        SELECT 
            event_id,
            SUM(qty) AS total_terjual,
            COUNT(id) AS total_transaksi
        FROM transactions
        WHERE status = 'paid'
        GROUP BY event_id

    ) AS transaction_summary
        ON transaction_summary.event_id = events.id

    WHERE events.organizer_id = ?

    ORDER BY events.created_at DESC
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
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

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

        <div class="table-container event-table-card">

            <div class="table-header-action">
                <div>
                    <h3>Daftar Event</h3>
                    <p>Kelola event, tiket, stok, dan status event promotor Anda.</p>
                </div>

                <a href="create-event.php" class="btn-add">+ Buat Event Baru</a>
            </div>

            <table class="event-manage-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Penjualan</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($events->num_rows > 0) { ?>
                        <?php while ($row = $events->fetch_assoc()) { ?>
                            <tr>
                                <td>
                                    <div class="event-cell">
                                        <?php if (!empty($row['banner'])) { ?>
                                            <img 
                                                src="/Karciz/assets/images/events/<?= htmlspecialchars($row['banner']); ?>" 
                                                alt="Banner"
                                            >
                                        <?php } else { ?>
                                            <div class="event-cell-empty">No Banner</div>
                                        <?php } ?>

                                        <div>
                                            <h4><?= htmlspecialchars($row['nama_event']); ?></h4>
                                            <p><?= htmlspecialchars($row['lokasi']); ?></p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="event-date-cell">
                                        <strong><?= date('d M Y', strtotime($row['tanggal'])); ?></strong>
                                        <span>
                                            <?= !empty($row['jam_mulai']) ? substr($row['jam_mulai'], 0, 5) : '--:--'; ?>
                                            -
                                            <?= !empty($row['jam_selesai']) ? substr($row['jam_selesai'], 0, 5) : '--:--'; ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($row['status'] == 'aktif') { ?>
                                        <span class="badge-active">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge-finished">Selesai</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <div class="sales-mini">
                                        <span>Terjual: <strong><?= $row['total_terjual'] ?? 0; ?></strong></span>
                                        <span>Stok: <strong><?= $row['total_stok'] ?? 0; ?></strong></span>
                                    </div>
                                </td>

                                <td>
                                    <?= date('d M Y', strtotime($row['created_at'])); ?>
                                </td>

                                <td>
                                    <div class="action-group">
                                        <a href="../customer/event-detail.php?id=<?= $row['id']; ?>" class="btn-view">Lihat</a>
                                        <a href="edit-event.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                                        <a 
                                            href="manage-event.php?delete=<?= $row['id']; ?>" 
                                            class="btn-delete"
                                            onclick="return confirm('Yakin ingin menghapus event ini?')"
                                        >
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="empty-table">Belum ada event.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>