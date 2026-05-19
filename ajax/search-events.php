<?php
include '../config.php';

$keyword = trim($_GET['q'] ?? '');

if ($keyword === '') {
    echo json_encode([]);
    exit;
}

$search = "%{$keyword}%";

$stmt = $conn->prepare("
    SELECT 
        id,
        nama_event,
        lokasi,
        tanggal,
        jam_mulai,
        banner
    FROM events
    WHERE status = 'aktif'
      AND (
        nama_event LIKE ?
        OR lokasi LIKE ?
        OR deskripsi LIKE ?
        OR tanggal LIKE ?
        OR jam_mulai LIKE ?
      )
    ORDER BY tanggal ASC
    LIMIT 6
");

$stmt->bind_param("sssss", $search, $search, $search, $search, $search);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);