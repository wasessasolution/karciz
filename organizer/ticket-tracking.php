<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['organizer', 'staff_gate'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];
$role = $_SESSION['role'];

if ($role === 'organizer') {
    $stmt = $conn->prepare("
        SELECT promotor.id 
        FROM promotor
        JOIN users ON promotor.user_id = users.id
        WHERE users.username = ?
        LIMIT 1
    ");
} else {
    $stmt = $conn->prepare("
        SELECT promotor_staff.promotor_id AS id
        FROM promotor_staff
        JOIN users ON promotor_staff.user_id = users.id
        WHERE users.username = ?
        LIMIT 1
    ");
}

$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Akun ini belum terhubung ke promotor.");
}

$promotor_id = $promotor['id'];

$selected_event_id = intval($_GET['event_id'] ?? 0);
$search = trim($_GET['search'] ?? '');
$searchParam = "%{$search}%";

$eventStmt = $conn->prepare("
    SELECT id, nama_event, tanggal, status
    FROM events
    WHERE organizer_id = ?
    ORDER BY tanggal DESC
");
$eventStmt->bind_param("i", $promotor_id);
$eventStmt->execute();
$eventList = $eventStmt->get_result();

$sql = "
    SELECT 
        transactions.id,
        transactions.ticket_code,
        transactions.qty,
        transactions.status,
        transactions.used_status,
        transactions.used_at,
        transactions.checkin_method,
        users.username,
        users.email,
        events.nama_event,
        events.tanggal,
        tickets.nama_tiket
    FROM transactions
    JOIN users ON transactions.user_id = users.id
    JOIN events ON transactions.event_id = events.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    WHERE events.organizer_id = ?
      AND transactions.status = 'paid'
";

$params = [$promotor_id];
$types = "i";

if ($selected_event_id > 0) {
    $sql .= " AND events.id = ? ";
    $params[] = $selected_event_id;
    $types .= "i";
}

if ($search !== '') {
    $sql .= " AND (
        users.username LIKE ?
        OR users.email LIKE ?
        OR transactions.ticket_code LIKE ?
    ) ";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

$sql .= " ORDER BY transactions.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tickets = $stmt->get_result();

$total = 0;
$used = 0;
$unused = 0;
$rows = [];

while ($row = $tickets->fetch_assoc()) {
    $total++;
    if ($row['used_status'] === 'used') {
        $used++;
    } else {
        $unused++;
    }
    $rows[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Tiket - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Tracking Tiket</h3>
                <p>Monitoring tiket, check-in, dan verifikasi manual</p>
            </div>
        </div>

        <div class="form-card" style="margin-bottom:24px;">
            <h3>Filter & Pencarian Tiket</h3>

            <form method="GET" style="margin-top:14px; display:grid; grid-template-columns:1fr 1fr auto; gap:12px;">
                <select name="event_id" style="padding:14px; border-radius:12px; border:1px solid #d1d5db;">
                    <option value="0">Semua Event</option>

                    <?php while ($event = $eventList->fetch_assoc()) { ?>
                        <option value="<?= $event['id']; ?>" <?= $selected_event_id == $event['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($event['nama_event']); ?> - 
                            <?= date('d M Y', strtotime($event['tanggal'])); ?>
                            (<?= htmlspecialchars($event['status']); ?>)
                        </option>
                    <?php } ?>
                </select>

                <input 
                    type="text"
                    name="search"
                    placeholder="Cari username / email / kode tiket"
                    value="<?= htmlspecialchars($search); ?>"
                    style="padding:14px; border-radius:12px; border:1px solid #d1d5db;"
                >

                <button type="submit" class="btn-add">
                    Cari
                </button>
            </form>
            <a 
                href="export-tracking.php?event_id=<?= $selected_event_id; ?>" 
                class="btn-view"
                style="display:inline-block; margin-top:14px;"
                >
                Export CSV Tracking
            </a>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h4>Total Tiket Terjual</h4>
                <p><?= $total; ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Sudah Check-in</h4>
                <p><?= $used; ?></p>
            </div>

            <div class="dashboard-card">
                <h4>Belum Check-in</h4>
                <p><?= $unused; ?></p>
            </div>
        </div>

        <div class="table-container" style="margin-top:24px;">
            <h3>Daftar Tiket Customer</h3>

            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Tanggal</th>
                        <th>Jenis Tiket</th>
                        <th>Qty</th>
                        <th>Kode Tiket</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Waktu Check-in</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['nama_event']); ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td><?= htmlspecialchars($row['nama_tiket']); ?></td>
                                <td><?= $row['qty']; ?></td>
                                <td><?= htmlspecialchars($row['ticket_code']); ?></td>
                                <td>
                                    <?php if ($row['used_status'] === 'used'): ?>
                                        <span class="status-badge done">Sudah Check-in</span>
                                    <?php else: ?>
                                        <span class="status-badge active">Belum Check-in</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $row['checkin_method'] ? htmlspecialchars($row['checkin_method']) : '-'; ?>
                                </td>
                                <td>
                                    <?= $row['used_at'] ? htmlspecialchars($row['used_at']) : '-'; ?>
                                </td>
                                <td>
                                    <?php if ($row['used_status'] !== 'used'): ?>
                                        <a 
                                            href="manual-checkin.php?id=<?= $row['id']; ?>" 
                                            class="btn-view"
                                        >
                                            Verifikasi Manual
                                        </a>
                                    <?php else: ?>
                                        <span style="color:green;font-weight:700;">Terverifikasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">Data tiket tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

    </div>

</div>
<script>
let isTyping = false;

document.querySelector('input[name="search"]')?.addEventListener('focus', () => {
  isTyping = true;
});

document.querySelector('input[name="search"]')?.addEventListener('blur', () => {
  isTyping = false;
});

setInterval(() => {
  if (!isTyping) {
    window.location.reload();
  }
}, 5000);
</script>
</body>
</html>