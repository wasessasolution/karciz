<?php
session_start();

// 🔐 PROTEKSI
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Super Admin Dashboard - KarciZ</title>
  <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body>

<div class="wrapper">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h2>KarciZ Admin</h2>

    <a href="./dashboard.php">Dashboard</a>
    <a href="./manage-users.php">Kelola User</a>
    <a href="./verify-organizer.php">Verifikasi Organizer</a>
    <a href="./all-events.php">Semua Event</a>
    <a href="./transactions.php">Transaksi</a>
    <a href="./reports.php">Laporan</a>
  </div>

  <!-- MAIN -->
  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <h3>Dashboard</h3>

      <div class="user-info">
        <span>👤 <?php echo $username; ?></span>
            <a href="/Karciz/logout.php" class="logout-btn">Logout</a>>
          <button class="logout-btn">Logout</button>
        </a>
      </div>
    </div>

    <!-- CARD -->
    <div class="card-container">

      <div class="card">
        <h4>Total User</h4>
        <p>120</p>
      </div>

      <div class="card">
        <h4>Total Event</h4>
        <p>35</p>
      </div>

      <div class="card">
        <h4>Total Transaksi</h4>
        <p>500</p>
      </div>

      <div class="card">
        <h4>Organizer Pending</h4>
        <p>5</p>
      </div>

    </div>

  </div>

</div>

</body>
</html>