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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Promotor - KarciZ</title>

    <link rel="stylesheet" href="/Karciz/assets/css/style.css?v=1">
    <link rel="stylesheet" href="/Karciz/assets/css/navbar.css?v=2">
    <link rel="stylesheet" href="/Karciz/assets/css/footer.css?v=1">
    <link rel="stylesheet" href="/Karciz/assets/css/register-promotor.css?v=1">
</head>
<body>

<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="promotor-register-page">

    <section class="promotor-register-hero">

        <div class="promotor-pattern">
            <span>🎟</span><span>★</span><span>K</span><span>🎫</span><span>🎤</span><span>♫</span>
        </div>

        <div class="container">
            <div class="promotor-register-layout">

                <div class="promotor-copy">
                    <div class="promotor-badge">Promotor Program</div>

                    <h1>Mulai Jual Tiket Event Anda di KarciZ</h1>

                    <p>
                        Daftarkan brand atau organisasi event Anda, kelola tiket,
                        pantau penjualan, dan validasi pengunjung menggunakan QR ticket.
                    </p>

                    <div class="promotor-benefits">
                        <div>
                            <strong>QR Validation</strong>
                            <span>Anti tiket duplikat saat check-in.</span>
                        </div>

                        <div>
                            <strong>Dashboard Promotor</strong>
                            <span>Kelola event, tiket, dan laporan penjualan.</span>
                        </div>

                        <div>
                            <strong>Staff Gate</strong>
                            <span>Buat akun staff untuk validasi tiket venue.</span>
                        </div>
                    </div>
                </div>

                <div class="promotor-form-card">

                    <div class="form-header">
                        <div class="form-icon">K</div>
                        <div>
                            <h2>Daftar Jadi Promotor</h2>
                            <p>Lengkapi data brand atau organisasi event Anda.</p>
                        </div>
                    </div>

                    <?php if ($success): ?>
                        <div class="promotor-alert success">
                            <?= htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div class="promotor-alert error">
                            <?= htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="promotor-form">

                        <div class="promotor-form-group">
                            <label>Nama Brand / Organizer</label>
                            <input 
                                type="text" 
                                name="nama_brand" 
                                placeholder="Contoh: KarciZ Event Organizer"
                                required
                            >
                        </div>

                        <div class="promotor-form-group">
                            <label>Deskripsi Singkat</label>
                            <textarea
                                name="deskripsi_singkat"
                                placeholder="Ceritakan singkat tentang promotor Anda"
                                rows="4"
                                required
                            ></textarea>
                        </div>

                        <div class="promotor-form-grid">
                            <div class="promotor-form-group">
                                <label>Email Bisnis</label>
                                <input 
                                    type="email" 
                                    name="email_bisnis" 
                                    value="<?= htmlspecialchars($user['email']); ?>"
                                    required
                                >
                            </div>

                            <div class="promotor-form-group">
                                <label>No WhatsApp</label>
                                <input 
                                    type="text" 
                                    name="no_wa" 
                                    value="<?= htmlspecialchars($user['no_whatsapp']); ?>"
                                    placeholder="08123456789"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="promotor-submit-btn">
                            Kirim Pendaftaran
                            <span>→</span>
                        </button>

                        <p class="promotor-note">
                            Setelah dikirim, admin KarciZ akan melakukan verifikasi akun promotor Anda.
                        </p>

                    </form>

                </div>

            </div>
        </div>

    </section>

</main>

<?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>