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

$stmt = $conn->prepare("UPDATE promotor SET status='rejected' WHERE id=?");
$stmt->bind_param("i", $promotor_id);
$stmt->execute();

header("Location: verify-organizer.php");
exit;