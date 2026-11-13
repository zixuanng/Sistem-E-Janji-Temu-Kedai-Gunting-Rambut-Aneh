<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$flash = getFlash();
$nama  = clean($_SESSION['pelanggan_nama'] ?? 'Pelanggan');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Kedai Gunting Rambut Aneh</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">Kedai Gunting<br>Rambut Aneh</a>
  <ul class="navbar-nav">
    <li><a href="index.php"           class="nav-link">Utama</a></li>
    <li><a href="dashboard.php"       class="nav-link active">Dashboard</a></li>
    <li><a href="log-keluar.php"      class="nav-link">Log Keluar</a></li>
  </ul>
</nav>

<div class="dashboard-header">
  <h2>Selamat Datang, <?= $nama ?>!</h2>
  <p>Urus temu janji anda dengan mudah</p>
</div>

<div class="container">
  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?> mt-3">
    <?= clean($flash['msg']) ?>
  </div>
  <?php endif; ?>
</div>

<div class="dashboard-grid">
  <a href="buat-temu-janji.php" class="dash-card">
    <div class="dash-icon">📅</div>
    <h3>Buat Temu Janji</h3>
    <p>Tempah slot baru untuk perkhidmatan</p>
  </a>
  <a href="sejarah-temu-janji.php" class="dash-card">
    <div class="dash-icon">📋</div>
    <h3>Sejarah Temu Janji</h3>
    <p>Lihat rekod temu janji lepas dan akan datang</p>
  </a>
  <a href="profil.php" class="dash-card">
    <div class="dash-icon">👤</div>
    <h3>Profil Saya</h3>
    <p>Maklumat akaun dan butiran anda</p>
  </a>
</div>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>

</body>
</html>
