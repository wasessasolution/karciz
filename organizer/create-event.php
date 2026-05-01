<?php
session_start();
include '../config.php';

if ($_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['submit'])) {

    $nama = $_POST['nama_event'];
    $lokasi = $_POST['lokasi'];
    $tanggal = $_POST['tanggal'];
    $deskripsi = $_POST['deskripsi'];

    $organizer_id = $_SESSION['user'];

    $stmt = $conn->prepare("INSERT INTO events (organizer_id, nama_event, lokasi, tanggal, deskripsi) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $organizer_id, $nama, $lokasi, $tanggal, $deskripsi);
    $stmt->execute();

    echo "Event berhasil dibuat!";
}
?>