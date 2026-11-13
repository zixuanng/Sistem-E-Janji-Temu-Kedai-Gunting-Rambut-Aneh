<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
startSession();

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
if (isAdmin())    { header('Location: admin/laporan.php'); exit; }

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $noKad    = clean($_POST['noKad']    ?? '');
    $nama     = clean($_POST['nama']     ?? '');
    $noTel    = clean($_POST['noTel']    ?? '');
    $kata1    = $_POST['kataLaluan']     ?? '';
    $kata2    = $_POST['sahKataLaluan']  ?? '';
    $old      = compact('noKad','nama','noTel');

    if (empty($noKad))                        $errors[] = 'No. Kad Pengenalan diperlukan.';
    elseif (!preg_match('/^\d{12}$/', $noKad)) $errors[] = 'No. Kad Pengenalan mestilah 12 digit nombor sahaja.';
    if (empty($nama))                         $errors[] = 'Nama Pelanggan diperlukan.';
    if (empty($noTel))                        $errors[] = 'No. Telefon diperlukan.';
    elseif (!preg_match('/^01\d{8,9}$/', $noTel)) $errors[] = 'No. Telefon tidak sah (cth: 0122222222).';
    if (strlen($kata1) < 6)                   $errors[] = 'Kata Laluan mestilah sekurang-kurangnya 6 aksara.';
    if ($kata1 !== $kata2)                    $errors[] = 'Kata Laluan dan Sahkan Kata Laluan tidak sepadan.';

    if (empty($errors)) {
        $pdo = getDB();
        // Check duplicate IC
        $check = $pdo->prepare("SELECT idPelanggan FROM PELANGGAN WHERE idPelanggan = ?");
        $check->execute([$noKad]);
        if ($check->fetch()) {
            $errors[] = 'No. Kad Pengenalan ini telah didaftarkan.';
        } else {
            $hash = password_hash($kata1, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare("INSERT INTO PELANGGAN (idPelanggan,namaPelanggan,noTel,kataLaluan) VALUES (?,?,?,?)");
            $ins->execute([$noKad, $nama, $noTel, $hash]);
            flashMessage('success', 'Pendaftaran berjaya! Sila log masuk.');
            header('Location: log-masuk.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar – Kedai Gunting Rambut Aneh</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">Kedai Gunting<br>Rambut Aneh</a>
  <ul class="navbar-nav">
    <li><a href="index.php"    class="nav-link">Utama</a></li>
    <li><a href="log-masuk.php" class="nav-link">Log Masuk</a></li>
    <li><a href="daftar.php"   class="nav-link active">Daftar</a></li>
  </ul>
</nav>

<div class="page-wrapper">
  <div class="card auth-card" style="max-width:500px;">
    <div class="card-header">
      <h2>Pendaftaran</h2>
      <p>Cipta akaun baharu anda</p>
    </div>
    <div class="card-body">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): ?><?= $e ?><br><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="daftar.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label class="form-label" for="noKad">No. Kad Pengenalan</label>
          <input class="form-control" type="text" id="noKad" name="noKad"
                 placeholder="Contoh: 100125070415" maxlength="12"
                 value="<?= clean($old['noKad'] ?? '') ?>" required autofocus>
          <span class="form-hint">12 digit tanpa sempang. Ini akan menjadi ID Pengguna anda.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="nama">Nama Pelanggan</label>
          <input class="form-control" type="text" id="nama" name="nama"
                 placeholder="Nama penuh anda"
                 value="<?= clean($old['nama'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="noTel">No. Telefon</label>
          <input class="form-control" type="tel" id="noTel" name="noTel"
                 placeholder="Contoh: 0122222222"
                 value="<?= clean($old['noTel'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="kataLaluan">Kata Laluan</label>
          <input class="form-control" type="password" id="kataLaluan" name="kataLaluan"
                 placeholder="Sekurang-kurangnya 6 aksara" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="sahKataLaluan">Sahkan Kata Laluan</label>
          <input class="form-control" type="password" id="sahKataLaluan" name="sahKataLaluan"
                 placeholder="Ulang kata laluan anda" required>
        </div>

        <div class="divider"></div>

        <button type="submit" class="btn btn-primary btn-block">Daftar</button>

        <p class="text-center mt-2" style="font-size:.82rem;color:var(--text-light);">
          Sudah didaftar? <a href="log-masuk.php">Log Masuk sini</a>
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
