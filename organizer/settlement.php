<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

/*
|--------------------------------------------------------------------------
| Ambil promotor
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT promotor.id, promotor.nama_brand
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Promotor tidak ditemukan.");
}

$promotor_id = $promotor['id'];

/*
|--------------------------------------------------------------------------
| Generate settlement otomatis per event selesai
|--------------------------------------------------------------------------
*/
$generate = $conn->prepare("
    SELECT
        events.id AS event_id,

        COALESCE(SUM(transactions.total),0) AS total_sales,
        COALESCE(SUM(transactions.platform_fee),0) AS platform_fee,
        COALESCE(SUM(transactions.payment_gateway_fee),0) AS qris_fee,
        COALESCE(SUM(transactions.net_promoter_income),0) AS net_amount

    FROM events

    LEFT JOIN transactions 
        ON transactions.event_id = events.id
       AND transactions.status = 'paid'

    WHERE events.organizer_id = ?
      AND events.status = 'selesai'

    GROUP BY events.id
");

$generate->bind_param("i", $promotor_id);
$generate->execute();

$generated = $generate->get_result();

while ($row = $generated->fetch_assoc()) {

    $check = $conn->prepare("
        SELECT id 
        FROM settlements
        WHERE promotor_id = ?
          AND event_id = ?
        LIMIT 1
    ");

    $check->bind_param(
        "ii",
        $promotor_id,
        $row['event_id']
    );

    $check->execute();

    $exist = $check->get_result()->fetch_assoc();

    if (!$exist) {

        $insert = $conn->prepare("
            INSERT INTO settlements
            (
                promotor_id,
                event_id,
                total_sales,
                platform_fee,
                qris_fee,
                net_amount,
                status,
                requested_at
            )
            VALUES
            (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $insert->bind_param(
            "iiiiii",
            $promotor_id,
            $row['event_id'],
            $row['total_sales'],
            $row['platform_fee'],
            $row['qris_fee'],
            $row['net_amount']
        );

        $insert->execute();
    }
}

/*
|--------------------------------------------------------------------------
| Ambil data settlement
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT
        settlements.*,
        events.nama_event,
        events.tanggal
    FROM settlements
    JOIN events ON settlements.event_id = events.id
    WHERE settlements.promotor_id = ?
    ORDER BY settlements.requested_at DESC
");

$stmt->bind_param("i", $promotor_id);
$stmt->execute();

$settlements = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Settlement Dana - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

<?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Settlement Dana</h3>
                <p>Pencairan dana hasil penjualan tiket event</p>
            </div>
        </div>

        <div class="dashboard-cards">

            <?php
            $summary = $conn->query("
                SELECT
                    COALESCE(SUM(net_amount),0) AS total_dana
                FROM settlements
                WHERE promotor_id = {$promotor_id}
            ")->fetch_assoc();
            ?>

            <div class="dashboard-card">
                <h4>Total Dana Settlement</h4>
                <p>Rp <?= number_format($summary['total_dana'], 0, ',', '.'); ?></p>
            </div>

        </div>

        <div class="table-container" style="margin-top:24px;">

            <h3>Daftar Settlement</h3>

            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Tanggal Event</th>
                        <th>Total Penjualan</th>
                        <th>Fee KarciZ</th>
                        <th>Fee QRIS</th>
                        <th>Dana Bersih</th>
                        <th>Status</th>
                        <th>Request</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($settlements->num_rows > 0): ?>

                    <?php while ($row = $settlements->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($row['nama_event']); ?>
                            </td>

                            <td>
                                <?= date('d M Y', strtotime($row['tanggal'])); ?>
                            </td>

                            <td>
                                Rp <?= number_format($row['total_sales'], 0, ',', '.'); ?>
                            </td>

                            <td>
                                Rp <?= number_format($row['platform_fee'], 0, ',', '.'); ?>
                            </td>

                            <td>
                                Rp <?= number_format($row['qris_fee'], 0, ',', '.'); ?>
                            </td>

                            <td>
                                <strong>
                                    Rp <?= number_format($row['net_amount'], 0, ',', '.'); ?>
                                </strong>
                            </td>

                            <td>

                                <?php if ($row['status'] === 'pending'): ?>

                                    <span class="status-badge active">
                                        Pending
                                    </span>

                                <?php elseif ($row['status'] === 'approved'): ?>

                                    <span class="status-badge done">
                                        Approved
                                    </span>

                                <?php elseif ($row['status'] === 'paid'): ?>

                                    <span class="status-badge done">
                                        Paid
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge reject">
                                        Rejected
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= date('d M Y H:i', strtotime($row['requested_at'])); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8">
                            Belum ada settlement tersedia.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>