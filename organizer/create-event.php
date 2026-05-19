<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];
$error = "";

$stmt = $conn->prepare("
    SELECT promotor.* 
    FROM promotor
    JOIN users ON promotor.user_id = users.id
    WHERE users.username=? AND promotor.status='approved'
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$promotor = $stmt->get_result()->fetch_assoc();

if (!$promotor) {
    echo "Akun promotor belum terverifikasi.";
    exit;
}

$organizer_id = $promotor['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_event      = trim($_POST['nama_event'] ?? '');
    $lokasi          = trim($_POST['lokasi'] ?? '');
    $tanggal         = $_POST['tanggal'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $jam_mulai       = $_POST['jam_mulai'] ?? '';
    $jam_selesai     = $_POST['jam_selesai'] ?? '';
    $deskripsi       = trim($_POST['deskripsi'] ?? '');

    $nama_tiket_arr = $_POST['nama_tiket'] ?? [];
    $harga_arr      = $_POST['harga'] ?? [];
    $stok_arr       = $_POST['stok'] ?? [];

    $banner_name = null;

    if (
        empty($nama_event) || empty($lokasi) || empty($tanggal) ||
        empty($tanggal_selesai) || empty($jam_mulai) || empty($jam_selesai) ||
        empty($deskripsi)
    ) {
        $error = "Semua data event wajib diisi.";
    }

    if (!$error && (empty($nama_tiket_arr) || count($nama_tiket_arr) < 1)) {
        $error = "Minimal harus ada 1 kategori tiket.";
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
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO events 
                (
                    organizer_id,
                    nama_event,
                    lokasi,
                    tanggal,
                    tanggal_selesai,
                    jam_mulai,
                    jam_selesai,
                    deskripsi,
                    banner,
                    status,
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif', NOW())
            ");

            $stmt->bind_param(
                "issssssss",
                $organizer_id,
                $nama_event,
                $lokasi,
                $tanggal,
                $tanggal_selesai,
                $jam_mulai,
                $jam_selesai,
                $deskripsi,
                $banner_name
            );

            $stmt->execute();
            $event_id = $conn->insert_id;

            $stmtTicket = $conn->prepare("
                INSERT INTO tickets
                (event_id, nama_tiket, harga, stok, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            foreach ($nama_tiket_arr as $index => $nama_tiket) {
                $nama_tiket = trim($nama_tiket);
                $harga = intval($harga_arr[$index] ?? 0);
                $stok = intval($stok_arr[$index] ?? 0);

                if ($nama_tiket === "" || $harga <= 0 || $stok <= 0) {
                    throw new Exception("Semua kategori tiket wajib diisi dengan benar.");
                }

                $stmtTicket->bind_param("isii", $event_id, $nama_tiket, $harga, $stok);
                $stmtTicket->execute();
            }

            $conn->commit();

            header("Location: manage-event.php?success=created");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal membuat event: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Event - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=10">
    <link rel="stylesheet" href="/Karciz/assets/css/organizer-premium.css?v=1">
</head>
<body>

<div class="organizer-wrapper">

    <?php include 'sidebar.php'; ?>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <div>
                <h3>Buat Event</h3>
                <p>Buat event baru beserta kategori tiket</p>
            </div>
            <span><?= htmlspecialchars($promotor['nama_brand']); ?></span>
        </div>

        <div class="form-card">

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <h3>Informasi Event</h3>

                <label>Nama Event</label>
                <input type="text" name="nama_event" required>

                <label>Lokasi Event</label>
                <input type="text" name="lokasi" placeholder="Contoh: GOR Haji Agus Salim, Padang" required>

                <div class="form-grid">
                    <div>
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal" required>
                    </div>

                    <div>
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Jam Mulai</label>
                        <input type="time" name="jam_mulai" required>
                    </div>

                    <div>
                        <label>Jam Selesai</label>
                        <input type="time" name="jam_selesai" required>
                    </div>
                </div>

                <label>Deskripsi Event</label>
                <textarea name="deskripsi" rows="5" required></textarea>

                <label>Banner Event</label>
                <input type="file" name="banner" accept="image/*">

                <hr style="margin:28px 0;">

                <h3>Kategori Tiket</h3>

                <div id="ticketContainer">

                    <div class="ticket-input-group">
                        <label>Nama Kategori Tiket</label>
                        <input type="text" name="nama_tiket[]" placeholder="Contoh: Presale" required>

                        <div class="form-grid">
                            <div>
                                <label>Harga Tiket</label>
                                <input type="number" name="harga[]" min="1" placeholder="Contoh: 150000" required>
                            </div>

                            <div>
                                <label>Stok Tiket</label>
                                <input type="number" name="stok[]" min="1" placeholder="Contoh: 100" required>
                            </div>
                        </div>
                    </div>

                </div>

                <button type="button" class="btn-view" onclick="addTicketCategory()" style="margin-top:14px;">
                    + Tambah Kategori Tiket
                </button>

                <button type="submit" class="btn-add" style="margin-top:20px;">
                    Simpan Event
                </button>

            </form>

        </div>

    </div>

</div>

<script>
function addTicketCategory() {
    const container = document.getElementById('ticketContainer');

    const div = document.createElement('div');
    div.className = 'ticket-input-group';

    div.innerHTML = `
        <button type="button" class="btn-remove-ticket" onclick="this.parentElement.remove()">
            Hapus
        </button>

        <label>Nama Kategori Tiket</label>
        <input type="text" name="nama_tiket[]" placeholder="Contoh: VIP" required>

        <div class="form-grid">
            <div>
                <label>Harga Tiket</label>
                <input type="number" name="harga[]" min="1" placeholder="Contoh: 250000" required>
            </div>

            <div>
                <label>Stok Tiket</label>
                <input type="number" name="stok[]" min="1" placeholder="Contoh: 50" required>
            </div>
        </div>
    `;

    container.appendChild(div);
}
</script>

</body>
</html>