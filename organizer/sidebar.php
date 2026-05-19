<?php
require_once __DIR__ . '/../lang/lang.php';
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>

<button class="sidebar-toggle" id="sidebarToggle" type="button">×</button>

<div class="organizer-sidebar" id="organizerSidebar">

    <div class="sidebar-brand">
        <img src="/Karciz/assets/images/logo/logo.png" alt="KarciZ">
        <span><?= $role === 'staff_gate' ? 'Staff Gate' : 'Promotor'; ?></span>
    </div>

    <div class="sidebar-menu">

        <?php if ($role === 'organizer') { ?>
            <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="/Karciz/index.php">Ke Beranda</a>
            <a href="create-event.php" class="<?= $current_page == 'create-event.php' ? 'active' : ''; ?>">Buat Event</a>
            <a href="manage-event.php" class="<?= $current_page == 'manage-event.php' ? 'active' : ''; ?>">Kelola Event</a>
            <a href="scan-ticket.php" class="<?= $current_page == 'scan-ticket.php' ? 'active' : ''; ?>">Scan Tiket</a>
            <a href="ticket-tracking.php" class="<?= $current_page == 'ticket-tracking.php' ? 'active' : ''; ?>">Tracking Tiket</a>
            <a href="sales-report.php" class="<?= $current_page == 'sales-report.php' ? 'active' : ''; ?>">Laporan Penjualan</a>
            <a href="settlement.php" class="<?= $current_page == 'settlement.php' ? 'active' : ''; ?>">Settlement Dana</a>
            <a href="create-staff.php" class="<?= $current_page == 'create-staff.php' ? 'active' : ''; ?>">Staff Gate</a>
        <?php } ?>

        <?php if ($role === 'staff_gate') { ?>
            <a href="scan-ticket.php" class="<?= $current_page == 'scan-ticket.php' ? 'active' : ''; ?>">Scan Tiket</a>
            <a href="ticket-tracking.php" class="<?= $current_page == 'ticket-tracking.php' ? 'active' : ''; ?>">Tracking Tiket</a>
        <?php } ?>

        <a href="../logout.php">Logout</a>
    </div>

    <div class="sidebar-footer">
        <strong>KarciZ</strong>
        <p>© 2026 KarciZ</p>
        <small>Powered by Wasessa Solution Tech</small>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const wrapper = document.querySelector(".organizer-wrapper");
  const toggle = document.getElementById("sidebarToggle");

  if (!wrapper || !toggle) return;

  toggle.addEventListener("click", function () {
    wrapper.classList.toggle("sidebar-hidden");
    toggle.textContent = wrapper.classList.contains("sidebar-hidden") ? "☰" : "×";
  });
});
</script>