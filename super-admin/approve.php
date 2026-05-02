<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: verify-organizer.php");
    exit;
}

$promotor_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT user_id FROM promotor WHERE id=? LIMIT 1");
$stmt->bind_param("i", $promotor_id);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if ($promotor) {
    $user_id = $promotor['user_id'];

    $updatePromotor = $conn->prepare("UPDATE promotor SET status='approved' WHERE id=?");
    $updatePromotor->bind_param("i", $promotor_id);
    $updatePromotor->execute();

    $updateUser = $conn->prepare("UPDATE users SET role='organizer', status='approved' WHERE id=?");
    $updateUser->bind_param("i", $user_id);
    $updateUser->execute();
}

header("Location: verify-organizer.php");
exit;