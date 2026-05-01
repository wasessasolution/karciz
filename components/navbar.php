<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

$user_data = null;
$role = $_SESSION['role'] ?? null;

if (!empty($_SESSION['user'])) {
    $username = $_SESSION['user'];

    if ($stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE username=? LIMIT 1")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
    }
}

// default fallback
$profile_img = 'default-profile.png';
$username_display = 'Guest';

if ($user_data) {
    $profile_img = !empty($user_data['profile_image']) 
        ? $user_data['profile_image'] 
        : 'default-profile.png';

    $username_display = $user_data['username'] ?? 'Guest';
}
?>

<header class="navbar">
  <div class="container nav-wrapper">

    <!-- LOGO -->
    <div class="logo">
      <a href="/Karciz/index.php">KarciZ</a>
    </div>

    <!-- SEARCH -->
    <div class="search-box">
      <input type="text" placeholder="Cari event favoritmu..." />
    </div>

    <!-- RIGHT -->
    <div class="nav-right">

      <?php if ($role !== 'superadmin') { ?>
        <a href="/Karciz/customer/history_transaksi.php" class="my-karciz">
          My KarciZ
        </a>
      <?php } ?>

      <?php if ($user_data) { ?>
        <div class="profile-user" id="profileToggle">

          <img 
            src="/Karciz/assets/images/profile/<?= htmlspecialchars($profile_img) ?>"
            alt="Profile"
          />

          <span><?= htmlspecialchars($username_display) ?></span>

          <!-- DROPDOWN -->
          <div class="dropdown-menu" id="profileDropdown">

            <?php if ($role !== 'superadmin') { ?>
              <a href="/Karciz/customer/profile.php">Edit Profile</a>
              <a href="/Karciz/customer/history_transaksi.php">My Ticket</a>
            <?php } ?>

            <!-- logout tetap ada -->
            <a href="/Karciz/logout.php" class="logout">Logout</a>

          </div>

        </div>
      <?php } else { ?>
        <a href="/Karciz/login.php">Login</a>
      <?php } ?>

    </div>

  </div>
</header>

<!-- JS DROPDOWN -->
<script>
document.addEventListener("DOMContentLoaded", function () {

  const toggle = document.getElementById("profileToggle");
  const dropdown = document.getElementById("profileDropdown");

  if (!toggle || !dropdown) return;

  toggle.addEventListener("click", function (e) {
    e.stopPropagation();
    dropdown.classList.toggle("show");
  });

  document.addEventListener("click", function () {
    dropdown.classList.remove("show");
  });

});
</script>