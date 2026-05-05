<?php
session_start();
include '../config.php';

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

// Ambil data event + tiket
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

if (!$error) {
    $total = $data['harga'] * $qty;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - Checkout</title>
  <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=5" />
</head>
<body>

<?php include '../components/navbar.php'; ?>

<main>
  <section class="event-section">
    <div class="container">

      <div class="main-banner">
        <h1>Checkout Tiket</h1>
        <p>Pastikan detail pesanan Anda sudah benar sebelum melakukan pembayaran</p>
      </div>

      <?php if ($error): ?>

        <div class="event-card" style="padding:24px; margin-top:30px;">
          <h2>Checkout Gagal</h2>
          <p style="color:red; margin-top:10px;"><?= htmlspecialchars($error); ?></p>
          <br>
          <a href="../index.php" class="btn-login">Kembali ke Home</a>
        </div>

      <?php else: ?>

        <div class="event-grid" style="margin-top: 30px;">

          <div class="event-card">
            <h3>Ringkasan Pesanan</h3>

            <p><strong>Event:</strong> <?= htmlspecialchars($data['nama_event']); ?></p>
            <p><strong>Lokasi:</strong> <?= htmlspecialchars($data['lokasi']); ?></p>
            <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($data['tanggal'])); ?></p>
            <p><strong>Jenis Tiket:</strong> <?= htmlspecialchars($data['nama_tiket']); ?></p>
            <p><strong>Jumlah Tiket:</strong> <?= $qty; ?></p>
            <p><strong>Harga per Tiket:</strong> Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>

            <hr style="margin: 16px 22px;">

            <p>
              <strong>Total Pembayaran:</strong>
              Rp <?= number_format($total, 0, ',', '.'); ?>
            </p>
          </div>

          <div class="event-card">
            <h3>Metode Pembayaran</h3>

            <form action="process-payment.php" method="POST" style="padding: 0 22px 22px;">

              <input type="hidden" name="event_id" value="<?= $data['event_id']; ?>">
              <input type="hidden" name="ticket_id" value="<?= $data['ticket_id']; ?>">
              <input type="hidden" name="qty" value="<?= $qty; ?>">
              <input type="hidden" name="total" value="<?= $total; ?>">

              <!-- PILIH METODE -->
              <label>
                <input type="radio" name="payment_method" value="bank" checked onclick="showPayment('bank')">
                Transfer Bank
              </label>

              <label>
                <input type="radio" name="payment_method" value="ewallet" onclick="showPayment('ewallet')">
                E-Wallet
              </label>

              <label>
                <input type="radio" name="payment_method" value="qris" onclick="showPayment('qris')">
                QRIS
              </label>

              <!-- BANK -->
              <div id="bankBox" class="payment-box">
                <select name="payment_detail">
                  <option value="BCA">BCA</option>
                  <option value="Mandiri">Mandiri</option>
                  <option value="BNI">BNI</option>
                </select>
              </div>

              <!-- EWALLET -->
              <div id="ewalletBox" class="payment-box" style="display:none;">
                <select name="payment_detail">
                  <option value="OVO">OVO</option>
                  <option value="DANA">DANA</option>
                  <option value="GoPay">GoPay</option>
                </select>
              </div>

              <!-- QRIS -->
              <div id="qrisBox" class="payment-box" style="display:none;">
                <p>Silakan scan QRIS setelah konfirmasi pembayaran.</p>
              </div>

              <button type="submit" class="btn-login" style="margin-top: 12px;">
                Konfirmasi Pembayaran
              </button>

            </form>
          </div>

        </div>

      <?php endif; ?>

    </div>
  </section>
</main>

<?php include '../components/footer.php'; ?>

<script>
function showPayment(type) {

  document.getElementById('bankBox').style.display = 'none';
  document.getElementById('ewalletBox').style.display = 'none';
  document.getElementById('qrisBox').style.display = 'none';

  if (type === 'bank') {
    document.getElementById('bankBox').style.display = 'block';
  }

  if (type === 'ewallet') {
    document.getElementById('ewalletBox').style.display = 'block';
  }

  if (type === 'qris') {
    document.getElementById('qrisBox').style.display = 'block';
  }
}
</script>

</body>
</html>