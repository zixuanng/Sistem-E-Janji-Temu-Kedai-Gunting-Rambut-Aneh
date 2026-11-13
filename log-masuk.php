<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
startSession();

// Redirect if already logged in
if (isLoggedIn())  { header('Location: dashboard.php'); exit; }
if (isAdmin())     { header('Location: admin/laporan.php'); exit; }

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $idPengguna = clean($_POST['idPengguna'] ?? '');
    $kataLaluan = $_POST['kataLaluan'] ?? '';
    $old['idPengguna'] = $idPengguna;

    if (empty($idPengguna)) $errors[] = 'Sila masukkan ID Pengguna.';
    if (empty($kataLaluan)) $errors[] = 'Sila masukkan Kata Laluan.';

    if (empty($errors)) {
        // Check admin first
        if ($idPengguna === ADMIN_ID && $kataLaluan === ADMIN_PASSWORD) {
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_name'] = 'Admin';
            flashMessage('success', 'Selamat datang, Admin!');
            header('Location: admin/laporan.php');
            exit;
        }

        // Check customer
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM PELANGGAN WHERE idPelanggan = ?");
        $stmt->execute([$idPengguna]);
        $user = $stmt->fetch();

        if ($user && password_verify($kataLaluan, $user['kataLaluan'])) {
            $_SESSION['pelanggan_id']   = $user['idPelanggan'];
            $_SESSION['pelanggan_nama'] = $user['namaPelanggan'];
            flashMessage('success', 'Selamat datang, ' . $user['namaPelanggan'] . '!');
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'ID Pengguna atau Kata Laluan tidak betul.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log Masuk – Kedai Gunting Rambut Aneh</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">Kedai Gunting<br>Rambut Aneh</a>
  <ul class="navbar-nav">
    <li><a href="index.php"    class="nav-link">Utama</a></li>
    <li><a href="log-masuk.php" class="nav-link active">Log Masuk</a></li>
    <li><a href="daftar.php"   class="nav-link">Daftar</a></li>
  </ul>
</nav>

<div class="page-wrapper">
  <div class="card auth-card">
    <div class="card-header">
      <h2>Log Masuk</h2>
      <p>Sila log masuk untuk meneruskan</p>
    </div>
    <div class="card-body">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): ?><?= $e ?><br><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="log-masuk.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label class="form-label" for="idPengguna">ID Pengguna</label>
          <input class="form-control" type="text" id="idPengguna" name="idPengguna"
                 placeholder="Contoh: 100125070415"
                 value="<?= clean($old['idPengguna'] ?? '') ?>" required autofocus>
          <span class="form-hint">Masukkan No. Kad Pengenalan anda (tanpa sempang)</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="kataLaluan">Kata Laluan</label>
          <input class="form-control" type="password" id="kataLaluan" name="kataLaluan"
                 placeholder="Kata laluan anda" required>
        </div>

        <div class="divider"></div>

        <button type="submit" class="btn btn-primary btn-block">Log Masuk</button>

        <p class="text-center mt-2" style="font-size:.82rem;color:var(--text-light);">
          Belum didaftar? <a href="daftar.php">Daftar sini</a>
        </p>
      </form>
    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>

</body>
</html>
