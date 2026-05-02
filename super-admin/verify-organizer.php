<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

$result = $conn->query("
    SELECT 
        promotor.id,
        promotor.user_id,
        promotor.nama_brand,
        promotor.deskripsi_singkat,
        promotor.email_bisnis,
        promotor.no_wa,
        promotor.status,
        users.username
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    ORDER BY promotor.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Promotor</title>
    <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body>

<div class="wrapper">

    <div class="sidebar">
        <h2>KarciZ Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-users.php">Kelola User</a>
        <a href="verify-organizer.php" class="active">Verifikasi Organizer</a>
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
                    <th>User</th>
                    <th>Brand</th>
                    <th>Email Bisnis</th>
                    <th>No WA</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php $no = 1; while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['nama_brand']); ?></td>
                        <td><?= htmlspecialchars($row['email_bisnis']); ?></td>
                        <td><?= htmlspecialchars($row['no_wa']); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending') { ?>
                                <a href="approve.php?id=<?= $row['id']; ?>" class="btn-approve">Approve</a>
                                <a href="reject.php?id=<?= $row['id']; ?>" class="btn-reject">Reject</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</div>

</body>
</html>