<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../logout.php");
    exit;
}

$success = "";
$error = "";

if (isset($_POST['update'])) {

    $email = trim($_POST['email']);
    $no_wa = trim($_POST['no_whatsapp']);
    $foto = $user['profile_image'] ?: 'default-profile.png';

    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($_FILES['foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Format foto harus JPG, JPEG, PNG, atau WEBP.";
        } else {
            $file_name = 'profile_' . time() . '_' . uniqid() . '.' . $ext;
            $tmp = $_FILES['foto']['tmp_name'];
            $path = "../assets/images/profile/" . $file_name;

            if (move_uploaded_file($tmp, $path)) {
                $foto = $file_name;
            } else {
                $error = "Upload foto gagal.";
            }
        }
    }

    $hashed_password = "";

    if ($old_password !== '' || $new_password !== '' || $confirm_password !== '') {

        if ($old_password === '' || $new_password === '' || $confirm_password === '') {
            $error = "Semua kolom password wajib diisi jika ingin mengganti password.";
        } elseif (!password_verify($old_password, $user['password'])) {
            $error = "Password lama tidak sesuai.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi password baru tidak sama.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password baru minimal 6 karakter.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    if (!$error) {

        if ($hashed_password !== "") {
            $stmt = $conn->prepare("
                UPDATE users 
                SET email=?, no_whatsapp=?, profile_image=?, password=?
                WHERE username=?
            ");
            $stmt->bind_param("sssss", $email, $no_wa, $foto, $hashed_password, $username);
        } else {
            $stmt = $conn->prepare("
                UPDATE users 
                SET email=?, no_whatsapp=?, profile_image=? 
                WHERE username=?
            ");
            $stmt->bind_param("ssss", $email, $no_wa, $foto, $username);
        }

        if ($stmt->execute()) {
            header("Location: profile.php?success=1");
            exit;
        } else {
            $error = "Gagal update profil.";
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Profil berhasil diperbarui.";
}

$profile_img = !empty($user['profile_image']) ? $user['profile_image'] : 'default-profile.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - KarciZ</title>

  <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=5">
  <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/profile.css?v=3">
</head>
<body>

<?php include '../components/navbar.php'; ?>

<main class="profile-page">

  <section class="profile-hero">
    <div class="profile-pattern">
      <span>🎟</span>
      <span>★</span>
      <span>K</span>
      <span>🎫</span>
      <span>♫</span>
    </div>

    <div class="container">
      <div class="profile-layout">

        <div class="profile-intro">
          <div class="profile-badge">My Account</div>
          <h1>Profil Saya</h1>
          <p>
            Kelola informasi akun, foto profil, email, nomor WhatsApp,
            dan password agar data tiket KarciZ tetap aman dan terbaru.
          </p>
        </div>

        <div class="profile-card">

          <?php if ($success): ?>
            <div class="profile-alert success"><?= htmlspecialchars($success); ?></div>
          <?php endif; ?>

          <?php if ($error): ?>
            <div class="profile-alert error"><?= htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">

            <div class="profile-avatar-box">
              <img 
                src="/Karciz/assets/images/profile/<?= htmlspecialchars($profile_img); ?>" 
                alt="Profile"
                class="profile-avatar"
              >

              <div>
                <h3><?= htmlspecialchars($user['username']); ?></h3>
                <p>Customer KarciZ</p>

                <label class="upload-btn">
                  Ganti Foto
                  <input type="file" name="foto" accept="image/*">
                </label>
              </div>
            </div>

            <div class="profile-form-grid">

              <div class="profile-form-group">
                <label>Username</label>
                <input 
                  type="text" 
                  value="<?= htmlspecialchars($user['username']); ?>" 
                  disabled
                >
              </div>

              <div class="profile-form-group">
                <label>Email</label>
                <input 
                  type="email" 
                  name="email" 
                  value="<?= htmlspecialchars($user['email']); ?>" 
                  required
                >
              </div>

              <div class="profile-form-group full">
                <label>No WhatsApp</label>
                <input 
                  type="text" 
                  name="no_whatsapp" 
                  value="<?= htmlspecialchars($user['no_whatsapp'] ?? ''); ?>"
                  placeholder="Contoh: 08123456789"
                >
              </div>

            </div>

            <div class="password-toggle-box">
              <button type="button" class="password-toggle-btn" id="togglePasswordPanel">
                Ganti Password
                <span>+</span>
              </button>
            </div>

            <div class="password-panel" id="passwordPanel">
              <h3>Ganti Password</h3>
              <p>Kosongkan bagian ini jika tidak ingin mengganti password.</p>

              <div class="profile-form-group">
                <label>Password Lama</label>
                <input 
                  type="password" 
                  name="old_password" 
                  placeholder="Masukkan password lama"
                >
              </div>

              <div class="profile-form-group">
                <label>Password Baru</label>
                <input 
                  type="password" 
                  name="new_password" 
                  placeholder="Minimal 6 karakter"
                >
              </div>

              <div class="profile-form-group">
                <label>Konfirmasi Password Baru</label>
                <input 
                  type="password" 
                  name="confirm_password" 
                  placeholder="Ulangi password baru"
                >
              </div>
            </div>

            <button type="submit" name="update" class="profile-save-btn">
              Simpan Perubahan
            </button>

            <a href="/Karciz/index.php" class="profile-back-btn">
              Kembali ke Beranda
            </a>

          </form>

        </div>

      </div>
    </div>
  </section>

</main>

<?php include '../components/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.getElementById("togglePasswordPanel");
  const panel = document.getElementById("passwordPanel");

  if (!toggleBtn || !panel) return;

  toggleBtn.addEventListener("click", function () {
    panel.classList.toggle("show");

    const icon = toggleBtn.querySelector("span");

    if (panel.classList.contains("show")) {
      icon.textContent = "−";
    } else {
      icon.textContent = "+";

      panel.querySelectorAll("input").forEach(input => {
        input.value = "";
      });
    }
  });
});
</script>

</body>
</html>