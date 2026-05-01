<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

// sementara dummy (karena tabel event belum ada)
?>

<!DOCTYPE html>
<html>
<head>
  <title>Semua Event</title>
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
    <h3>Semua Event</h3>
  </div>

  <div class="table-container">
    <table>
      <tr>
        <th>No</th>
        <th>Nama Event</th>
        <th>Lokasi</th>
        <th>Tanggal</th>
      </tr>

      <tr>
        <td>1</td>
        <td>Konser Musik Nasional</td>
        <td>Padang</td>
        <td>15 Mei 2026</td>
      </tr>
    </table>
  </div>

</div>
</div>

</body>
</html>