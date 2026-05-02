<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username_login = $_SESSION['user'];

$stmtUser = $conn->prepare("SELECT id, username, email, no_whatsapp FROM users WHERE username=? LIMIT 1");
$stmtUser->bind_param("s", $username_login);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

if (!$user) {
    header("Location: login.php");
    exit;
}

$message = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $user['id'];
    $nama_brand = trim($_POST['nama_brand']);
    $deskripsi_singkat = trim($_POST['deskripsi_singkat']);
    $email_bisnis = trim($_POST['email_bisnis']);
    $no_wa = trim($_POST['no_wa']);

    // cek apakah user sudah pernah daftar promotor
    $check = $conn->prepare("SELECT id FROM promotor WHERE user_id=? LIMIT 1");
    $check->bind_param("i", $user_id);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        $message = "Anda sudah pernah mendaftar sebagai promotor.";
    } else {

        $logo = "";
        $banner = "";

        $stmt = $conn->prepare("
            INSERT INTO promotor 
            (user_id, nama_brand, deskripsi_singkat, email_bisnis, no_wa, logo, banner, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->bind_param(
            "issssss",
            $user_id,
            $nama_brand,
            $deskripsi_singkat,
            $email_bisnis,
            $no_wa,
            $logo,
            $banner
        );

        if ($stmt->execute()) {
            $success = "Pendaftaran promotor berhasil. Menunggu verifikasi admin.";
        } else {
            $message = "Gagal mendaftar promotor.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Promotor - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/components/navbar.php'; ?>

<main>
    <div class="register-container">
        <h2>Daftar Jadi Promotor</h2>
        <p>Lengkapi data brand atau organisasi event Anda.</p>

        <?php if ($success): ?>
            <div class="popup-success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <p class="error-msg"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="nama_brand" placeholder="Nama Brand / Organizer" required>

            <input type="text" name="deskripsi_singkat" placeholder="Deskripsi singkat promotor" required>

            <input type="email" name="email_bisnis" placeholder="Email bisnis" value="<?= htmlspecialchars($user['email']); ?>" required>

            <input type="text" name="no_wa" placeholder="No WhatsApp" value="<?= htmlspecialchars($user['no_whatsapp']); ?>" required>

            <button type="submit">Daftar Promotor</button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>