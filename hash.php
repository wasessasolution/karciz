<?php
include 'config.php';

$hash = password_hash("1", PASSWORD_DEFAULT);

mysqli_query($conn, "UPDATE users SET password='$hash' WHERE username='superadmin1'");

echo "Password updated!";
?>