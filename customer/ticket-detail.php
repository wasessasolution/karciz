<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: history_transaksi.php");
    exit;
}

$username = $_SESSION['user'];
$transaction_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../login.php");
    exit;
}

$user_id = $user['id'];

$stmt = $conn->prepare("
    SELECT
        transactions.id AS transaction_id,
        transactions.qty,
        transactions.total,
        transactions.payment_method,
        transactions.payment_detail,
        transactions.ticket_code,
        transactions.status,
        transactions.used_status,
        transactions.used_at,
        transactions.created_at,

        events.nama_event,
        events.lokasi,
        events.tanggal,
        events.banner,

        tickets.nama_tiket,
        tickets.harga,

        users.username
    FROM transactions
    JOIN events ON transactions.event_id = events.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    JOIN users ON transactions.user_id = users.id
    WHERE transactions.id = ?
      AND transactions.user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $transaction_id, $user_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    echo "Tiket tidak ditemukan.";
    exit;
}

if (empty($ticket['ticket_code'])) {
    $new_code = 'KZ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

    $stmt = $conn->prepare("UPDATE transactions SET ticket_code=? WHERE id=?");
    $stmt->bind_param("si", $new_code, $transaction_id);
    $stmt->execute();

    $ticket['ticket_code'] = $new_code;
}

/* AUTO BASE URL: localhost / hosting */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/Karciz";

$qr_data = $base_url . "/organizer/verify-ticket.php?code=" . urlencode($ticket['ticket_code']);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=" . urlencode($qr_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Ticket - <?= htmlspecialchars($ticket['nama_event']); ?></title>
    <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=1">
    <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=5">
    <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">

</head>
<body>

<?php include '../components/navbar.php'; ?>

<main>
<section class="event-section">
    <div class="container">

        <?php if (isset($_GET['success'])): ?>
            <div class="popup-success">
                Payment success. E-ticket berhasil dibuat.
            </div>
        <?php endif; ?>

        <div class="main-banner">
            <h1>E-Ticket KarciZ</h1>
            <p>Tunjukkan QR ticket ini saat masuk ke lokasi event</p>
        </div>

        <div style="max-width:900px; margin:30px auto;">
            <div class="eticket-card">

                <div class="eticket-left">
                    <?php if (!empty($ticket['banner'])) { ?>
                        <img 
                            src="/Karciz/assets/images/events/<?= htmlspecialchars($ticket['banner']); ?>" 
                            alt="<?= htmlspecialchars($ticket['nama_event']); ?>"
                        >
                    <?php } else { ?>
                        <div class="empty-ticket-img">No Banner</div>
                    <?php } ?>

                    <div class="eticket-info">
                        <h2><?= htmlspecialchars($ticket['nama_event']); ?></h2>

                        <p>
                            <?= htmlspecialchars($ticket['lokasi']); ?> •
                            <?= date('d M Y', strtotime($ticket['tanggal'])); ?>
                        </p>

                        <hr>

                        <p><strong>Nama:</strong> <?= htmlspecialchars($ticket['username']); ?></p>
                        <p><strong>Jenis Tiket:</strong> <?= htmlspecialchars($ticket['nama_tiket']); ?></p>
                        <p><strong>Jumlah:</strong> <?= $ticket['qty']; ?> tiket</p>
                        <p><strong>Total:</strong> Rp <?= number_format($ticket['total'], 0, ',', '.'); ?></p>
                        <p><strong>Pembayaran:</strong> <?= htmlspecialchars($ticket['payment_detail']); ?></p>
                        <p><strong>Kode Tiket:</strong> <?= htmlspecialchars($ticket['ticket_code']); ?></p>

                        <?php if ($ticket['used_status'] === 'used') { ?>
                            <span class="status-used">
                                Sudah digunakan
                            </span>
                            <p style="font-size:13px;color:#666;margin-top:8px;">
                                Digunakan pada: <?= htmlspecialchars($ticket['used_at']); ?>
                            </p>
                        <?php } else { ?>
                            <span class="status-paid">
                                Aktif / Belum digunakan
                            </span>
                        <?php } ?>
                    </div>
                </div>

                <div class="eticket-qr">
                    <h3>QR Ticket</h3>

                    <img 
                        src="<?= $qr_url; ?>" 
                        alt="QR Ticket"
                    >

                    <p style="font-size:13px;color:#666;margin-top:12px;">
                        Scan QR ini oleh promotor/staff gate saat validasi masuk event.
                    </p>

                    <p style="font-size:12px;color:#999;margin-top:8px;">
                        <?= htmlspecialchars($ticket['ticket_code']); ?>
                    </p>
                </div>

            </div>

            <div class="ticket-actions" style="display:flex; gap:12px; margin-top:20px; justify-content:center; flex-wrap:wrap;">
                <a href="history_transaksi.php" class="btn-login" style="width:auto; padding:14px 24px;">
                    Kembali ke My KarciZ
                </a>

                <button onclick="window.print()" class="btn-login" style="width:auto; padding:14px 24px;">
                    Download / Cetak PDF
                </button>
            </div>
        </div>

    </div>
</section>
</main>

<?php include '../components/footer.php'; ?>

<script>
setTimeout(() => {
  const popup = document.querySelector(".popup-success");
  if (popup) popup.style.display = "none";
}, 3000);
</script>

</body>
</html>