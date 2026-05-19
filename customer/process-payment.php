<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$username = $_SESSION['user'];

$event_id  = intval($_POST['event_id'] ?? 0);
$ticket_id = intval($_POST['ticket_id'] ?? 0);
$qty       = intval($_POST['qty'] ?? 0);

$payment_method = 'qris';
$payment_detail = 'DANA - 083182004753';

if ($event_id <= 0 || $ticket_id <= 0 || $qty <= 0) {
    die("Data pembayaran tidak valid.");
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User tidak ditemukan.");
}

$user_id = $user['id'];

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        SELECT id, stok, harga
        FROM tickets
        WHERE id=? AND event_id=?
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->bind_param("ii", $ticket_id, $event_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) {
        throw new Exception("Tiket tidak ditemukan.");
    }

    if ($ticket['stok'] < $qty) {
        throw new Exception("Stok tiket tidak mencukupi.");
    }

    $gross_total = $ticket['harga'] * $qty;

   $platform_fee_percent = round($gross_total * 0.05);
    $minimum_platform_fee = 500;

    $platform_fee = max($platform_fee_percent, $minimum_platform_fee);

    $payment_gateway_fee = round($gross_total * 0.007);

    $promoter_income = $gross_total - $platform_fee;
    $net_promoter_income = $gross_total - $platform_fee - $payment_gateway_fee;

    $ticket_code = 'KZ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

    $stmt = $conn->prepare("
        UPDATE tickets
        SET stok = stok - ?
        WHERE id=? AND event_id=?
    ");
    $stmt->bind_param("iii", $qty, $ticket_id, $event_id);
    $stmt->execute();

    $stmt = $conn->prepare("
        INSERT INTO transactions
        (
            user_id,
            event_id,
            ticket_id,
            qty,
            total,
            gross_total,
            platform_fee,
            payment_gateway_fee,
            promoter_income,
            net_promoter_income,
            payment_method,
            payment_detail,
            ticket_code,
            status,
            created_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', NOW())
    ");

    $stmt->bind_param(
        "iiiiiiiiiisss",
        $user_id,
        $event_id,
        $ticket_id,
        $qty,
        $gross_total,
        $gross_total,
        $platform_fee,
        $payment_gateway_fee,
        $promoter_income,
        $net_promoter_income,
        $payment_method,
        $payment_detail,
        $ticket_code
    );

    $stmt->execute();

    $transaction_id = $conn->insert_id;

    $conn->commit();

    header("Location: ticket-detail.php?id=" . $transaction_id . "&success=1");
    exit;

} catch (Exception $e) {

    $conn->rollback();

    echo "Pembayaran gagal: " . $e->getMessage();
    exit;
}
?>