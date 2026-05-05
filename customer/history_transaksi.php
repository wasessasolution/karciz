<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

// Ambil user login
$stmt = $conn->prepare("SELECT id, username FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../login.php");
    exit;
}

$user_id = $user['id'];

// Ambil transaksi user
$stmt = $conn->prepare("
    SELECT 
        transactions.id AS transaction_id,
        transactions.qty,
        transactions.total,
        transactions.payment_method,
        transactions.payment_detail,
        transactions.status,
        transactions.created_at,
        events.nama_event,
        events.lokasi,
        events.tanggal,
        events.banner,
        tickets.nama_tiket,
        tickets.harga
    FROM transactions
    JOIN events ON transactions.event_id = events.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    WHERE transactions.user_id = ?
    ORDER BY transactions.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>My KarciZ - Riwayat Transaksi</title>
    <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=6">
</head>
<body>

<?php include '../components/navbar.php'; ?>

<main>
    <section class="event-section">
        <div class="container">

            <div class="main-banner">
                <h1>My KarciZ</h1>
                <p>Riwayat transaksi dan tiket event Anda</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="popup-success">
                    Pembayaran berhasil. Tiket berhasil dibuat.
                </div>
            <?php endif; ?>

            <div style="margin-top:30px;">

                <?php if ($transactions->num_rows > 0) { ?>

                    <?php while ($row = $transactions->fetch_assoc()) { ?>

                        <div class="ticket-history-card">

                            <div class="ticket-history-left">
                                <?php if (!empty($row['banner'])) { ?>
                                    <img 
                                        src="/Karciz/assets/images/events/<?= htmlspecialchars($row['banner']); ?>" 
                                        alt="<?= htmlspecialchars($row['nama_event']); ?>"
                                    >
                                <?php } else { ?>
                                    <div class="empty-ticket-img">No Banner</div>
                                <?php } ?>
                            </div>

                            <div class="ticket-history-content">
                                <h3><?= htmlspecialchars($row['nama_event']); ?></h3>

                                <p>
                                    <?= htmlspecialchars($row['lokasi']); ?> • 
                                    <?= date('d M Y', strtotime($row['tanggal'])); ?>
                                </p>

                                <p><strong>Jenis Tiket:</strong> <?= htmlspecialchars($row['nama_tiket']); ?></p>
                                <p><strong>Jumlah:</strong> <?= $row['qty']; ?> tiket</p>
                                <p><strong>Total:</strong> Rp <?= number_format($row['total'], 0, ',', '.'); ?></p>
                                <p><strong>Pembayaran:</strong> <?= htmlspecialchars($row['payment_method']); ?> <?= htmlspecialchars($row['payment_detail']); ?></p>

                                <span class="status-paid">
                                    <?= htmlspecialchars($row['status']); ?>
                                </span>
                            </div>

                            <div class="ticket-history-action">
                                <a href="ticket-detail.php?id=<?= $row['transaction_id']; ?>" class="btn-login">
                                    Lihat Tiket
                                </a>
                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="event-card" style="padding:24px;">
                        <h3>Belum ada transaksi</h3>
                        <p>Silakan beli tiket event terlebih dahulu.</p>
                        <br>
                        <a href="../index.php" class="btn-login">Cari Event</a>
                    </div>

                <?php } ?>

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