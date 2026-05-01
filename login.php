<?php
session_start();
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi sederhana
    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {

        // Ambil user (AMAN)
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // 🔥 DEBUG (sementara, bisa dihapus nanti)
            // echo $user['password']; exit;

            // Verifikasi password hash
            if (password_verify($password, $user['password'])) {

                // Regenerate session (security)
                session_regenerate_id(true);

                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect sesuai role
                if ($user['role'] === 'superadmin') {
                    header("Location: super-admin/dashboard.php");
                } elseif ($user['role'] === 'organizer') {
                    header("Location: organizer/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
                $error = "Password salah!";
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