<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $no_whatsapp = trim($_POST['no_whatsapp']);

    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak sama.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "customer";
            $profile_image = "default-profile.png";

            $stmt = $conn->prepare("
                INSERT INTO users 
                (username, email, password, no_whatsapp, role, profile_image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $no_whatsapp, $role, $profile_image);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Gagal membuat akun.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KarciZ - Register</title>

  <link rel="stylesheet" href="/Karciz/assets/css/register.css?v=1">
</head>
<body>

<section class="register-page">

  <div class="register-pattern">
    <span>🎟</span>
    <span>★</span>
    <span>K</span>
    <span>🎫</span>
    <span>♫</span>
    <span>🎤</span>
  </div>

  <div class="register-card">

    <div class="register-logo">
      <img src="/Karciz/assets/images/logo/logo.png" alt="KarciZ">
    </div>

    <h1>Daftar Akun</h1>
    <p class="register-subtitle">
      Buat akun customer untuk membeli tiket dan mengakses e-ticket KarciZ.
    </p>

    <?php if ($error): ?>
      <div class="register-alert error">
        <?= htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="register-alert success">
        <?= htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="register-form">

      <div class="register-form-group">
        <span>👤</span>
        <input 
          type="text" 
          name="username" 
          placeholder="Username"
          required
        >
      </div>

      <div class="register-form-group">
        <span>✉️</span>
        <input 
          type="email" 
          name="email" 
          placeholder="Email"
          required
        >
      </div>

      <div class="register-form-group">
        <span>🔒</span>
        <input 
          type="password" 
          name="password" 
          placeholder="Password"
          required
        >
      </div>

      <div class="register-form-group">
        <span>🔐</span>
        <input 
          type="password" 
          name="confirm_password" 
          placeholder="Konfirmasi Password"
          required
        >
      </div>

      <div class="register-form-group">
        <span>📱</span>
        <input 
          type="text" 
          name="no_whatsapp" 
          placeholder="No WhatsApp"
          required
        >
      </div>

      <button type="submit" class="register-btn">
        Daftar
        <b>→</b>
      </button>

    </form>

    <p class="register-login-link">
      Sudah punya akun?
      <a href="/Karciz/login.php">Login</a>
    </p>

  </div>

  <div class="register-footer-mini">
    © 2026 KarciZ • Powered by Wasessa Solution Tech
  </div>

</section>

</body>
</html>