<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

// sementara ambil user organizer
$result = $conn->query("SELECT * FROM users WHERE role='organizer'");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Verifikasi Organizer</title>
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
    <h3>Verifikasi Organizer</h3>
  </div>

  <div class="table-container">
    <table>
      <tr>
        <th>No</th>
        <th>Username</th>
        <th>Email</th>
        <th>Aksi</th>
      </tr>

      <?php $no=1; while($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['username'] ?></td>
        <td><?= $row['email'] ?></td>
        <td>
          <button>Approve</button>
          <button>Reject</button>
        </td>
      </tr>
      <?php } ?>
    </table>
  </div>

</div>
</div>

</body>
</html>