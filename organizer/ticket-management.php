<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'organizer') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['user'];
$error = "";
$success = "";

// Ambil promotor login
$stmt = $conn->prepare("
    SELECT promotor.id, promotor.nama_brand
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

// Ambil event milik promotor
$stmt = $conn->prepare("SELECT id, nama_event FROM events WHERE organizer_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events = $stmt->get_result();

// Tambah tiket
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = intval($_POST['event_id']);
    $nama_tiket = trim($_POST['nama_tiket']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);

    // validasi event harus milik promotor
    $check = $conn->prepare("SELECT id FROM events WHERE id=? AND organizer_id=? LIMIT 1");
    $check->bind_param("ii", $event_id, $organizer_id);
    $check->execute();
    $validEvent = $check->get_result()->fetch_assoc();

    if (!$validEvent) {
        $error = "Event tidak valid.";
    } elseif ($harga < 0 || $stok < 0) {
        $error = "Harga dan stok tidak boleh negatif.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO tickets (event_id, nama_tiket, harga, stok)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isii", $event_id, $nama_tiket, $harga, $stok);

        if ($stmt->execute()) {
            $success = "Tiket berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan tiket.";
        }
    }
}

// Hapus tiket
if (isset($_GET['delete'])) {
    $ticket_id = intval($_GET['delete']);

    $stmt = $conn->prepare("
        DELETE tickets FROM tickets
        JOIN events ON tickets.event_id = events.id
        WHERE tickets.id=? AND events.organizer_id=?
    ");
    $stmt->bind_param("ii", $ticket_id, $organizer_id);

    if ($stmt->execute()) {
        $success = "Tiket berhasil dihapus.";
    } else {
        $error = "Gagal menghapus tiket.";
    }
}

// Ambil tiket milik promotor
$stmt = $conn->prepare("
    SELECT 
        tickets.id,
        tickets.nama_tiket,
        tickets.harga,
        tickets.stok,
        tickets.created_at,
        events.nama_event
    FROM tickets
    JOIN events ON tickets.event_id = events.id
    WHERE events.organizer_id=?
    ORDER BY tickets.created_at DESC
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$tickets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Tiket - KarciZ</title>
    <link rel="stylesheet" href="/Karciz/assets/css/organizer.css?v=4">
</head>
<body>

<div class="organizer-wrapper">

    <div class="organizer-sidebar">
        <h2>KarciZ Promotor</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="create-event.php">Buat Event</a>
        <a href="manage-event.php">Kelola Event</a>
        <a href="ticket-management.php" class="active">Manajemen Tiket</a>
        <a href="sales-report.php">Laporan Penjualan</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="organizer-main">

        <div class="organizer-topbar">
            <h3>Manajemen Tiket</h3>
            <span><?= htmlspecialchars($promotor['nama_brand']); ?></span>
        </div>

        <?php if ($success): ?>
            <div class="success-msg"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h3>Tambah Tiket</h3>
            <br>

            <form method="POST">
                <label>Pilih Event</label>
                <select name="event_id" required>
                    <option value="">-- Pilih Event --</option>
                    <?php while ($event = $events->fetch_assoc()) { ?>
                        <option value="<?= $event['id']; ?>">
                            <?= htmlspecialchars($event['nama_event']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Nama Tiket</label>
                <input type="text" name="nama_tiket" placeholder="Contoh: Regular, VIP, Presale" required>

                <label>Harga</label>
                <input type="number" name="harga" placeholder="Contoh: 150000" min="0" required>

                <label>Stok</label>
                <input type="number" name="stok" placeholder="Contoh: 100" min="0" required>

                <button type="submit">Simpan Tiket</button>
            </form>
        </div>

        <br>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Event</th>
                        <th>Nama Tiket</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($tickets->num_rows > 0) { ?>
                        <?php $no = 1; while ($row = $tickets->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_event']); ?></td>
                                <td><?= htmlspecialchars($row['nama_tiket']); ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td><?= $row['stok']; ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="edit-ticket.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a 
                                        href="ticket-management.php?delete=<?= $row['id']; ?>" 
                                        class="btn-delete"
                                        onclick="return confirm('Yakin ingin menghapus tiket ini?')"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="empty-table">Belum ada tiket.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

</body>
</html>