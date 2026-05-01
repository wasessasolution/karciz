<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - Riwayat Transaksi</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <!-- Navbar -->
  <div id="navbar"></div>

  <!-- History Transaction -->
  <section class="event-section">
    <div class="container">

      <div class="main-banner">
        <h1>Riwayat Transaksi</h1>
        <p>Lihat seluruh pembelian tiket dan status transaksi Anda di KarciZ</p>
      </div>

      <div style="margin-top: 30px; display: grid; gap: 20px;">

        <div class="event-card">
          <h2>Konser Musik Nasional 2026</h2>
          <p><strong>Order ID:</strong> KCZ-2026-00125</p>
          <p><strong>Tanggal Pembelian:</strong> 10 Mei 2026</p>
          <p><strong>Venue:</strong> VIP Area</p>
          <p><strong>Total:</strong> Rp 1.000.000</p>
          <p><strong>Status:</strong> Paid</p>
        </div>

        <div class="event-card">
          <h2>Festival Kuliner Nusantara</h2>
          <p><strong>Order ID:</strong> KCZ-2026-00126</p>
          <p><strong>Tanggal Pembelian:</strong> 12 Mei 2026</p>
          <p><strong>Venue:</strong> Festival Area</p>
          <p><strong>Total:</strong> Rp 500.000</p>
          <p><strong>Status:</strong> Waiting Payment</p>
        </div>

      </div>

    </div>
  </section>

  <!-- Footer -->
  <div id="footer"></div>

  <script>
    fetch('../components/navbar.html')
      .then(response => response.text())
      .then(data => {
        document.getElementById('navbar').innerHTML = data;
      });

    fetch('../components/footer.html')
      .then(response => response.text())
      .then(data => {
        document.getElementById('footer').innerHTML = data;
      });
  </script>

</body>
</html>
