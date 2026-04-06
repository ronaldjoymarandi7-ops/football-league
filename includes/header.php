<?php
session_start();
require_once __DIR__ . '/config.php';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — ' . SITE_NAME : SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <span class="nav-icon">⚽</span>
        <span class="brand-text"><?= SITE_NAME ?></span>
    </div>
    <ul class="nav-links">
        <li><a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/teams.php" class="<?= $current_page === 'teams' ? 'active' : '' ?>">Teams</a></li>
        <li><a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/players.php" class="<?= $current_page === 'players' ? 'active' : '' ?>">Players</a></li>
        <li><a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/matches.php" class="<?= $current_page === 'matches' ? 'active' : '' ?>">Matches</a></li>
        <li><a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/standings.php" class="<?= $current_page === 'standings' ? 'active' : '' ?>">Standings</a></li>
    </ul>
    <div class="nav-hamburger" onclick="toggleMenu()">☰</div>
</nav>

<?php
$flash = getFlash();
if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['msg']) ?>
    <span class="flash-close" onclick="this.parentElement.remove()">✕</span>
</div>
<?php endif; ?>
