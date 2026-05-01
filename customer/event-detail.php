<?php
session_start();
include '../config.php';

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$event_id = $_GET['id'];

// ambil data event
$stmt = $conn->prepare("SELECT * FROM events WHERE id=?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

// ambil tiket event
$tickets = $conn->query("SELECT * FROM tickets WHERE event_id = $event_id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $event['nama_event']; ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../components/navbar.php'; ?>

<div class="container" style="margin-top:40px;">

  <h1><?= $event['nama_event']; ?></h1>
  <p><?= $event['lokasi']; ?> • <?= date('d M Y', strtotime($event['tanggal'])); ?></p>

  <hr style="margin:20px 0;">

  <h2>Pilih Tiket</h2>

  <form action="checkout.php" method="POST">

    <input type="hidden" name="event_id" value="<?= $event_id ?>">

    <?php while($row = $tickets->fetch_assoc()) { ?>

      <div class="event-card" style="margin-bottom:15px;">
        <h3><?= $row['nama_tiket']; ?></h3>
        <p>Rp <?= number_format($row['harga']); ?></p>
        <p>Stok: <?= $row['stok']; ?></p>

        <input 
          type="number" 
          name="qty[<?= $row['id']; ?>]" 
          min="0" 
          max="<?= $row['stok']; ?>" 
          value="0"
        >
      </div>

    <?php } ?>

    <button type="submit" class="btn-login" style="margin-top:20px;">
      Lanjut Checkout
    </button>

  </form>

</div>

<?php include '../components/footer.php'; ?>

</body>
</html>