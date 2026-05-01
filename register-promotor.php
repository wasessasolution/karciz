<?php
session_start();
require_once 'config.php';

$message = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $whatsapp  = trim($_POST['no_whatsapp']);

    // cek email
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Email sudah digunakan!";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, no_whatsapp, role, created_at)
            VALUES (?, ?, ?, ?, 'organizer', NOW())
        ");
        $stmt->bind_param("ssss", $username, $email, $password, $whatsapp);

        if ($stmt->execute()) {
            $success = "Register berhasil, menunggu verifikasi admin";
            $_POST = []; // reset form
        } else {
            $message = "Terjadi kesalahan saat menyimpan data!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Promotor - KarciZ</title>

  <!-- CSS GLOBAL -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<?php include __DIR__ . '/components/navbar.php'; ?>

<!-- MAIN CONTENT -->
<main>

  <div class="register-container">

    <h2>Daftar Jadi Promotor</h2>
    <p>Isi data untuk menjadi promotor event di KarciZ</p>

    <!-- SUCCESS POPUP -->
    <?php if ($success): ?>
      <div class="popup-success">
        <?= htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <!-- ERROR -->
    <?php if ($message): ?>
      <p class="error-msg"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST">

      <input 
        type="text" 
        name="username" 
        placeholder="Username" 
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        required
      >

      <input 
        type="email" 
        name="email" 
        placeholder="Email"
        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        required
      >

      <input 
        type="text" 
        name="no_whatsapp" 
        placeholder="No WhatsApp"
        value="<?= htmlspecialchars($_POST['no_whatsapp'] ?? '') ?>"
        required
      >

      <input 
        type="password" 
        name="password" 
        placeholder="Password" 
        required
      >

      <button type="submit">Daftar Promotor</button>

    </form>

  </div>

</main>

<!-- FOOTER -->
<?php include __DIR__ . '/components/footer.php'; ?>

<!-- AUTO HIDE POPUP -->
<script>
setTimeout(() => {
  const popup = document.querySelector(".popup-success");
  if (popup) {
    popup.style.opacity = "0";
    popup.style.transform = "translateY(-10px)";
    setTimeout(() => popup.remove(), 500);
  }
}, 3000);
</script>

</body>
</html>