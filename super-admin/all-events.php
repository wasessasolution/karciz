<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

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
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin-premium.css?v=2">
</head>
<body>

<div class="wrapper">

<?php include 'sidebar.php'; ?>

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