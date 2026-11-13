<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
startSession();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem E-Janji Temu – Kedai Gunting Rambut Aneh</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">Kedai Gunting<br>Rambut Aneh</a>
  <ul class="navbar-nav">
    <li><a href="index.php" class="nav-link active">Utama</a></li>
    <?php if (isAdmin()): ?>
      <li><a href="admin/laporan.php" class="nav-link">Laporan</a></li>
      <li><a href="log-keluar.php" class="nav-link">Log Keluar</a></li>
    <?php elseif (isLoggedIn()): ?>
      <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
      <li><a href="log-keluar.php" class="nav-link">Log Keluar</a></li>
    <?php else: ?>
      <li><a href="log-masuk.php" class="nav-link">Log Masuk</a></li>
      <li><a href="daftar.php" class="nav-link">Daftar</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Hero -->
<section class="hero">
  <h1>Tempah Janji Temu<br>Anda Dalam Talian</h1>
  <div class="hero-divider"></div>
  <p>Tiada lagi beratur atau panggilan telefon. Pilih tarikh, perkhidmatan dan slot masa anda dengan mudah.</p>
  <?php if (!isLoggedIn() && !isAdmin()): ?>
  <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
    <a href="daftar.php"    class="btn btn-outline" style="color:var(--champagne);border-color:var(--champagne);">Daftar</a>
    <a href="log-masuk.php" class="btn btn-gold">Log Masuk</a>
  </div>
  <?php else: ?>
  <a href="<?= isAdmin() ? 'admin/laporan.php' : 'dashboard.php' ?>" class="btn btn-gold">Ke Dashboard</a>
  <?php endif; ?>
</section>

<!-- Services -->
<section class="services-section">
  <div class="container">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
      <?= clean($flash['msg']) ?>
    </div>
    <?php endif; ?>
    <p class="section-sub">Perkhidmatan Kami</p>
    <h2 class="section-title">Apa Yang Kami Tawarkan</h2>
    <div class="gold-line"></div>
    <div class="services-grid">
      <div class="service-card">
        <div class="icon">✂️</div>
        <h3>Gunting Rambut</h3>
        <p style="color:var(--text-light);font-size:.8rem;margin-top:.5rem;">Potongan rambut profesional untuk semua gaya</p>
      </div>
      <div class="service-card">
        <div class="icon">💈</div>
        <h3>Gaya Rambut</h3>
        <p style="color:var(--text-light);font-size:.8rem;margin-top:.5rem;">Styling eksklusif mengikut citarasa anda</p>
      </div>
      <div class="service-card">
        <div class="icon">🚿</div>
        <h3>Cuci Rambut</h3>
        <p style="color:var(--text-light);font-size:.8rem;margin-top:.5rem;">Rawatan cuci rambut yang menyegarkan</p>
      </div>
    </div>
    <div style="text-align:center;margin-top:2.5rem;">
      <?php if (!isLoggedIn() && !isAdmin()): ?>
      <p style="color:var(--text-light);font-size:.85rem;margin-bottom:1rem;">Daftar atau log masuk untuk membuat temu janji.</p>
      <a href="daftar.php" class="btn btn-outline">Daftar Sekarang</a>
      &nbsp;
      <a href="log-masuk.php" class="btn btn-primary">Log Masuk</a>
      <?php else: ?>
      <a href="<?= isAdmin() ? 'admin/laporan.php' : 'buat-temu-janji.php' ?>" class="btn btn-primary">
        <?= isAdmin() ? 'Lihat Laporan' : 'Buat Temu Janji' ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>

</body>
</html>
