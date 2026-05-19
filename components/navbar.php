<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

/* LANGUAGE */
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'] === 'en' ? 'en' : 'id';
}

$lang = $_SESSION['lang'] ?? 'id';

$text = [
    'id' => [
        'search' => 'Cari event favoritmu...',
        'category' => 'Kategori',
        'all_category' => 'Semua Kategori',
        'location' => 'Lokasi',
        'date' => 'Tanggal',
        'show_finished' => 'Tampilkan event selesai',
        'apply_filter' => 'Terapkan Filter',
        'search_btn' => 'Cari',
        'login' => 'Login',
        'promoter_dashboard' => 'Dashboard Promotor',
        'admin_dashboard' => 'Dashboard SA',
        'my_karciz' => 'My KarciZ',
        'edit_profile' => 'Edit Profile',
        'my_ticket' => 'My Ticket',
        'logout' => 'Logout',
        'not_found' => 'Event tidak ditemukan.',
        'failed_search' => 'Gagal memuat hasil pencarian.'
    ],
    'en' => [
        'search' => 'Search your favorite event...',
        'category' => 'Category',
        'all_category' => 'All Categories',
        'location' => 'Location',
        'date' => 'Date',
        'show_finished' => 'Show finished events',
        'apply_filter' => 'Apply Filter',
        'search_btn' => 'Search',
        'login' => 'Log In',
        'promoter_dashboard' => 'Promoter Dashboard',
        'admin_dashboard' => 'Super Admin Dashboard',
        'my_karciz' => 'My KarciZ',
        'edit_profile' => 'Edit Profile',
        'my_ticket' => 'My Ticket',
        'logout' => 'Logout',
        'not_found' => 'Event not found.',
        'failed_search' => 'Failed to load search results.'
    ]
];

function tr_text($key) {
    global $text, $lang;
    return $text[$lang][$key] ?? $key;
}

function lang_link($selectedLang) {
    $query = $_GET;
    $query['lang'] = $selectedLang;
    return '?' . http_build_query($query);
}

$user_data = null;
$role = $_SESSION['role'] ?? null;

if (!empty($_SESSION['user'])) {
    $username = $_SESSION['user'];

    $stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$profile_img = $user_data && !empty($user_data['profile_image'])
    ? $user_data['profile_image']
    : 'default-profile.png';

$username_display = $user_data['username'] ?? 'Guest';
?>

<header class="premium-navbar">
  <div class="container premium-nav-wrap">

    <a href="/Karciz/index.php" class="premium-logo">
      <img src="/Karciz/assets/images/logo/logo.png" alt="KarciZ">
    </a>

    <form action="/Karciz/index.php" method="GET" class="premium-search" autocomplete="off">
      <input type="hidden" name="lang" value="<?= htmlspecialchars($lang); ?>">

      <div class="search-main">
        <input 
          type="text" 
          name="search"
          id="liveSearchInput"
          placeholder="<?= htmlspecialchars(tr_text('search')); ?>"
          value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>"
        >

        <button type="button" class="filter-toggle" id="filterToggle">☷</button>

        <button type="submit" class="search-submit">
          <?= htmlspecialchars(tr_text('search_btn')); ?>
        </button>
      </div>

      <div class="live-search-result" id="liveSearchResult"></div>

      <div class="search-filter-panel" id="filterPanel">
        <div class="filter-group">
          <label><?= htmlspecialchars(tr_text('category')); ?></label>
          <select name="category">
            <option value=""><?= htmlspecialchars(tr_text('all_category')); ?></option>
            <option value="music">Music</option>
            <option value="sport">Sport</option>
            <option value="seminar">Seminar</option>
            <option value="festival">Festival</option>
          </select>
        </div>

        <div class="filter-group">
          <label><?= htmlspecialchars(tr_text('location')); ?></label>
          <input 
            type="text" 
            name="location" 
            placeholder="Contoh: Jakarta"
            value="<?= htmlspecialchars($_GET['location'] ?? ''); ?>"
          >
        </div>

        <div class="filter-group">
          <label><?= htmlspecialchars(tr_text('date')); ?></label>
          <input 
            type="date" 
            name="date"
            value="<?= htmlspecialchars($_GET['date'] ?? ''); ?>"
          >
        </div>

        <label class="filter-check">
          <input 
            type="checkbox" 
            name="include_expired" 
            value="1"
            <?= isset($_GET['include_expired']) ? 'checked' : ''; ?>
          >
          <span><?= htmlspecialchars(tr_text('show_finished')); ?></span>
        </label>

        <button type="submit" class="filter-apply-btn">
          <?= htmlspecialchars(tr_text('apply_filter')); ?>
        </button>
      </div>
    </form>

    <button class="hamburger-btn" id="hamburgerBtn" type="button">☰</button>

    <nav class="premium-nav-menu" id="navMenu">

      <div class="language-dropdown">
        <button type="button" class="language-btn" id="languageBtn">
          🌐 <?= strtoupper($lang); ?> <span>▾</span>
        </button>

        <div class="language-menu" id="languageMenu">
          <a href="<?= lang_link('id'); ?>">🇮🇩 Indonesia</a>
          <a href="<?= lang_link('en'); ?>">🇺🇸 English</a>
        </div>
      </div>

      <?php if ($user_data) { ?>

        <?php if ($role === 'organizer') { ?>
          <a href="/Karciz/organizer/dashboard.php"><?= htmlspecialchars(tr_text('promoter_dashboard')); ?></a>
        <?php } ?>

        <?php if ($role === 'superadmin') { ?>
          <a href="/Karciz/super-admin/dashboard.php"><?= htmlspecialchars(tr_text('admin_dashboard')); ?></a>
        <?php } ?>

        <div class="profile-dropdown">
          <button type="button" class="profile-dropdown-btn" id="profileDropdownBtn">
            <img src="/Karciz/assets/images/profile/<?= htmlspecialchars($profile_img); ?>" alt="Profile">
            <strong><?= htmlspecialchars($username_display); ?></strong>
            <span>▾</span>
          </button>

          <div class="profile-dropdown-menu" id="profileDropdownMenu">
            <a href="/Karciz/customer/history_transaksi.php"><?= htmlspecialchars(tr_text('my_karciz')); ?></a>
            <a href="/Karciz/customer/profile.php"><?= htmlspecialchars(tr_text('edit_profile')); ?></a>
            <a href="/Karciz/customer/history_transaksi.php"><?= htmlspecialchars(tr_text('my_ticket')); ?></a>
            <a href="/Karciz/logout.php" class="nav-logout"><?= htmlspecialchars(tr_text('logout')); ?></a>
          </div>
        </div>

      <?php } else { ?>
        <a href="/Karciz/login.php" class="nav-login-btn"><?= htmlspecialchars(tr_text('login')); ?></a>
      <?php } ?>

    </nav>

  </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const navbar = document.querySelector(".premium-navbar");

  function closeAllDropdowns() {
    document.getElementById("profileDropdownMenu")?.classList.remove("show");
    document.getElementById("languageMenu")?.classList.remove("show");
    document.getElementById("filterPanel")?.classList.remove("show");
  }

  function handleNavbarScroll() {
    if (!navbar) return;
    navbar.classList.toggle("scrolled", window.scrollY > 40);
  }

  handleNavbarScroll();
  window.addEventListener("scroll", handleNavbarScroll);

  const hamburgerBtn = document.getElementById("hamburgerBtn");
  const navMenu = document.getElementById("navMenu");

  if (hamburgerBtn && navMenu) {
    hamburgerBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      navMenu.classList.toggle("show");
      hamburgerBtn.textContent = navMenu.classList.contains("show") ? "×" : "☰";
    });
  }

  const profileBtn = document.getElementById("profileDropdownBtn");
  const profileMenu = document.getElementById("profileDropdownMenu");

  if (profileBtn && profileMenu) {
    profileBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      profileMenu.classList.toggle("show");
      document.getElementById("languageMenu")?.classList.remove("show");
      document.getElementById("filterPanel")?.classList.remove("show");
    });
  }

  const languageBtn = document.getElementById("languageBtn");
  const languageMenu = document.getElementById("languageMenu");

  if (languageBtn && languageMenu) {
    languageBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      languageMenu.classList.toggle("show");
      document.getElementById("profileDropdownMenu")?.classList.remove("show");
      document.getElementById("filterPanel")?.classList.remove("show");
    });
  }

  const filterToggle = document.getElementById("filterToggle");
  const filterPanel = document.getElementById("filterPanel");

  if (filterToggle && filterPanel) {
    filterToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      filterPanel.classList.toggle("show");
      document.getElementById("profileDropdownMenu")?.classList.remove("show");
      document.getElementById("languageMenu")?.classList.remove("show");
      document.getElementById("liveSearchResult")?.classList.remove("show");
    });
  }

  const liveInput = document.getElementById("liveSearchInput");
  const liveResult = document.getElementById("liveSearchResult");
  let searchTimer = null;

  if (liveInput && liveResult) {
    liveInput.addEventListener("input", function () {
      const keyword = this.value.trim();

      clearTimeout(searchTimer);

      if (keyword.length < 2) {
        liveResult.classList.remove("show");
        liveResult.innerHTML = "";
        return;
      }

      searchTimer = setTimeout(function () {
        fetch(`/Karciz/ajax/search-events.php?q=${encodeURIComponent(keyword)}`)
          .then(res => res.json())
          .then(data => {
            if (!data.length) {
              liveResult.innerHTML = `<div class="live-search-empty"><?= htmlspecialchars(tr_text('not_found')); ?></div>`;
              liveResult.classList.add("show");
              return;
            }

            liveResult.innerHTML = data.map(item => {
              const banner = item.banner
                ? `/Karciz/assets/images/events/${item.banner}`
                : `/Karciz/assets/images/logo/logo.png`;

              const jam = item.jam_mulai ? item.jam_mulai.substring(0, 5) : "";

              return `
                <a href="/Karciz/customer/event-detail.php?id=${item.id}" class="live-search-item">
                  <img src="${banner}" alt="${item.nama_event}">
                  <div>
                    <h4>${item.nama_event}</h4>
                    <p>${item.lokasi}</p>
                    <span>${item.tanggal}${jam ? " • " + jam : ""}</span>
                  </div>
                </a>
              `;
            }).join("");

            liveResult.classList.add("show");
            closeAllDropdowns();
          })
          .catch(() => {
            liveResult.innerHTML = `<div class="live-search-empty"><?= htmlspecialchars(tr_text('failed_search')); ?></div>`;
            liveResult.classList.add("show");
          });
      }, 250);
    });
  }

  document.addEventListener("click", function (e) {
    const liveResult = document.getElementById("liveSearchResult");
    const liveInput = document.getElementById("liveSearchInput");

    closeAllDropdowns();

    if (liveResult && liveInput && !liveResult.contains(e.target) && e.target !== liveInput) {
      liveResult.classList.remove("show");
    }
  });

  document.querySelectorAll(".search-filter-panel, .profile-dropdown-menu, .language-menu, .live-search-result")
    .forEach(panel => {
      panel.addEventListener("click", function (e) {
        e.stopPropagation();
      });
    });
});
</script>