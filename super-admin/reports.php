<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Laporan</title>
  <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body>

<div class="wrapper">

<div class="sidebar">
  <h2>KarciZ Admin</h2>
  <a href="dashboard.php">Dashboard</a>
  <a href="manage-users.php">Kelola User</a>
  <a href="verify-organizer.php">Verifikasi Organizer</a>
  <a href="all-events.php">Semua Event</a>
  <a href="transactions.php">Transaksi</a>
  <a href="reports.php">Laporan</a>
</div>

<div class="main">
  <div class="topbar">
    <h3>Laporan</h3>
  </div>

  <div class="card-container">

    <div class="card">
      <h4>Total Penjualan</h4>
      <p>Rp 10.000.000</p>
    </div>

    <div class="card">
      <h4>Total Tiket Terjual</h4>
      <p>500</p>
    </div>

    <div class="card">
      <h4>Total Event</h4>
      <p>35</p>
    </div>

  </div>

</div>
</div>

</body>
</html>