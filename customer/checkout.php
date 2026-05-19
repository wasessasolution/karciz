<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$event_id  = intval($_POST['event_id'] ?? 0);
$ticket_id = intval($_POST['ticket_id'] ?? 0);
$qty       = intval($_POST['qty'] ?? 0);

$error = "";

if ($event_id <= 0 || $ticket_id <= 0 || $qty <= 0) {
    $error = "Silakan pilih tiket dan jumlah tiket terlebih dahulu.";
}

$stmt = $conn->prepare("
    SELECT 
        events.id AS event_id,
        events.nama_event,
        events.lokasi,
        events.tanggal,
        tickets.id AS ticket_id,
        tickets.nama_tiket,
        tickets.harga,
        tickets.stok
    FROM tickets
    JOIN events ON tickets.event_id = events.id
    WHERE events.id = ? AND tickets.id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $event_id, $ticket_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $error = "Data tiket tidak ditemukan.";
} elseif ($qty > $data['stok']) {
    $error = "Jumlah tiket melebihi stok tersedia.";
}

$total = 0;
$dana_number = "083182004753";

if (!$error) {
    $total = $data['harga'] * $qty;
}

$qr_text = "DANA Payment KarciZ | Nomor: " . $dana_number . " | Total: Rp " . number_format($total, 0, ',', '.');
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($qr_text);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Karciz - Checkout</title>
  <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=5">
  <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">
</head>
<body>

<?php include '../components/navbar.php'; ?>

<main>
<section class="event-section">
  <div class="container">

    <div class="main-banner">
      <h1>Checkout Tiket</h1>
      <p>Scan QR DANA untuk melakukan pembayaran</p>
    </div>

    <?php if ($error): ?>

      <div class="event-card" style="padding:24px; margin-top:30px;">
        <h2>Checkout Gagal</h2>
        <p style="color:red; margin-top:10px;"><?= htmlspecialchars($error); ?></p>
        <br>
        <a href="../index.php" class="btn-login">Kembali ke Home</a>
      </div>

    <?php else: ?>

      <div class="event-grid" style="margin-top:30px;">

        <div class="event-card" style="padding:24px;">
          <h3>Ringkasan Pesanan</h3>

          <p><strong>Event:</strong> <?= htmlspecialchars($data['nama_event']); ?></p>
          <p><strong>Lokasi:</strong> <?= htmlspecialchars($data['lokasi']); ?></p>
          <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($data['tanggal'])); ?></p>
          <p><strong>Jenis Tiket:</strong> <?= htmlspecialchars($data['nama_tiket']); ?></p>
          <p><strong>Jumlah Tiket:</strong> <?= $qty; ?></p>
          <p><strong>Harga per Tiket:</strong> Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>

          <hr style="margin:16px 0;">

          <p style="font-size:20px;">
            <strong>Total Pembayaran:</strong><br>
            Rp <?= number_format($total, 0, ',', '.'); ?>
          </p>
        </div>

        <div class="event-card" style="padding:24px; text-align:center;">
          <h3>Pembayaran QRIS / DANA</h3>

          <p style="margin:10px 0;">
            Scan QR berikut menggunakan aplikasi DANA.
          </p>

          <div style="margin:20px 0;">
            <img 
              src="<?= $qr_url; ?>" 
              alt="QR Payment DANA"
              style="width:220px; height:220px; border-radius:16px; border:1px solid #ddd; padding:10px;"
            >
          </div>

          <p><strong>Nomor DANA:</strong></p>
          <p style="font-size:20px; font-weight:700;"><?= $dana_number; ?></p>

          <p style="color:#666; font-size:14px; margin-top:10px;">
            Total yang harus dibayar:
          </p>
          <h2 style="margin:8px 0 20px;">
            Rp <?= number_format($total, 0, ',', '.'); ?>
          </h2>

          <form action="process-payment.php" method="POST">
            <input type="hidden" name="event_id" value="<?= $data['event_id']; ?>">
            <input type="hidden" name="ticket_id" value="<?= $data['ticket_id']; ?>">
            <input type="hidden" name="qty" value="<?= $qty; ?>">
            <input type="hidden" name="total" value="<?= $total; ?>">
            <input type="hidden" name="payment_method" value="qris">
            <input type="hidden" name="payment_detail" value="DANA - <?= $dana_number; ?>">

            <button type="submit" class="btn-login" style="width:100%; margin-top:12px;">
              Saya Sudah Bayar
            </button>
          </form>

          <p style="font-size:12px; color:#888; margin-top:12px;">
            Setelah klik tombol ini, pembayaran akan otomatis dikonfirmasi.
          </p>
        </div>

      </div>

    <?php endif; ?>

  </div>
</section>
</main>

<?php include '../components/footer.php'; ?>

</body>
</html>