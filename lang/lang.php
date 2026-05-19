<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'id';

$translations = [

    'id' => [
        'search_placeholder' => 'Cari event favoritmu...',
        'login' => 'Login',
        'dashboard_promotor' => 'Dashboard Promotor',
        'dashboard_sa' => 'Dashboard SA',
        'mykarciz' => 'My KarciZ',
        'edit_profile' => 'Edit Profile',
        'myticket' => 'Tiket Saya',
        'logout' => 'Logout',
        'apply_filter' => 'Terapkan Filter',
        'all_category' => 'Semua Kategori',
        'show_finished' => 'Tampilkan event selesai'
    ],

    'en' => [
        'search_placeholder' => 'Search your favorite event...',
        'login' => 'Login',
        'dashboard_promotor' => 'Promoter Dashboard',
        'dashboard_sa' => 'Super Admin Dashboard',
        'mykarciz' => 'My KarciZ',
        'edit_profile' => 'Edit Profile',
        'myticket' => 'My Tickets',
        'logout' => 'Logout',
        'apply_filter' => 'Apply Filter',
        'all_category' => 'All Categories',
        'show_finished' => 'Show finished events'
    ]
];

function t($key)
{
    global $translations, $lang;

    return $translations[$lang][$key] ?? $key;
}