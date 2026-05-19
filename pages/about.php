<?php
session_start();
include '../config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tentang Kami - KarciZ</title>

  <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=2">
  <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/about.css?v=1">
</head>
<body>

<?php include '../components/navbar.php'; ?>

<main class="about-page">

  <section class="about-hero">
    <div class="about-pattern">
      <span>🎟</span><span>★</span><span>K</span><span>🎫</span><span>🎤</span>
    </div>

    <div class="container">
      <div class="about-hero-grid">

        <div class="about-hero-copy">
          <div class="about-badge">Tentang KarciZ</div>
          <h1>Membuat pengalaman beli tiket jadi lebih mudah dan aman.</h1>
          <p>
            KarciZ hadir sebagai platform ticketing modern yang membantu customer
            menemukan event favorit, membeli tiket, mendapatkan e-ticket, dan
            melakukan validasi masuk venue dengan QR ticket.
          </p>

          <div class="about-actions">
            <a href="/Karciz/index.php#events" class="about-btn-primary">Jelajahi Event</a>
            <a href="/Karciz/register-promotor.php" class="about-btn-secondary">Jadi Promotor</a>
          </div>
        </div>

        <div class="about-hero-card">
          <div class="about-card-icon">K</div>
          <h3>KarciZ Ticketing</h3>
          <p>
            Satu platform untuk customer, promotor, dan staff gate agar proses
            ticketing event menjadi lebih rapi.
          </p>

          <div class="about-mini-stats">
            <div>
              <strong>QR</strong>
              <span>Validation</span>
            </div>
            <div>
              <strong>24/7</strong>
              <span>Access</span>
            </div>
            <div>
              <strong>Live</strong>
              <span>Tracking</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="about-story">
    <div class="container">
      <div class="about-section-heading">
        <span>Our Story</span>
        <h2>Kenapa KarciZ dibuat?</h2>
        <p>
          Banyak event masih mengelola tiket secara manual, rawan data tercecer,
          validasi lambat, dan sulit melakukan tracking tiket. KarciZ dibuat untuk
          menyederhanakan proses itu dalam satu sistem.
        </p>
      </div>

      <div class="story-grid">
        <div class="story-card">
          <div class="story-number">01</div>
          <h3>Mudah untuk Customer</h3>
          <p>Customer bisa melihat event, membeli tiket, dan membuka e-ticket kapan saja.</p>
        </div>

        <div class="story-card">
          <div class="story-number">02</div>
          <h3>Praktis untuk Promotor</h3>
          <p>Promotor bisa membuat event, mengatur tiket, memantau penjualan, dan settlement.</p>
        </div>

        <div class="story-card">
          <div class="story-number">03</div>
          <h3>Aman saat Check-in</h3>
          <p>Staff gate bisa validasi QR tiket dan mencegah tiket digunakan lebih dari sekali.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="about-values">
    <div class="container">
      <div class="values-panel">
        <div>
          <span>Our Values</span>
          <h2>Simple, secure, dan siap dipakai untuk event nyata.</h2>
        </div>

        <div class="values-list">
          <div>
            <h4>Transparan</h4>
            <p>Data transaksi dan tiket bisa dipantau dengan jelas.</p>
          </div>

          <div>
            <h4>Modern</h4>
            <p>Tampilan dan alur sistem dibuat mudah dipahami semua pengguna.</p>
          </div>

          <div>
            <h4>Aman</h4>
            <p>Validasi tiket dibuat untuk mencegah penggunaan tiket ganda.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="about-team">
    <div class="container">
      <div class="about-section-heading center">
        <span>Our Team</span>
        <h2>Tim di balik KarciZ</h2>
        <!-- <p>
          Tambahkan foto tim kamu di folder
          <strong>assets/images/team/</strong>, lalu sesuaikan nama file di bawah.
        </p> -->
      </div>

     <div class="team-grid team-grid-3">

        <div class="team-card featured">
            <img src="/Karciz/assets/images/team/bagus.jpg" alt="Bagus Fatur Rahman">
            <div>
            <span>Founder</span>
            <h3>Bagus Fatur Rahman, S.Kom.</h3>
            <p>
                Founder Wasessa Solution Tech sekaligus penggagas project KarciZ.
                Berperan dalam arah produk, konsep platform, dan pengembangan solusi ticketing digital.
            </p>
            </div>
        </div>

        <div class="team-card">
            <img src="/Karciz/assets/images/team/arjun.png" alt="Arjuna Satria Dwi Sumardi">
            <div>
            <span>System & Database Engineer</span>
            <h3>Arjuna Satria Dwi Sumardi, S.Kom.</h3>
            <p>
                Bertanggung jawab dalam pengelolaan database, perancangan server,
                serta membantu pembangunan sistem KarciZ dari sisi teknis.
            </p>
            </div>
        </div>

        <div class="team-card">
            <img src="/Karciz/assets/images/team/riyan.jpg" alt="Muhammad Riyan Arsya">
            <div>
            <span>UI/UX & Partnership</span>
            <h3>M. Arsya Fadrian Imral, S.Kom.</h3>
            <p>
                Berperan dalam perancangan pengalaman pengguna, tampilan antarmuka,
                serta membantu proses pendekatan calon promotor event.
            </p>
            </div>
        </div>

        </div>
    </div>
  </section>

  <section class="about-cta">
    <div class="container">
      <div class="about-cta-box">
        <h2>Siap mulai pakai KarciZ?</h2>
        <p>Jelajahi event aktif atau daftar sebagai promotor untuk mulai menjual tiket event Anda.</p>

        <div class="about-actions center-action">
          <a href="/Karciz/index.php#events" class="about-btn-primary">Cari Event</a>
          <a href="/Karciz/register-promotor.php" class="about-btn-secondary dark">Daftar Promotor</a>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include '../components/footer.php'; ?>

</body>
</html>