<?php
session_start();
include '../config.php';

// 🔐 PROTEKSI
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../login.php");
    exit;
}

// 🔥 HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // biar superadmin tidak bisa hapus dirinya sendiri
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage-users.php");
    exit;
}

// 🔥 AMBIL DATA USER
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola User - KarciZ</title>
  <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body>

<div class="wrapper">

  <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>KarciZ Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-users.php">Kelola User</a>
        <a href="verify-organizer.php">Verifikasi Organizer</a>
        <a href="all-events.php">Semua Event</a>
        <a href="transactions.php">Transaksi</a>
        <a href="reports.php">Laporan</a>
    </div>

  <!-- MAIN -->
  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <h3>Kelola User</h3>

      <div class="user-info">
        <span>👤 <?php echo $_SESSION['user']; ?></span>
        <a href="../logout.php">
          <button class="logout-btn">Logout</button>
        </a>
      </div>
    </div>

    <!-- TABLE USER -->
    <div class="table-container">

      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Username</th>
            <th>Email</th>
            <th>No WhatsApp</th>
            <th>Role</th>
            <th>Aksi</th>
          </tr>
        </thead>

        <tbody>
          <?php 
          $no = 1;
          while ($row = $result->fetch_assoc()) { 
          ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['username']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['no_whatsapp']; ?></td>
            <td><?= $row['role']; ?></td>
            <td>
              <a href="?delete=<?= $row['id']; ?>" 
                 onclick="return confirm('Yakin ingin hapus user ini?')">
                 <button class="btn-delete">Hapus</button>
              </a>
            </td>
          </tr>
          <?php } ?>
        </tbody>

      </table>

    </div>

  </div>

</div>

</body>
</html>