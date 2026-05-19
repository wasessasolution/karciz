<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$conn->query("
    UPDATE events 
    SET status='selesai' 
    WHERE tanggal < CURDATE() 
      AND status='aktif'
");

$username = $_SESSION['user'];

$stmt = $conn->prepare("
    SELECT promotor.* 
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username=? AND promotor.status='approved'
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    echo "Akun promotor belum terverifikasi.";
    exit;
}

$organizer_id = $promotor['id'];

$stmt = $conn->prepare("
    SELECT 
        COUNT(transactions.id) AS total_transaksi,
        COALESCE(SUM(transactions.qty), 0) AS tiket_terjual,
        COALESCE(SUM(transactions.total), 0) AS total_penjualan,
        COALESCE(SUM(transactions.platform_fee), 0) AS total_platform_fee,
        COALESCE(SUM(transactions.payment_gateway_fee), 0) AS total_qris_fee,
        COALESCE(SUM(transactions.net_promoter_income), 0) AS pendapatan_bersih
    FROM transactions
    JOIN events ON transactions.event_id = events.id
    WHERE events.organizer_id = ?
      AND transactions.status = 'paid'
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT 
        events.nama_event,
        events.tanggal,
        events.status,
        tickets.nama_tiket,
        COUNT(transactions.id) AS jumlah_transaksi,
        COALESCE(SUM(transactions.qty), 0) AS tiket_terjual,
        COALESCE(SUM(transactions.total), 0) AS total_penjualan,
        COALESCE(SUM(transactions.platform_fee), 0) AS platform_fee,
        COALESCE(SUM(transactions.payment_gateway_fee), 0) AS qris_fee,
        COALESCE(SUM(transactions.net_promoter_income), 0) AS pendapatan_bersih
    FROM transactions
    JOIN events ON transactions.event_id = events.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    WHERE events.organizer_id = ?
      AND transactions.status = 'paid'
    GROUP BY events.id, tickets.id
    ORDER BY events.tanggal DESC
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$reports = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Laporan Penjualan</h3>
                <p>Transparansi penjualan dan pendapatan promotor</p>
            </div>
        </div>

        <div class="dashboard-cards">

            <div class="dashboard-card">
                <h4>Total Transaksi</h4>
                <p><?= $summary['total_transaksi']; ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Tiket Terjual</h4>
                <p><?= $summary['tiket_terjual']; ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Penjualan Kotor</h4>
                <p>Rp <?= number_format($summary['total_penjualan'], 0, ',', '.'); ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Pendapatan Bersih</h4>
                <p>Rp <?= number_format($summary['pendapatan_bersih'], 0, ',', '.'); ?></p>
            </div>

        </div>

        <div class="form-card" style="margin-top:24px;">
            <h3>Rincian Transparansi Dana</h3>

            <div class="finance-summary">
                <p><strong>Total penjualan customer:</strong> Rp <?= number_format($summary['total_penjualan'], 0, ',', '.'); ?></p>
                <p><strong>Fee platform KarciZ 5%:</strong> Rp <?= number_format($summary['total_platform_fee'], 0, ',', '.'); ?></p>
                <p><strong>Biaya QRIS 0.7%:</strong> Rp <?= number_format($summary['total_qris_fee'], 0, ',', '.'); ?></p>
                <p><strong>Dana bersih untuk promotor:</strong> Rp <?= number_format($summary['pendapatan_bersih'], 0, ',', '.'); ?></p>
            </div>

            <p style="margin-top:14px;color:#6b7280;">
            Catatan: Dana bersih dihitung dari total penjualan dikurangi fee platform KarciZ 
            sebesar 5% atau minimum Rp500 per transaksi, serta biaya QRIS 0.7%.
            </p>
        </div>

        <div class="table-container" style="margin-top:24px;">
            <h3>Detail Penjualan Per Event</h3>

            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Tanggal</th>
                        <th>Status Event</th>
                        <th>Jenis Tiket</th>
                        <th>Tiket Terjual</th>
                        <th>Penjualan</th>
                        <th>Fee KarciZ</th>
                        <th>Fee QRIS</th>
                        <th>Bersih Promotor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports->num_rows > 0) { ?>
                        <?php while ($row = $reports->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_event']); ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td>
                                    <span class="status-badge <?= $row['status'] == 'aktif' ? 'active' : 'done'; ?>">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['nama_tiket']); ?></td>
                                <td><?= $row['tiket_terjual']; ?></td>
                                <td>Rp <?= number_format($row['total_penjualan'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['platform_fee'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['qris_fee'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?= number_format($row['pendapatan_bersih'], 0, ',', '.'); ?></strong></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9">Belum ada transaksi penjualan.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>