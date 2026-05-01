<?php
session_start();
include '../config.php';

// 🔐 PROTEKSI
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];

// 🔥 AMBIL DATA USER
$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success = "";
$error = "";

// 🔥 HANDLE UPDATE
if (isset($_POST['update'])) {

    $email = $_POST['email'];
    $no_wa = $_POST['no_whatsapp'];

    // upload foto
    $foto = $user['profile_image'];

    if (!empty($_FILES['foto']['name'])) {
        $file_name = time() . '_' . $_FILES['foto']['name'];
        $tmp = $_FILES['foto']['tmp_name'];
        $path = "../assets/images/profile/" . $file_name;

        move_uploaded_file($tmp, $path);
        $foto = $file_name;
    }

    // update ke DB
    $stmt = $conn->prepare("UPDATE users SET email=?, no_whatsapp=?, profile_image=? WHERE username=?");
    $stmt->bind_param("ssss", $email, $no_wa, $foto, $username);

    if ($stmt->execute()) {
        $success = "Profil berhasil diperbarui!";
        $_SESSION['user'] = $username;
        header("Refresh:1");
    } else {
        $error = "Gagal update!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profile - KarciZ</title>
  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    .profile-container {
      max-width: 500px;
      margin: 50px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
    }

    .profile-img {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-img img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
    }

    .form-group {
      margin-bottom: 15px;
    }

    input {
      width: 100%;
      padding: 10px;
    }

    .btn-save {
      background: #2f3640;
      color: #fff;
      border: none;
      padding: 10px;
      width: 100%;
      cursor: pointer;
    }
  </style>

</head>
<body>

<div class="profile-container">

  <h2>Profil Saya</h2>

  <?php if ($success) echo "<p style='color:green'>$success</p>"; ?>
  <?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

  <form method="POST" enctype="multipart/form-data">

    <div class="profile-img">
      <img src="../assets/images/profile/<?php echo $user['profile_image'] ?? 'default-profile.png'; ?>">
    </div>

    <div class="form-group">
      <label>Ganti Foto</label>
      <input type="file" name="foto">
    </div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" value="<?php echo $user['username']; ?>" disabled>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
    </div>

    <div class="form-group">
      <label>No WhatsApp</label>
      <input type="text" name="no_whatsapp" value="<?php echo $user['no_whatsapp']; ?>">
    </div>

    <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>

  </form>

</div>

</body>
</html>