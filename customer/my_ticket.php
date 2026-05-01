<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - My Ticket</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <!-- Navbar -->
  <div id="navbar"></div>

  <!-- My Ticket Section -->
  <section class="event-section">
    <div class="container">

      <div class="main-banner">
        <h1>My Ticket</h1>
        <p>
          Tiket Anda berhasil dibuat. Simpan e-ticket dan QR Code untuk validasi masuk event.
        </p>
      </div>

      <div class="event-grid" style="margin-top: 30px;">

        <!-- Detail Ticket -->
        <div class="event-card">
          <h2>Detail E-Ticket</h2>

          <p><strong>Event:</strong> <span id="eventName"></span></p>
          <p><strong>Tanggal:</strong> 15 Mei 2026</p>
          <p><strong>Lokasi:</strong> GOR Haji Agus Salim, Padang</p>
          <p><strong>Venue:</strong> <span id="ticketType"></span></p>
          <p><strong>Jumlah Tiket:</strong> <span id="ticketQty"></span></p>
          <p><strong>Status:</strong> Paid</p>
          <p><strong>Order ID:</strong> <span id="orderId"></span></p>
          <p><strong>Total:</strong> Rp <span id="ticketTotal"></span></p>
        </div>

        <!-- QR Ticket -->
        <div class="event-card" style="text-align:center;">
          <h2>QR Ticket</h2>

          <img
            src="../assets/images/profile/default-profile.png"
            alt="QR Code"
            style="width:220px; height:220px; object-fit:cover; margin:20px auto; display:block; border-radius:12px;"
          />

          <p>Scan QR ini saat masuk ke venue event</p>

          <div style="margin-top: 24px;">
            <a href="#" class="btn-login" style="display:block; text-align:center;">
              Download E-Ticket
            </a>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- Footer -->
  <div id="footer"></div>

  <script>
    const eventName = localStorage.getItem('event_name') || '-';
    const ticketType = localStorage.getItem('ticket_type') || '-';
    const ticketQty = localStorage.getItem('ticket_qty') || '-';
    const ticketTotal = Number(localStorage.getItem('ticket_total') || 0)
      .toLocaleString('id-ID');

    const randomOrderId = 'KCZ-2026-' + Math.floor(10000 + Math.random() * 90000);

    document.getElementById('eventName').innerText = eventName;
    document.getElementById('ticketType').innerText = ticketType;
    document.getElementById('ticketQty').innerText = ticketQty;
    document.getElementById('ticketTotal').innerText = ticketTotal;
    document.getElementById('orderId').innerText = randomOrderId;

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