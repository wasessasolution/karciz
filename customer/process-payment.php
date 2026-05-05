<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$username = $_SESSION['user'];

$event_id = intval($_POST['event_id'] ?? 0);
$ticket_id = intval($_POST['ticket_id'] ?? 0);
$qty = intval($_POST['qty'] ?? 0);
$total = intval($_POST['total'] ?? 0);
$payment_method = $_POST['payment_method'] ?? '';
$payment_detail = $_POST['payment_detail'] ?? '';

if ($event_id <= 0 || $ticket_id <= 0 || $qty <= 0 || $total <= 0) {
    die("Data pembayaran tidak valid.");
}

// Ambil user
$stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User tidak ditemukan.");
}

$user_id = $user['id'];

// Mulai transaksi database
$conn->begin_transaction();

try {

    // Cek tiket + stok
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

    $real_total = $ticket['harga'] * $qty;

    if ($real_total != $total) {
        throw new Exception("Total pembayaran tidak valid.");
    }

    // Kurangi stok
    $stmt = $conn->prepare("
        UPDATE tickets 
        SET stok = stok - ? 
        WHERE id=? AND event_id=?
    ");
    $stmt->bind_param("iii", $qty, $ticket_id, $event_id);
    $stmt->execute();

    // Simpan transaksi
    $stmt = $conn->prepare("
        INSERT INTO transactions 
        (user_id, event_id, ticket_id, qty, total, payment_method, payment_detail, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'paid')
    ");
    $stmt->bind_param(
        "iiiiiss",
        $user_id,
        $event_id,
        $ticket_id,
        $qty,
        $real_total,
        $payment_method,
        $payment_detail
    );
    $stmt->execute();

    $transaction_id = $conn->insert_id;

    $conn->commit();

    header("Location: history_transaksi.php?success=1&trx=" . $transaction_id);
    exit;

} catch (Exception $e) {

    $conn->rollback();

    echo "Pembayaran gagal: " . $e->getMessage();
    exit;
}
?>