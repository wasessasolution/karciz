<?php
include 'config.php';

$password = password_hash('123', PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    UPDATE users 
    SET password = ?, role = 'superadmin'
    WHERE username = 'superadmin1'
");
$stmt->bind_param("s", $password);

if ($stmt->execute()) {
    echo "Password superadmin1 berhasil direset ke: 123";
} else {
    echo "Gagal reset password";
}
?>