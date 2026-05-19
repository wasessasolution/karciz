<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT
        transactions.id,
        transactions.qty,
        transactions.total,
        transactions.platform_fee,
        transactions.payment_gateway_fee,
        transactions.net_promoter_income,
        transactions.payment_method,
        transactions.payment_detail,
        transactions.status,
        transactions.created_at,

        users.username,
        events.nama_event,
        promotor.nama_brand,
        tickets.nama_tiket
    FROM transactions
    JOIN users ON transactions.user_id = users.id
    JOIN events ON transactions.event_id = events.id
    LEFT JOIN promotor ON events.organizer_id = promotor.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    ORDER BY transactions.created_at DESC
");
$stmt->execute();
$transactions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transaksi - KarciZ</title>
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin-premium.css?v=2">
</head>
<body>

<div class="wrapper">

<?php include 'sidebar.php'; ?>

  <div class="main">

    <div class="topbar">
      <div>
        <h3>Transaksi</h3>
        <p>Monitoring seluruh transaksi customer dan pembagian dana</p>
      </div>
    </div>

    <div class="table-container">
      <table>
        <tr>
          <th>No</th>
          <th>Customer</th>
          <th>Promotor</th>
          <th>Event</th>
          <th>Tiket</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Fee KarciZ</th>
          <th>Fee QRIS</th>
          <th>Bersih Promotor</th>
          <th>Status</th>
          <th>Tanggal</th>
        </tr>

        <?php if ($transactions->num_rows > 0) { ?>
          <?php $no = 1; while ($row = $transactions->fetch_assoc()) { ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['username']); ?></td>
              <td><?= htmlspecialchars($row['nama_brand'] ?? 'Tidak diketahui'); ?></td>
              <td><?= htmlspecialchars($row['nama_event']); ?></td>
              <td><?= htmlspecialchars($row['nama_tiket']); ?></td>
              <td><?= $row['qty']; ?></td>
              <td>Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
              <td>Rp <?= number_format($row['platform_fee'], 0, ',', '.'); ?></td>
              <td>Rp <?= number_format($row['payment_gateway_fee'], 0, ',', '.'); ?></td>
              <td><strong>Rp <?= number_format($row['net_promoter_income'], 0, ',', '.'); ?></strong></td>
              <td>
                <span class="status-badge <?= $row['status'] == 'paid' ? 'active' : 'done'; ?>">
                  <?= htmlspecialchars($row['status']); ?>
                </span>
              </td>
              <td><?= date('d M Y H:i', strtotime($row['created_at'])); ?></td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="12">Belum ada transaksi.</td>
          </tr>
        <?php } ?>

      </table>
    </div>

  </div>
</div>

</body>
</html>