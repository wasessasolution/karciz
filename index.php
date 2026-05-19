<?php
session_start();
include 'config.php';
require_once __DIR__ . '/lang/lang.php';

$category = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$location = trim($_GET['location'] ?? '');
$date = trim($_GET['date'] ?? '');
$includeExpired = isset($_GET['include_expired']);

$where = [];
$params = [];
$types = "";

if (!$includeExpired) {
    $where[] = "status='aktif'";
}

if ($search !== '') {
    $where[] = "(
        nama_event LIKE ?
        OR lokasi LIKE ?
        OR deskripsi LIKE ?
        OR tanggal LIKE ?
        OR jam_mulai LIKE ?
        OR jam_selesai LIKE ?
    )";

    $keyword = "%$search%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;

    $types .= "ssssss";
}

if ($category !== '') {
    $where[] = "kategori = ?";
    $params[] = $category;
    $types .= "s";
}

if ($location !== '') {
    $where[] = "lokasi LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

if ($date !== '') {
    $where[] = "tanggal = ?";
    $params[] = $date;
    $types .= "s";
}

$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $conn->prepare("
    SELECT * FROM events
    $whereSql
    ORDER BY tanggal ASC
");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

$past_events = $conn->query("SELECT * FROM events WHERE status='selesai' ORDER BY tanggal DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>KarciZ — Premium Event Ticketing</title>

  <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=4">
  <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=7">
  <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">
</head>
<body>

<?php include 'components/navbar.php'; ?>

<main>

  <section class="landing-slider">
    <div class="slider-track" id="sliderTrack">

      <div class="slider-item active">
        <img src="/Karciz/assets/images/banners/banner-1.jpg" alt="KarciZ Banner">
        <div class="slider-overlay">
          <span>Premium Event Ticketing</span>
          <h1>Temukan Event Terbaikmu di KarciZ</h1>
          <p>Beli tiket event, dapatkan e-ticket, dan masuk venue dengan QR validation.</p>
          <a href="#events">Jelajahi Event</a>
        </div>
      </div>

      <div class="slider-item">
        <img src="/Karciz/assets/images/banners/banner-2.jpg" alt="KarciZ Banner">
        <div class="slider-overlay">
          <span>Secure & Fast</span>
          <h1>Ticketing Modern untuk Semua Event</h1>
          <p>Platform aman untuk customer, promotor, dan staff gate.</p>
          <a href="register-promotor.php">Daftar Promotor</a>
        </div>
      </div>

      <div class="slider-item">
        <img src="/Karciz/assets/images/banners/banner-3.jpg" alt="KarciZ Banner">
        <div class="slider-overlay">
          <span>QR Ticket</span>
          <h1>Validasi Tiket Lebih Cepat</h1>
          <p>Scan QR ticket dan cegah penggunaan tiket duplikat.</p>
          <a href="#events">Cari Event</a>
        </div>
      </div>

    </div>

    <button class="slider-nav prev" id="prevSlide">‹</button>
    <button class="slider-nav next" id="nextSlide">›</button>

    <div class="slider-dots" id="sliderDots">
      <button class="active"></button>
      <button></button>
      <button></button>
    </div>
  </section>

  <section class="customer-benefit-section">
    <div class="container">
      <div class="benefit-panel">

        <div class="benefit-heading">
          <span>KarciZ Experience</span>
          <h2>Satu tiket, semua lebih mudah.</h2>
          <p>
            Dari cari event, beli tiket, sampai masuk venue, KarciZ membantu
            pengalaman event kamu jadi lebih praktis dan aman.
          </p>
        </div>

        <div class="benefit-grid">
          <div class="benefit-card">
            <div class="benefit-icon">🎟</div>
            <h3>E-Ticket Praktis</h3>
            <p>Tiket tersimpan digital dan bisa dibuka kembali melalui akun KarciZ.</p>
          </div>

          <div class="benefit-card">
            <div class="benefit-icon">🔒</div>
            <h3>Validasi Aman</h3>
            <p>QR ticket hanya bisa digunakan satu kali untuk mencegah duplikasi tiket.</p>
          </div>

          <div class="benefit-card">
            <div class="benefit-icon">⚡</div>
            <h3>Check-in Cepat</h3>
            <p>Masuk venue lebih cepat dengan sistem scan QR oleh staff gate.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="premium-event-section" id="events">
    <div class="container">

      <div class="section-heading">
        <span>Events</span>
        <h2>Event Populer</h2>
        <p>Pilih event favoritmu dan dapatkan e-ticket resmi dari KarciZ.</p>
      </div>

      <div class="premium-event-grid">

        <?php if ($events->num_rows > 0) { ?>
          <?php while($row = $events->fetch_assoc()) { ?>
            <div class="premium-event-card">

              <div class="premium-event-img-wrap">
                <?php if (!empty($row['banner'])) { ?>
                  <img src="/Karciz/assets/images/events/<?= htmlspecialchars($row['banner']); ?>" alt="<?= htmlspecialchars($row['nama_event']); ?>">
                <?php } else { ?>
                  <div class="premium-empty-banner">No Banner</div>
                <?php } ?>

                <div class="event-date-badge">
                  <?= date('d M', strtotime($row['tanggal'])); ?>
                </div>
              </div>

              <div class="premium-event-body">
                <h3><?= htmlspecialchars($row['nama_event']); ?></h3>
                <p class="event-location"><?= htmlspecialchars($row['lokasi']); ?></p>

                <p class="event-date">
                  <?= date('d M Y', strtotime($row['tanggal'])); ?>
                  <?php if (!empty($row['jam_mulai'])): ?>
                    • <?= substr($row['jam_mulai'], 0, 5); ?>
                  <?php endif; ?>
                </p>

                <a href="customer/event-detail.php?id=<?= $row['id']; ?>" class="premium-ticket-btn">
                  Beli Tiket
                </a>
              </div>

            </div>
          <?php } ?>
        <?php } else { ?>
          <div class="empty-state">
            <h3>Event tidak ditemukan</h3>
            <p>Coba ubah kata kunci atau filter pencarian.</p>
          </div>
        <?php } ?>

      </div>
    </div>
  </section>

  <section class="premium-how">
    <div class="container">
      <div class="section-heading">
        <span>How it works</span>
        <h2>Cara Pesan Tiket</h2>
        <p>Simple, cepat, dan siap digunakan saat masuk venue.</p>
      </div>

      <div class="how-grid">
        <div class="how-card"><b>01</b><h3>Pilih Event</h3><p>Temukan event yang ingin kamu hadiri.</p></div>
        <div class="how-card"><b>02</b><h3>Pilih Tiket</h3><p>Pilih kategori tiket dan jumlah pembelian.</p></div>
        <div class="how-card"><b>03</b><h3>Bayar</h3><p>Lakukan pembayaran sesuai instruksi checkout.</p></div>
        <div class="how-card"><b>04</b><h3>Dapatkan QR</h3><p>Gunakan QR ticket untuk validasi di venue.</p></div>
      </div>
    </div>
  </section>

  <section class="premium-past-section">
    <div class="container">
      <div class="section-heading">
        <span>Archive</span>
        <h2>Event Sebelumnya</h2>
      </div>

      <div class="past-grid">
        <?php if ($past_events->num_rows > 0) { ?>
          <?php while($row = $past_events->fetch_assoc()) { ?>
            <div class="past-card">
              <h3><?= htmlspecialchars($row['nama_event']); ?></h3>
              <p><?= htmlspecialchars($row['lokasi']); ?> • Event selesai</p>
            </div>
          <?php } ?>
        <?php } else { ?>
          <p>Belum ada event selesai.</p>
        <?php } ?>
      </div>
    </div>
  </section>

  <section class="premium-faq" id="faq">
    <div class="container">
      <div class="faq-layout">

        <div class="faq-heading">
          <span>FAQ</span>
          <h2>Pertanyaan Umum</h2>
          <p>Temukan jawaban cepat seputar pembelian tiket, validasi QR, dan penggunaan akun KarciZ.</p>
        </div>

        <div class="faq-accordion">
          <div class="faq-accordion-item active">
            <button type="button" class="faq-question"><span>Bagaimana cara membeli tiket di KarciZ?</span><b>−</b></button>
            <div class="faq-answer"><p>Pilih event, login, pilih kategori tiket, checkout, lalu e-ticket tersedia di My KarciZ.</p></div>
          </div>

          <div class="faq-accordion-item">
            <button type="button" class="faq-question"><span>Bagaimana proses validasi tiket?</span><b>+</b></button>
            <div class="faq-answer"><p>Staff gate melakukan scan QR ticket dan sistem mencegah tiket digunakan lebih dari satu kali.</p></div>
          </div>

          <div class="faq-accordion-item">
            <button type="button" class="faq-question"><span>Bagaimana jika QR ticket hilang?</span><b>+</b></button>
            <div class="faq-answer"><p>Anda bisa membuka kembali e-ticket melalui menu My KarciZ selama login dengan akun yang sama.</p></div>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".slider-item");
  const dots = document.querySelectorAll(".slider-dots button");
  const next = document.getElementById("nextSlide");
  const prev = document.getElementById("prevSlide");
  let index = 0;

  function showSlide(i) {
    slides.forEach(s => s.classList.remove("active"));
    dots.forEach(d => d.classList.remove("active"));

    slides[i].classList.add("active");
    dots[i].classList.add("active");
  }

  function nextSlide() {
    index = (index + 1) % slides.length;
    showSlide(index);
  }

  function prevSlide() {
    index = (index - 1 + slides.length) % slides.length;
    showSlide(index);
  }

  next.addEventListener("click", nextSlide);
  prev.addEventListener("click", prevSlide);

  dots.forEach((dot, i) => {
    dot.addEventListener("click", function () {
      index = i;
      showSlide(index);
    });
  });

  setInterval(nextSlide, 5000);

  const faqItems = document.querySelectorAll(".faq-accordion-item");

  faqItems.forEach(item => {
    const button = item.querySelector(".faq-question");
    const icon = button.querySelector("b");

    button.addEventListener("click", function () {
      faqItems.forEach(other => {
        if (other !== item) {
          other.classList.remove("active");
          other.querySelector("b").textContent = "+";
        }
      });

      item.classList.toggle("active");
      icon.textContent = item.classList.contains("active") ? "−" : "+";
    });
  });
});
</script>

</body>
</html>