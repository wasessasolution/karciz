<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config.php';

$user_data = null;

if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
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

      <a href="/Karciz/customer/history_transaksi.php" class="my-karciz">
        My KarciZ
      </a>

      <!-- PROFILE -->
      <?php if ($user_data) { ?>
        <a href="/Karciz/customer/profile.php" class="profile-user">

          <img 
            src="/Karciz/assets/images/profile/<?=
              $user_data['profile_image'] ? $user_data['profile_image'] : 'default-profile.png'
            ?>"
            alt="Profile User"
          />

          <span><?= $user_data['username']; ?></span>

        </a>
      <?php } else { ?>
        <a href="/Karciz/login.php">Login</a>
      <?php } ?>

    </div>

  </div>
</header>