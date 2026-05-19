<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['organizer', 'staff_gate'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['code']) || trim($_GET['code']) === '') {
    die("Kode tiket tidak valid.");
}

$code = trim($_GET['code']);
$username = $_SESSION['user'];
$role = $_SESSION['role'];

if ($role === 'organizer') {
    $stmt = $conn->prepare("
        SELECT promotor.id, promotor.nama_brand
        FROM promotor
        JOIN users ON promotor.user_id = users.id
        WHERE users.username = ?
        LIMIT 1
    ");
} else {
    $stmt = $conn->prepare("
        SELECT 
            promotor_staff.promotor_id AS id,
            promotor.nama_brand,
            promotor_staff.nama_staff
        FROM promotor_staff
        JOIN users ON promotor_staff.user_id = users.id
        JOIN promotor ON promotor_staff.promotor_id = promotor.id
        WHERE users.username = ?
        LIMIT 1
    ");
}

$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Akun tidak terhubung dengan promotor.");
}

$promotor_id = $promotor['id'];

$stmt = $conn->prepare("
    SELECT 
        transactions.id,
        transactions.ticket_code,
        transactions.qty,
        transactions.total,
        transactions.status,
        transactions.used_status,
        transactions.used_at,
        transactions.checkin_method,
        transactions.created_at,
        users.username,
        users.email,
        events.nama_event,
        events.lokasi,
        events.tanggal,
        events.jam_mulai,
        events.jam_selesai,
        tickets.nama_tiket
    FROM transactions
    JOIN users ON transactions.user_id = users.id
    JOIN events ON transactions.event_id = events.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    WHERE transactions.ticket_code = ?
      AND events.organizer_id = ?
    LIMIT 1
");
$stmt->bind_param("si", $code, $promotor_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    die("Tiket tidak ditemukan atau bukan milik event promotor ini.");
}

$jam_selesai = !empty($ticket['jam_selesai']) ? $ticket['jam_selesai'] : '23:59:59';
$event_end = strtotime($ticket['tanggal'] . ' ' . $jam_selesai);
$event_expired = time() > $event_end;

$message = "";
$message_type = "";

if ($event_expired) {
    $message = "Event sudah selesai. Tiket tidak dapat digunakan.";
    $message_type = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($ticket['status'] !== 'paid') {
        $message = "Tiket belum memiliki status pembayaran valid.";
        $message_type = "error";

    } elseif ($ticket['used_status'] === 'used') {
        $message = "Tiket atas nama " . $ticket['username'] . " sudah pernah digunakan.";
        $message_type = "error";

    } else {
        $stmt = $conn->prepare("
            UPDATE transactions
            SET 
                used_status = 'used',
                used_at = NOW(),
                checkin_method = 'qr'
            WHERE id = ?
              AND used_status = 'unused'
              AND status = 'paid'
        ");
        $stmt->bind_param("i", $ticket['id']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "Tiket atas nama " . $ticket['username'] . " berhasil dikonfirmasi.";
            $message_type = "success";
            $ticket['used_status'] = 'used';
            $ticket['used_at'] = date('Y-m-d H:i:s');
            $ticket['checkin_method'] = 'qr';
        } else {
            $message = "Tiket sudah pernah digunakan atau tidak valid.";
            $message_type = "error";
        }
    }
}

$back_link = "ticket-tracking.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Validasi Tiket - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="verify-mobile-page">
    <div class="verify-card">

        <h2>Validasi Tiket</h2>

        <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">
            Login sebagai: <strong><?= htmlspecialchars($username); ?></strong>
        </p>

        <?php if (!empty($message)): ?>
            <div class="<?= $message_type === 'success' ? 'success-msg' : 'error-msg'; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="verify-status <?= $ticket['used_status'] === 'used' ? 'used' : 'valid'; ?>">
            <?= $ticket['used_status'] === 'used' ? 'SUDAH DIGUNAKAN' : 'TIKET VALID' ?>
        </div>

        <h3><?= htmlspecialchars($ticket['nama_event']); ?></h3>

        <p><strong>Pemilik:</strong> <?= htmlspecialchars($ticket['username']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($ticket['email']); ?></p>
        <p><strong>Jenis Tiket:</strong> <?= htmlspecialchars($ticket['nama_tiket']); ?></p>
        <p><strong>Jumlah:</strong> <?= $ticket['qty']; ?> tiket</p>
        <p><strong>Lokasi:</strong> <?= htmlspecialchars($ticket['lokasi']); ?></p>
        <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($ticket['tanggal'])); ?></p>
        <p><strong>Kode Tiket:</strong> <?= htmlspecialchars($ticket['ticket_code']); ?></p>

        <?php if ($event_expired): ?>
            <div class="error-msg">
                Event sudah selesai. Tiket tidak dapat diverifikasi.
            </div>

            <a href="<?= $back_link; ?>" class="btn-add full-btn">Kembali</a>

        <?php elseif ($ticket['used_status'] === 'used'): ?>
            <div class="error-msg">
                Tiket sudah digunakan pada <?= htmlspecialchars($ticket['used_at']); ?><br>
                Metode: <?= htmlspecialchars($ticket['checkin_method'] ?? '-'); ?>
            </div>

            <a href="<?= $back_link; ?>" class="btn-add full-btn">Kembali</a>
        <?php else: ?>
            <form method="POST" onsubmit="return confirm('Konfirmasi tiket atas nama <?= htmlspecialchars($ticket['username']); ?> masuk venue?');">
                <button type="submit" class="btn-add full-btn">
                    Konfirmasi Tiket Masuk
                </button>
            </form>

            <a href="<?= $back_link; ?>" class="btn-view full-btn" style="margin-top:10px;">
                Batal / Kembali
            </a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>