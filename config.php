<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbtiket";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/* AUTO UPDATE STATUS EVENT */
$conn->query("
    UPDATE events
    SET status = 'selesai'
    WHERE 
      (
        (jam_selesai IS NOT NULL AND CONCAT(tanggal, ' ', jam_selesai) < NOW())
        OR
        (jam_selesai IS NULL AND tanggal < CURDATE())
      )
      AND status = 'aktif'
");
?>