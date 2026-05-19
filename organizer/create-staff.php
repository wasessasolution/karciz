<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];
$error = "";
$success = "";

$stmt = $conn->prepare("
    SELECT promotor.id, promotor.nama_brand
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username = ? AND promotor.status = 'approved'
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Akun promotor belum valid.");
}

$promotor_id = $promotor['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_staff = trim($_POST['nama_staff']);
    $staff_username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($nama_staff === "" || $staff_username === "" || $email === "" || $password === "") {
        $error = "Semua field wajib diisi.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $check->bind_param("ss", $staff_username, $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            $error = "Username atau email sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $conn->begin_transaction();

            try {
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, password, role, created_at, status)
                    VALUES (?, ?, ?, 'staff_gate', NOW(), 'approved')
                ");
                $stmt->bind_param("sss", $staff_username, $email, $hash);
                $stmt->execute();

                $user_id = $conn->insert_id;

                $stmt = $conn->prepare("
                    INSERT INTO promotor_staff (promotor_id, user_id, nama_staff)
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iis", $promotor_id, $user_id, $nama_staff);
                $stmt->execute();

                $conn->commit();
                $success = "Akun staff gate berhasil dibuat.";

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Gagal membuat staff: " . $e->getMessage();
            }
        }
    }
}

$staffs = $conn->prepare("
    SELECT users.username, users.email, promotor_staff.nama_staff, promotor_staff.created_at
    FROM promotor_staff
    JOIN users ON promotor_staff.user_id = users.id
    WHERE promotor_staff.promotor_id = ?
    ORDER BY promotor_staff.created_at DESC
");
$staffs->bind_param("i", $promotor_id);
$staffs->execute();
$list = $staffs->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Staff Gate - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Kelola Staff Gate</h3>
                <p>Staff hanya bisa melakukan scan dan validasi tiket</p>
            </div>
        </div>

        <div class="form-card">
            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-msg"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Nama Staff</label>
                <input type="text" name="nama_staff" required>

                <label>Username Login</label>
                <input type="text" name="username" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit" class="btn-add" style="margin-top:16px;">
                    Buat Akun Staff
                </button>
            </form>
        </div>

        <div class="table-container" style="margin-top:24px;">
            <h3>Daftar Staff Gate</h3>

            <table>
                <thead>
                    <tr>
                        <th>Nama Staff</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($list->num_rows > 0): ?>
                        <?php while ($row = $list->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_staff']); ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Belum ada staff gate.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

</body>
</html>