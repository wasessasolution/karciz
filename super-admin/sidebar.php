<?php
$current_page = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../lang/lang.php';
?>

<button class="admin-sidebar-toggle" id="adminSidebarToggle" type="button" onclick="toggleAdminSidebar()">
    ×
</button>

<div class="admin-sidebar" id="adminSidebar">

    <div class="admin-sidebar-brand">
        <img src="/Karciz/assets/images/logo/logo.png" alt="KarciZ">
        <span>Super Admin</span>
    </div>

    <div class="admin-sidebar-menu">
        <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="/Karciz/index.php">Ke Beranda</a>
        <a href="manage-users.php" class="<?= $current_page == 'manage-users.php' ? 'active' : ''; ?>">Kelola User</a>
        <a href="verify-organizer.php" class="<?= $current_page == 'verify-organizer.php' ? 'active' : ''; ?>">Verifikasi Organizer</a>
        <a href="all-events.php" class="<?= $current_page == 'all-events.php' ? 'active' : ''; ?>">Semua Event</a>
        <a href="transactions.php" class="<?= $current_page == 'transactions.php' ? 'active' : ''; ?>">Transaksi</a>
        <a href="reports.php" class="<?= $current_page == 'reports.php' ? 'active' : ''; ?>">Laporan</a>
        <a href="settlements.php" class="<?= $current_page == 'settlements.php' ? 'active' : ''; ?>">Settlement</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="admin-sidebar-footer">
        <strong>KarciZ</strong>
        <p>© 2026 KarciZ</p>
        <small>Powered by Wasessa Solution Tech</small>
    </div>

</div>

<script>
function toggleAdminSidebar() {
  const wrapper = document.querySelector(".admin-wrapper");
  const toggle = document.getElementById("adminSidebarToggle");

  if (!wrapper || !toggle) return;

  wrapper.classList.toggle("admin-sidebar-hidden");
  toggle.textContent = wrapper.classList.contains("admin-sidebar-hidden") ? "☰" : "×";
}
</script>