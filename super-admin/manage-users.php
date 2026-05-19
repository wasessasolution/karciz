<?php
session_start();
include '../config.php';
require_once __DIR__ . '/../lang/lang.php';

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
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin.css?v=1">
  <link rel="stylesheet" href="/Karciz/assets/css/superadmin-premium.css?v=2">
</head>
<body>

<div class="wrapper">

  <!-- SIDEBAR -->
  <?php include 'sidebar.php'; ?>

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