<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - Detail Event</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

<div id="navbar"></div>

<section class="event-section">
  <div class="container">

    <div class="main-banner">
      <h1 id="eventTitle">Detail Event</h1>
      <p id="eventLocation">Lokasi Event</p>
    </div>

    <div class="event-grid" style="margin-top: 30px;">

      <div class="event-card">
        <h2>Deskripsi Event</h2>
        <p id="eventDescription"></p>
      </div>

      <div class="event-card">
        <h2>Pilih Venue / Tiket</h2>

        <form id="ticketForm">
          <label style="display:block; margin-bottom:12px;">
            <input type="radio" name="ticket_type" value="VIP Area" data-price="500000" checked />
            <strong>VIP Area</strong> — Rp 500.000
          </label>

          <label style="display:block; margin-bottom:12px;">
            <input type="radio" name="ticket_type" value="Festival Area" data-price="250000" />
            <strong>Festival Area</strong> — Rp 250.000
          </label>

          <label style="display:block; margin-bottom:12px;">
            <input type="radio" name="ticket_type" value="Regular Area" data-price="150000" />
            <strong>Regular Area</strong> — Rp 150.000
          </label>

          <label style="display:block; margin-top:20px; margin-bottom:10px;">
            Jumlah Tiket
          </label>

          <input
            type="number"
            id="ticketQty"
            min="1"
            max="10"
            value="1"
            style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"
          />

          <div style="margin-top:24px;">
            <button
              type="button"
              onclick="saveTicketAndCheckout()"
              class="btn-login"
              style="width:100%;"
            >
              Beli Tiket
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>

<div id="footer"></div>

<script>
const events = {
  konser: {
    title: 'Konser Musik Nasional 2026',
    location: 'Padang • 15 Mei 2026 • GOR Haji Agus Salim',
    description: 'Nikmati konser musik terbesar tahun ini bersama artis nasional, panggung spektakuler, dan suasana festival yang meriah.'
  },
  kuliner: {
    title: 'Festival Kuliner Nusantara',
    location: 'Jakarta • 18 Mei 2026 • Jakarta Convention Center',
    description: 'Festival kuliner terbesar dengan berbagai tenant makanan nusantara, chef spesial, dan hiburan menarik.'
  },
  seminar: {
    title: 'Seminar Digital Business',
    location: 'Bandung • 20 Mei 2026 • Aula Bisnis Digital',
    description: 'Seminar bisnis digital untuk entrepreneur muda, startup founder, dan pelaku UMKM modern.'
  }
};

const params = new URLSearchParams(window.location.search);
const selectedEvent = params.get('event') || 'konser';
const eventData = events[selectedEvent] || events.konser;

document.getElementById('eventTitle').innerText = eventData.title;
document.getElementById('eventLocation').innerText = eventData.location;
document.getElementById('eventDescription').innerText = eventData.description;

function saveTicketAndCheckout() {
  const selected = document.querySelector('input[name="ticket_type"]:checked');
  const qty = document.getElementById('ticketQty').value;

  const ticketType = selected.value;
  const price = parseInt(selected.dataset.price);
  const total = price * qty;

  localStorage.setItem('event_name', eventData.title);
  localStorage.setItem('event_location', eventData.location);
  localStorage.setItem('ticket_type', ticketType);
  localStorage.setItem('ticket_qty', qty);
  localStorage.setItem('ticket_price', price);
  localStorage.setItem('ticket_total', total);

  window.location.href = 'checkout.html';
}

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