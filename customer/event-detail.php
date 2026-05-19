<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$event_id = intval($_GET['id']);

/* Ambil event */
$stmt = $conn->prepare("
    SELECT events.*, promotor.nama_brand
    FROM events
    LEFT JOIN promotor ON events.organizer_id = promotor.id
    WHERE events.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event tidak ditemukan.";
    exit;
}

/* Cek event expired */
$jam_selesai = !empty($event['jam_selesai']) ? $event['jam_selesai'] : '23:59:59';
$event_end = strtotime($event['tanggal'] . ' ' . $jam_selesai);
$event_expired = time() > $event_end || $event['status'] === 'selesai';

/* Ambil tiket event */
$stmt = $conn->prepare("
    SELECT * FROM tickets
    WHERE event_id = ?
    ORDER BY harga ASC
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$tickets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event['nama_event']); ?> - KarciZ</title>
      <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=1">
      <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=5">
      <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">
</head>
<body>

<?php include '../components/navbar.php'; ?>

<main>
    <section class="event-detail-section">
        <div class="container">

            <div class="event-detail-card">

                <?php if (!empty($event['banner'])) { ?>
                    <img 
                        src="/Karciz/assets/images/events/<?= htmlspecialchars($event['banner']); ?>" 
                        alt="<?= htmlspecialchars($event['nama_event']); ?>"
                        class="event-detail-banner"
                    >
                <?php } else { ?>
                    <div class="event-detail-banner empty-banner">No Banner</div>
                <?php } ?>

                <div class="event-detail-content">
                    <h1><?= htmlspecialchars($event['nama_event']); ?></h1>

                    <p class="event-meta">
                        <?= htmlspecialchars($event['lokasi']); ?> •
                        <?= date('d M Y', strtotime($event['tanggal'])); ?>
                    </p>

                    <p class="event-organizer">
                        Promotor: <?= htmlspecialchars($event['nama_brand'] ?? 'KarciZ Organizer'); ?>
                    </p>

                    <hr>

                    <h3>Deskripsi Event</h3>
                    <p class="event-description">
                        <?= nl2br(htmlspecialchars($event['deskripsi'])); ?>
                    </p>
                </div>

            </div>

            <div class="ticket-list-card">
              <h2>Pilih Tiket</h2>

              <?php if ($event_expired): ?>

                <div class="event-card" style="padding:24px; margin-top:20px;">
                  <h3>Event Sudah Selesai</h3>
                  <p>Penjualan tiket telah ditutup.</p>
                </div>

              <?php else: ?>

                <?php if ($tickets->num_rows > 0) { ?>
                  <form action="checkout.php" method="POST" id="ticketForm">

                    <input type="hidden" name="event_id" value="<?= $event_id; ?>">
                    <input type="hidden" name="ticket_id" id="selectedTicketId">

                    <?php while ($ticket = $tickets->fetch_assoc()) { ?>
                      <div class="ticket-option <?= $ticket['stok'] <= 0 ? 'ticket-disabled' : ''; ?>">

                        <label class="ticket-radio-area">
                          <input 
                            type="radio"
                            name="ticket_choice"
                            value="<?= $ticket['id']; ?>"
                            data-stok="<?= $ticket['stok']; ?>"
                            <?= $ticket['stok'] <= 0 ? 'disabled' : ''; ?>
                          >

                          <div>
                            <h3><?= htmlspecialchars($ticket['nama_tiket']); ?></h3>
                            <p>Stok tersedia: <?= $ticket['stok']; ?></p>
                            <strong>Rp <?= number_format($ticket['harga'], 0, ',', '.'); ?></strong>
                          </div>
                        </label>

                      </div>
                    <?php } ?>

                    <div class="qty-box">
                      <label>Jumlah Tiket</label>
                      <input 
                        type="number" 
                        name="qty" 
                        id="ticketQty"
                        min="1"
                        value="0"
                        disabled
                      >
                    </div>

                    <button type="submit" class="btn-login" id="checkoutBtn" disabled>
                      Lanjut Checkout
                    </button>

                  </form>
                <?php } else { ?>
                  <p>Belum ada tiket tersedia untuk event ini.</p>
                <?php } ?>

              <?php endif; ?>
            </div>

        </div>
    </section>
</main>

<?php include '../components/footer.php'; ?>

<script>
  const ticketRadios = document.querySelectorAll('input[name="ticket_choice"]');
  const qtyInput = document.getElementById('ticketQty');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const selectedTicketId = document.getElementById('selectedTicketId');

  ticketRadios.forEach(radio => {
    radio.addEventListener('change', function () {
      const stok = parseInt(this.dataset.stok);

      selectedTicketId.value = this.value;
      qtyInput.disabled = false;
      qtyInput.min = 1;
      qtyInput.max = stok;
      qtyInput.value = 1;

      checkoutBtn.disabled = false;
    });
  });

  qtyInput.addEventListener('input', function () {
    let qty = parseInt(this.value);
    const max = parseInt(this.max);

    if (isNaN(qty) || qty < 1) {
      this.value = 1;
      qty = 1;
    }

    if (qty > max) {
      this.value = max;
      qty = max;
    }

    checkoutBtn.disabled = !selectedTicketId.value || qty < 1 || qty > max;
  });

  document.getElementById('ticketForm')?.addEventListener('submit', function(e) {
    const qty = parseInt(qtyInput.value);
    const max = parseInt(qtyInput.max);

    if (!selectedTicketId.value || qty < 1 || qty > max) {
      e.preventDefault();
      alert('Pilih tiket dan jumlah tiket yang valid.');
    }
  });
</script>

</body>
</html>