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
  <title>Transaksi</title>
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
    <h3>Transaksi</h3>
  </div>

  <div class="table-container">
    <table>
      <tr>
        <th>No</th>
        <th>User</th>
        <th>Event</th>
        <th>Total</th>
        <th>Status</th>
      </tr>

      <tr>
        <td>1</td>
        <td>User A</td>
        <td>Konser Musik</td>
        <td>Rp 150.000</td>
        <td>Berhasil</td>
      </tr>
    </table>
  </div>

</div>
</div>

</body>
</html>