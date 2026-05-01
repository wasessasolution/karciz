<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - Checkout</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <!-- Navbar -->
  <div id="navbar"></div>

  <!-- Checkout Section -->
  <section class="event-section">
    <div class="container">

      <div class="main-banner">
        <h1>Checkout Tiket</h1>
        <p>Pastikan detail pesanan Anda sudah benar sebelum melakukan pembayaran</p>
      </div>

      <div class="event-grid" style="margin-top: 30px;">

        <!-- Ringkasan Pesanan -->
        <div class="event-card">
          <h2>Ringkasan Pesanan</h2>
          <p><strong>Event:</strong> Konser Musik Nasional 2026</p>
          <p><strong>Venue:</strong> VIP Area</p>
          <p><strong>Jumlah Tiket:</strong> 2</p>
          <p><strong>Harga per Tiket:</strong> Rp 500.000</p>
          <hr style="margin: 16px 0;">
          <p><strong>Total Pembayaran:</strong> Rp 1.000.000</p>
        </div>

        <!-- Metode Pembayaran -->
        <div class="event-card">
          <h2>Metode Pembayaran</h2>

          <form class="payment-form">
            <label style="display:block; margin-bottom:12px;">
              <input type="radio" name="payment_method" value="transfer_bank" checked />
              Transfer Bank
            </label>

            <label style="display:block; margin-bottom:12px;">
              <input type="radio" name="payment_method" value="ewallet" />
              E-Wallet
            </label>

            <label style="display:block; margin-bottom:12px;">
              <input type="radio" name="payment_method" value="qris" />
              QRIS
            </label>

            <div style="margin-top: 24px;">
              <a href="/customer/my_ticket.html" class="btn-login" style="display:block; text-align:center;">
                Konfirmasi Pembayaran
              </a>
            </div>
          </form>
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