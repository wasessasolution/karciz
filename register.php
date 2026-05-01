<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $whatsapp = trim($_POST['whatsapp']);

    if ($password !== $confirm) {
        $error = "Password tidak sama!";
    } else {

        // CEK DUPLIKAT (AMAN)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username atau email sudah digunakan!";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';

            // INSERT (AMAN)
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, no_whatsapp, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hash, $whatsapp, $role);

            if ($stmt->execute()) {
                $success = "Akun berhasil dibuat! Silakan login.";
            } else {
                $error = "Gagal mendaftar!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Karciz - Register</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<section class="login-page">
  <div class="login-container modern-login">
    <h1>Daftar Akun</h1>

    <?php if ($error != "") { ?>
      <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>

    <?php if ($success != "") { ?>
      <p style="color:green;"><?php echo $success; ?></p>
    <?php } ?>

    <form method="POST">

      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>
      </div>

      <div class="form-group">
        <label>No WhatsApp</label>
        <input type="text" name="whatsapp" required>
      </div>

      <button type="submit" class="btn-login">Daftar</button>

    </form>

    <p style="margin-top:15px;">
      Sudah punya akun?
      <a href="login.php">Login</a>
    </p>

  </div>
</section>

</body>
</html>