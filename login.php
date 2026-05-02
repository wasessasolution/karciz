<?php
session_start();
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (!password_verify($password, $user['password'])) {
                $error = "Password salah!";
            } else {

                // BLOK ORGANIZER YANG BELUM APPROVED
                if (
                    $user['role'] === 'organizer' &&
                    isset($user['status']) &&
                    $user['status'] !== 'approved'
                ) {
                    $error = "Akun promotor Anda belum diverifikasi admin!";
                } else {

                    session_regenerate_id(true);

                    $_SESSION['user'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_id'] = $user['id'];

                    if ($user['role'] === 'superadmin') {
                        header("Location: super-admin/dashboard.php");
                    } elseif ($user['role'] === 'organizer') {
                        header("Location: organizer/dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                }
            }

        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karciz - Login</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

<section class="login-page">
  <div class="login-container modern-login">
    <h1>Masuk</h1>
    <p class="subtitle">
      Login menggunakan username dan password Anda
    </p>

    <?php if ($error != "") { ?>
      <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>

    <!-- FORM LOGIN -->
    <form method="POST" class="login-form">

      <div class="form-group">
        <label>Username</label>
        <input
          type="text"
          name="username"
          placeholder="Masukkan username"
          required
        />
      </div>

      <div class="form-group">
        <label>Password</label>
        <input
          type="password"
          name="password"
          placeholder="Masukkan password"
          required
        />
      </div>

      <button type="submit" class="btn-login">
        Login
      </button>

    </form>

    <!-- REGISTER -->
    <p style="margin-top:15px; text-align:center;">
      Belum punya akun?
      <a href="register.php">Daftar di sini</a>
    </p>

  </div>
</section>

<!-- Footer -->
<div id="footer"></div>

<script>
  fetch('components/footer.php')
    .then(response => response.text())
    .then(data => {
      document.getElementById('footer').innerHTML = data;
    });
</script>

</body>
</html>