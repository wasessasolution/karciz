<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

$summary = $conn->query("
    SELECT
        COUNT(transactions.id) AS total_transaksi,
        COALESCE(SUM(transactions.qty), 0) AS total_tiket,
        COALESCE(SUM(transactions.total), 0) AS total_penjualan,
        COALESCE(SUM(transactions.platform_fee), 0) AS fee_karciz,
        COALESCE(SUM(transactions.payment_gateway_fee), 0) AS fee_qris,
        COALESCE(SUM(transactions.net_promoter_income), 0) AS pendapatan_promotor
    FROM transactions
    WHERE transactions.status = 'paid'
")->fetch_assoc();

$totalEvent = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];

$reports = $conn->query("
    SELECT
        promotor.nama_brand,
        events.nama_event,
        COUNT(transactions.id) AS total_transaksi,
        COALESCE(SUM(transactions.qty), 0) AS tiket_terjual,
        COALESCE(SUM(transactions.total), 0) AS total_penjualan,
        COALESCE(SUM(transactions.platform_fee), 0) AS fee_karciz,
        COALESCE(SUM(transactions.payment_gateway_fee), 0) AS fee_qris,
        COALESCE(SUM(transactions.net_promoter_income), 0) AS pendapatan_promotor
    FROM transactions
    JOIN events ON transactions.event_id = events.id
    LEFT JOIN promotor ON events.organizer_id = promotor.id
    WHERE transactions.status = 'paid'
    GROUP BY events.id
    ORDER BY total_penjualan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan - KarciZ</title>
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin-premium.css?v=2">
</head>
<body>

<div class="wrapper">

<?php include 'sidebar.php'; ?>

  <div class="main">

    <div class="topbar">
      <div>
        <h3>Laporan Keuangan Platform</h3>
        <p>Ringkasan penjualan, fee KarciZ, QRIS, dan pendapatan promotor</p>
      </div>
    </div>

    <div class="card-container">

      <div class="card">
        <h4>Total Penjualan</h4>
        <p>Rp <?= number_format($summary['total_penjualan'], 0, ',', '.'); ?></p>
      </div>

      <div class="card">
        <h4>Total Tiket Terjual</h4>
        <p><?= $summary['total_tiket']; ?></p>
      </div>

      <div class="card">
        <h4>Total Event</h4>
        <p><?= $totalEvent; ?></p>
      </div>

      <div class="card">
        <h4>Total Transaksi</h4>
        <p><?= $summary['total_transaksi']; ?></p>
      </div>

    </div>

    <div class="card-container">

      <div class="card">
        <h4>Fee KarciZ</h4>
        <p>Rp <?= number_format($summary['fee_karciz'], 0, ',', '.'); ?></p>
      </div>

      <div class="card">
        <h4>Fee QRIS</h4>
        <p>Rp <?= number_format($summary['fee_qris'], 0, ',', '.'); ?></p>
      </div>

      <div class="card">
        <h4>Dana Bersih Promotor</h4>
        <p>Rp <?= number_format($summary['pendapatan_promotor'], 0, ',', '.'); ?></p>
      </div>

    </div>

    <div class="table-container">
      <h3>Laporan Per Event</h3>

      <table>
        <tr>
          <th>No</th>
          <th>Promotor</th>
          <th>Event</th>
          <th>Transaksi</th>
          <th>Tiket Terjual</th>
          <th>Total Penjualan</th>
          <th>Fee KarciZ</th>
          <th>Fee QRIS</th>
          <th>Bersih Promotor</th>
        </tr>

        <?php if ($reports->num_rows > 0) { ?>
          <?php $no = 1; while ($row = $reports->fetch_assoc()) { ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['nama_brand'] ?? 'Tidak diketahui'); ?></td>
              <td><?= htmlspecialchars($row['nama_event']); ?></td>
              <td><?= $row['total_transaksi']; ?></td>
              <td><?= $row['tiket_terjual']; ?></td>
              <td>Rp <?= number_format($row['total_penjualan'], 0, ',', '.'); ?></td>
              <td>Rp <?= number_format($row['fee_karciz'], 0, ',', '.'); ?></td>
              <td>Rp <?= number_format($row['fee_qris'], 0, ',', '.'); ?></td>
              <td><strong>Rp <?= number_format($row['pendapatan_promotor'], 0, ',', '.'); ?></strong></td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="9">Belum ada laporan transaksi.</td>
          </tr>
        <?php } ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>