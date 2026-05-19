<?php
session_start();
include 'config.php';
require_once __DIR__ . '/lang/lang.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username/email dan password wajib diisi!";
    } else {

        $stmt = $conn->prepare("
            SELECT * FROM users 
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'superadmin') {
                    header("Location: super-admin/dashboard.php");
                } elseif ($user['role'] === 'organizer') {
                    header("Location: organizer/dashboard.php");
                } elseif ($user['role'] === 'staff_gate') {
                    header("Location: organizer/ticket-tracking.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
                $error = "Password salah!";
            }

        } else {
            $error = "Username/email tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KarciZ - Login</title>
  <link rel="stylesheet" href="assets/css/login.css?v=2">
</head>
<body>

<section class="login-page">

  <div class="pattern-layer">
    <?php for ($i = 0; $i < 34; $i++): ?>
      <span><?= ['🎟', '🎫', '★', '♫', '🎤', 'K', '✦'][$i % 7]; ?></span>
    <?php endfor; ?>
  </div>

  <div class="login-card">

    <img src="/Karciz/assets/images/logo/logo.png" alt="KarciZ" class="brand-icon">

    <h1>Masuk KarciZ</h1>
    <p class="login-subtitle">Login untuk membeli tiket dan mengakses akun Anda</p>

    <?php if ($error != "") { ?>
      <div class="error-box">
        <?= htmlspecialchars($error); ?>
      </div>
    <?php } ?>

    <button type="button" class="google-btn" onclick="alert('Google login belum diaktifkan');">
      <span>G</span>
      Lanjutkan dengan Google
    </button>

    <div class="divider">
      <span></span>
      <p>ATAU</p>
      <span></span>
    </div>

    <form method="POST" class="login-form">

      <div class="input-box">
        <span>👤</span>
        <input type="text" name="username" placeholder="Email atau Username" required>
      </div>

      <div class="input-box">
        <span>🔒</span>
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <button type="submit" class="login-btn">
        Login <span>→</span>
      </button>

    </form>

    <p class="register-text">
      Belum punya akun?
      <a href="register.php">Daftar saja</a>
    </p>

  </div>

  <div class="login-credit">
    © 2026 KarciZ • Powered by Wasessa Solution Tech
  </div>

</section>

</body>
</html>