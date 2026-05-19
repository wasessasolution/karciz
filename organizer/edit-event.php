<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage-event.php");
    exit;
}

$username = $_SESSION['user'];
$event_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT promotor.id, promotor.nama_brand
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    die("Promotor tidak ditemukan.");
}

$promotor_id = $promotor['id'];
$error = "";
$success = "";

/* Ambil event */
$stmt = $conn->prepare("
    SELECT *
    FROM events
    WHERE id = ? AND organizer_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $event_id, $promotor_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event tidak ditemukan atau bukan milik Anda.");
}

/* Update event */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_event = trim($_POST['nama_event'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_mulai = $_POST['jam_mulai'] ?? null;
    $jam_selesai = $_POST['jam_selesai'] ?? null;
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = $_POST['status'] ?? 'aktif';

    $banner_name = $event['banner'];

    if ($nama_event === '' || $lokasi === '' || $tanggal === '' || $deskripsi === '') {
        $error = "Nama event, lokasi, tanggal, dan deskripsi wajib diisi.";
    }

    if (!$error && !empty($_FILES['banner']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Format banner harus JPG, JPEG, PNG, atau WEBP.";
        } else {
            $banner_name = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = "../assets/images/events/" . $banner_name;

            if (!move_uploaded_file($_FILES['banner']['tmp_name'], $upload_path)) {
                $error = "Upload banner gagal.";
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("
            UPDATE events
            SET
                nama_event = ?,
                lokasi = ?,
                tanggal = ?,
                jam_mulai = ?,
                jam_selesai = ?,
                deskripsi = ?,
                banner = ?,
                status = ?
            WHERE id = ?
              AND organizer_id = ?
        ");

        $stmt->bind_param(
            "ssssssssii",
            $nama_event,
            $lokasi,
            $tanggal,
            $jam_mulai,
            $jam_selesai,
            $deskripsi,
            $banner_name,
            $status,
            $event_id,
            $promotor_id
        );

        if ($stmt->execute()) {
            header("Location: edit-event.php?id=" . $event_id . "&success=1");
            exit;
        } else {
            $error = "Gagal update event.";
        }
    }
}

/* Ambil tiket */
$stmt = $conn->prepare("
    SELECT *
    FROM tickets
    WHERE event_id = ?
    ORDER BY harga ASC
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$tickets = $stmt->get_result();

/* Refresh event setelah update */
$stmt = $conn->prepare("
    SELECT *
    FROM events
    WHERE id = ? AND organizer_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $event_id, $promotor_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Event - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Edit Event</h3>
                <p>Ubah detail event dan informasi jadwal</p>
            </div>
        </div>

        <div class="form-card">

            <?php if (isset($_GET['success'])): ?>
                <div class="success-msg">Event berhasil diperbarui.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <label>Nama Event</label>
                <input 
                    type="text" 
                    name="nama_event" 
                    value="<?= htmlspecialchars($event['nama_event']); ?>" 
                    required
                >

                <label>Lokasi</label>
                <input 
                    type="text" 
                    name="lokasi" 
                    value="<?= htmlspecialchars($event['lokasi']); ?>" 
                    required
                >

                <div class="form-grid">
                    <div>
                        <label>Tanggal Event</label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            value="<?= htmlspecialchars($event['tanggal']); ?>" 
                            required
                        >
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status" required>
                            <option value="aktif" <?= $event['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="selesai" <?= $event['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Jam Mulai</label>
                        <input 
                            type="time" 
                            name="jam_mulai" 
                            value="<?= htmlspecialchars($event['jam_mulai'] ?? ''); ?>"
                        >
                    </div>

                    <div>
                        <label>Jam Selesai</label>
                        <input 
                            type="time" 
                            name="jam_selesai" 
                            value="<?= htmlspecialchars($event['jam_selesai'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="6" required><?= htmlspecialchars($event['deskripsi']); ?></textarea>

                <label>Banner Saat Ini</label>
                <?php if (!empty($event['banner'])): ?>
                    <div style="margin:10px 0;">
                        <img 
                            src="/Karciz/assets/images/events/<?= htmlspecialchars($event['banner']); ?>" 
                            style="max-width:260px;border-radius:14px;"
                        >
                    </div>
                <?php else: ?>
                    <p>Belum ada banner.</p>
                <?php endif; ?>

                <label>Ganti Banner</label>
                <input type="file" name="banner" accept="image/*">

                <button type="submit" class="btn-add" style="margin-top:20px;">
                    Simpan Perubahan
                </button>

                <a href="manage-event.php" class="btn-view" style="display:inline-block;margin-top:20px;">
                    Kembali
                </a>

            </form>
        </div>

        <div class="table-container" style="margin-top:24px;">
            <h3>Daftar Tiket Event</h3>

            <table>
                <thead>
                    <tr>
                        <th>Nama Tiket</th>
                        <th>Harga</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tickets->num_rows > 0): ?>
                        <?php while ($ticket = $tickets->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($ticket['nama_tiket']); ?></td>
                                <td>Rp <?= number_format($ticket['harga'], 0, ',', '.'); ?></td>
                                <td><?= $ticket['stok']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Belum ada tiket.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>