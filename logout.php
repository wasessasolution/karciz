<?php
session_start();

// hapus semua session
$_SESSION = [];

// destroy session
session_destroy();

// redirect ke login
header("Location: login.php");
exit;
?>