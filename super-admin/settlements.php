<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("
            UPDATE settlements 
            SET status='approved', approved_at=NOW()
            WHERE id=? AND status='pending'
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if ($action === 'paid') {
        $stmt = $conn->prepare("
            UPDATE settlements 
            SET status='paid', paid_at=NOW()
            WHERE id=? AND status='approved'
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if ($action === 'reject') {
        $stmt = $conn->prepare("
            UPDATE settlements 
            SET status='rejected'
            WHERE id=? AND status='pending'
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: settlements.php");
    exit;
}

$summary = $conn->query("
    SELECT
        COALESCE(SUM(net_amount),0) AS total_settlement,
        COALESCE(SUM(CASE WHEN status='pending' THEN net_amount ELSE 0 END),0) AS pending_amount,
        COALESCE(SUM(CASE WHEN status='approved' THEN net_amount ELSE 0 END),0) AS approved_amount,
        COALESCE(SUM(CASE WHEN status='paid' THEN net_amount ELSE 0 END),0) AS paid_amount
    FROM settlements
")->fetch_assoc();

$settlements = $conn->query("
    SELECT
        settlements.*,
        promotor.nama_brand,
        events.nama_event,
        events.tanggal
    FROM settlements
    JOIN promotor ON settlements.promotor_id = promotor.id
    JOIN events ON settlements.event_id = events.id
    ORDER BY settlements.requested_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Settlement Promotor - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/superadmin.css?v=1">
    <link rel="stylesheet" href="/Karciz/assets/css/superadmin-premium.css?v=2">
</head>
<body>

<div class="wrapper">

<?php include 'sidebar.php'; ?>

    <div class="main">

        <div class="topbar">
            <div>
                <h3>Settlement Promotor</h3>
                <p>Approval dan pencairan dana bersih promotor</p>
            </div>
        </div>

        <div class="card-container">
            <div class="card">
                <h4>Total Settlement</h4>
                <p>Rp <?= number_format($summary['total_settlement'], 0, ',', '.'); ?></p>
            </div>

            <div class="card">
                <h4>Pending</h4>
                <p>Rp <?= number_format($summary['pending_amount'], 0, ',', '.'); ?></p>
            </div>

            <div class="card">
                <h4>Approved</h4>
                <p>Rp <?= number_format($summary['approved_amount'], 0, ',', '.'); ?></p>
            </div>

            <div class="card">
                <h4>Paid</h4>
                <p>Rp <?= number_format($summary['paid_amount'], 0, ',', '.'); ?></p>
            </div>
        </div>

        <div class="table-container">
            <h3>Daftar Request Settlement</h3>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Promotor</th>
                        <th>Event</th>
                        <th>Tanggal Event</th>
                        <th>Total Penjualan</th>
                        <th>Fee KarciZ</th>
                        <th>Fee QRIS</th>
                        <th>Dana Bersih</th>
                        <th>Status</th>
                        <th>Request</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($settlements->num_rows > 0): ?>
                        <?php $no = 1; while ($row = $settlements->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_brand']); ?></td>
                                <td><?= htmlspecialchars($row['nama_event']); ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td>Rp <?= number_format($row['total_sales'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['platform_fee'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['qris_fee'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?= number_format($row['net_amount'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="status-badge <?= htmlspecialchars($row['status']); ?>">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($row['requested_at'])); ?></td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="settlements.php?action=approve&id=<?= $row['id']; ?>" class="btn-approve">Approve</a>
                                        <a href="settlements.php?action=reject&id=<?= $row['id']; ?>" class="btn-delete">Reject</a>
                                    <?php elseif ($row['status'] === 'approved'): ?>
                                        <a href="settlements.php?action=paid&id=<?= $row['id']; ?>" class="btn-approve">Mark Paid</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">Belum ada settlement.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>