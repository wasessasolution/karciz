<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

/* Auto update event selesai */
$conn->query("
    UPDATE events 
    SET status='selesai' 
    WHERE tanggal < CURDATE() 
      AND status='aktif'
");

$totalUser = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalPromotor = $conn->query("SELECT COUNT(*) AS total FROM promotor WHERE status='approved'")->fetch_assoc()['total'];
$pendingPromotor = $conn->query("SELECT COUNT(*) AS total FROM promotor WHERE status='pending'")->fetch_assoc()['total'];
$totalEvent = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];

$finance = $conn->query("
    SELECT
        COUNT(id) AS total_transaksi,
        COALESCE(SUM(total), 0) AS total_penjualan,
        COALESCE(SUM(platform_fee), 0) AS fee_karciz,
        COALESCE(SUM(payment_gateway_fee), 0) AS fee_qris,
        COALESCE(SUM(net_promoter_income), 0) AS pendapatan_promotor
    FROM transactions
    WHERE status='paid'
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Super Admin Dashboard - KarciZ</title>
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin-premium.css?v=2">
</head>
<body>

<div class="wrapper">

  <?php include 'sidebar.php'; ?>

  <div class="main">

    <div class="topbar">
      <div>
        <h3>Dashboard Super Admin</h3>
        <p>Monitoring transaksi, event, promotor, dan fee platform</p>
      </div>

      <div class="user-info">
        <span>👤 <?= htmlspecialchars($username); ?></span>
        <a href="/Karciz/logout.php" class="logout-btn">Logout</a>
      </div>
    </div>

    <div class="card-container">

      <div class="card">
        <h4>Total User</h4>
        <p><?= $totalUser; ?></p>
      </div>

      <div class="card">
        <h4>Promotor Aktif</h4>
        <p><?= $totalPromotor; ?></p>
      </div>

      <div class="card">
        <h4>Total Event</h4>
        <p><?= $totalEvent; ?></p>
      </div>

      <div class="card">
        <h4>Promotor Pending</h4>
        <p><?= $pendingPromotor; ?></p>
      </div>

    </div>

    <div class="card-container">

      <div class="card">
        <h4>Total Transaksi Paid</h4>
        <p><?= $finance['total_transaksi']; ?></p>
      </div>

      <div class="card">
        <h4>Total Penjualan</h4>
        <p>Rp <?= number_format($finance['total_penjualan'], 0, ',', '.'); ?></p>
      </div>

      <div class="card">
        <h4>Fee KarciZ</h4>
        <p>Rp <?= number_format($finance['fee_karciz'], 0, ',', '.'); ?></p>
      </div>

      <div class="card">
        <h4>Fee QRIS</h4>
        <p>Rp <?= number_format($finance['fee_qris'], 0, ',', '.'); ?></p>
      </div>

    </div>

    <div class="table-container">
      <h3>Ringkasan Keuangan Platform</h3>

      <table>
        <tr>
          <th>Keterangan</th>
          <th>Nominal</th>
        </tr>
        <tr>
          <td>Total uang dibayarkan customer</td>
          <td>Rp <?= number_format($finance['total_penjualan'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
          <td>Pendapatan KarciZ / Fee Platform</td>
          <td>Rp <?= number_format($finance['fee_karciz'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
          <td>Biaya QRIS / Payment Gateway</td>
          <td>Rp <?= number_format($finance['fee_qris'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
          <td>Dana bersih untuk promotor</td>
          <td><strong>Rp <?= number_format($finance['pendapatan_promotor'], 0, ',', '.'); ?></strong></td>
        </tr>
      </table>
    </div>

  </div>

</div>

</body>
</html>