<?php
session_start();
include 'config.php';

// 🔐 PROTEKSI LOGIN
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// ambil event aktif
$events = $conn->query("SELECT * FROM events WHERE status='aktif' ORDER BY tanggal ASC");

// ambil event selesai
$past_events = $conn->query("SELECT * FROM events WHERE status='selesai'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - Landing Page</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

  <!-- ✅ NAVBAR -->
  <?php include 'components/navbar.php'; ?>

  <!-- Banner -->
  <section class="banner-section">
    <div class="container">
      <div class="main-banner">
        <h1>Temukan Event Terbaikmu di KarciZ</h1>
        <p>
          Beli tiket event dengan mudah, cepat, dan aman hanya dalam satu platform.
        </p>
      </div>
    </div>
  </section>

  <!-- ✅ EVENT AKTIF -->
  <section class="event-section">
    <div class="container">
      <h2>Event Populer</h2>

      <div class="event-grid">

        <?php if ($events->num_rows > 0) { ?>
          <?php while($row = $events->fetch_assoc()) { ?>

            <div class="event-card">
              <h3><?= htmlspecialchars($row['nama_event']); ?></h3>
              <p>
                <?= htmlspecialchars($row['lokasi']); ?> • 
                <?= date('d M Y', strtotime($row['tanggal'])); ?>
              </p>

              <div style="margin-top:16px;">
                <a href="customer/event-detail.php?id=<?= $row['id']; ?>" 
                   class="btn-login"
                   style="display:block; text-align:center;">
                  Beli Tiket
                </a>
              </div>
            </div>

          <?php } ?>
        <?php } else { ?>
          <p>Tidak ada event tersedia</p>
        <?php } ?>

      </div>
    </div>
  </section>

  <!-- ✅ EVENT SELESAI -->
  <section class="event-section">
    <div class="container">
      <h2>Event Sebelumnya</h2>

      <div class="event-grid">

        <?php if ($past_events->num_rows > 0) { ?>
          <?php while($row = $past_events->fetch_assoc()) { ?>

            <div class="event-card">
              <h3><?= htmlspecialchars($row['nama_event']); ?></h3>
              <p><?= htmlspecialchars($row['lokasi']); ?> • Event Selesai</p>
            </div>

          <?php } ?>
        <?php } else { ?>
          <p>Belum ada event selesai</p>
        <?php } ?>

      </div>
    </div>
  </section>

  <!-- Rules -->
  <section class="rules-section">
    <div class="container">
      <h2>Rules Pesan Tiket</h2>
      <ol>
        <li>Pilih event yang ingin Anda hadiri</li>
        <li>Pilih venue atau kategori tiket yang tersedia</li>
        <li>Tentukan jumlah tiket yang ingin dibeli</li>
        <li>Lakukan pembayaran sesuai metode yang tersedia</li>
        <li>Simpan e-ticket dan QR Code untuk validasi masuk event</li>
      </ol>
    </div>
  </section>

  <!-- FAQ -->
  <section class="faq-section">
    <div class="container">
      <h2>FAQ</h2>

      <div class="faq-item">
        <h4>Bagaimana cara membeli tiket?</h4>
        <p>Pilih event, checkout tiket, lakukan pembayaran, dan e-ticket akan otomatis dikirim.</p>
      </div>

      <div class="faq-item">
        <h4>Apakah tiket bisa dikembalikan?</h4>
        <p>Kebijakan refund mengikuti aturan masing-masing promotor event.</p>
      </div>

      <div class="faq-item">
        <h4>Bagaimana jika QR Ticket hilang?</h4>
        <p>Anda dapat melihat kembali tiket pada menu My KarciZ.</p>
      </div>

    </div>
  </section>

  <!-- ✅ FOOTER -->
  <?php include 'components/footer.php'; ?>

</body>
</html>