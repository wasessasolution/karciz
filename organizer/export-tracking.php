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
    die("Akun tidak terhubung ke promotor.");
}

$promotor_id = $promotor['id'];
$event_id = intval($_GET['event_id'] ?? 0);

$sql = "
    SELECT 
        users.username,
        users.email,
        events.nama_event,
        events.tanggal,
        tickets.nama_tiket,
        transactions.qty,
        transactions.ticket_code,
        transactions.used_status,
        transactions.checkin_method,
        transactions.used_at
    FROM transactions
    JOIN users ON transactions.user_id = users.id
    JOIN events ON transactions.event_id = events.id
    JOIN tickets ON transactions.ticket_id = tickets.id
    WHERE events.organizer_id = ?
      AND transactions.status = 'paid'
";

if ($event_id > 0) {
    $sql .= " AND events.id = ? ";
}

$sql .= " ORDER BY events.tanggal DESC, users.username ASC";

$stmt = $conn->prepare($sql);

if ($event_id > 0) {
    $stmt->bind_param("ii", $promotor_id, $event_id);
} else {
    $stmt->bind_param("i", $promotor_id);
}

$stmt->execute();
$result = $stmt->get_result();

$filename = "tracking_tiket_karciz_" . date('Ymd_His') . ".csv";

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen("php://output", "w");

fputcsv($output, [
    "Customer",
    "Email",
    "Event",
    "Tanggal Event",
    "Jenis Tiket",
    "Qty",
    "Kode Tiket",
    "Status Check-in",
    "Metode Check-in",
    "Waktu Check-in"
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['username'],
        $row['email'],
        $row['nama_event'],
        date('d M Y', strtotime($row['tanggal'])),
        $row['nama_tiket'],
        $row['qty'],
        $row['ticket_code'],
        $row['used_status'] === 'used' ? 'Sudah Check-in' : 'Belum Check-in',
        $row['checkin_method'] ?: '-',
        $row['used_at'] ?: '-'
    ]);
}

fclose($output);
exit;
?>