<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pdo         = getDB();
$idPelanggan = $_SESSION['pelanggan_id'];
$errors      = [];
$success     = false;

$user = $pdo->prepare("SELECT * FROM PELANGGAN WHERE idPelanggan = ?");
$user->execute([$idPelanggan]);
$pelanggan = $user->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nama   = clean($_POST['nama']   ?? '');
    $noTel  = clean($_POST['noTel']  ?? '');
    $kata1  = $_POST['kataLaluan']     ?? '';
    $kata2  = $_POST['sahKataLaluan']  ?? '';

    if (empty($nama))  $errors[] = 'Nama tidak boleh kosong.';
    if (empty($noTel)) $errors[] = 'No. Telefon tidak boleh kosong.';
    elseif (!preg_match('/^01\d{8,9}$/', $noTel)) $errors[] = 'No. Telefon tidak sah.';

    $newHash = $pelanggan['kataLaluan'];
    if (!empty($kata1)) {
        if (strlen($kata1) < 6)   $errors[] = 'Kata Laluan baru mestilah sekurang-kurangnya 6 aksara.';
        if ($kata1 !== $kata2)    $errors[] = 'Kata Laluan baru dan Sahkan tidak sepadan.';
        if (empty($errors))       $newHash = password_hash($kata1, PASSWORD_DEFAULT);
    }

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE PELANGGAN SET namaPelanggan=?, noTel=?, kataLaluan=? WHERE idPelanggan=?");
        $upd->execute([$nama, $noTel, $newHash, $idPelanggan]);
        $_SESSION['pelanggan_nama'] = $nama;
        $pelanggan['namaPelanggan'] = $nama;
        $pelanggan['noTel']         = $noTel;
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya – Kedai Gunting Rambut Aneh</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">Kedai Gunting<br>Rambut Aneh</a>
  <ul class="navbar-nav">
    <li><a href="index.php"      class="nav-link">Utama</a></li>
    <li><a href="dashboard.php"  class="nav-link">Dashboard</a></li>
    <li><a href="log-keluar.php" class="nav-link">Log Keluar</a></li>
  </ul>
</nav>

<div class="container section">
  <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>

  <div class="card" style="max-width:520px;margin:0 auto;">
    <div class="card-header">
      <h2>Profil Saya</h2>
    </div>
    <div class="card-body">
      <?php if ($success): ?>
        <div class="alert alert-success">Profil berjaya dikemaskini.</div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): ?><?= $e ?><br><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="form-group">
        <label class="form-label">ID Pengguna (No. Kad Pengenalan)</label>
        <input class="form-control" type="text" value="<?= clean($pelanggan['idPelanggan']) ?>" disabled>
      </div>

      <form method="POST" action="profil.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label class="form-label" for="nama">Nama Pelanggan</label>
          <input class="form-control" type="text" id="nama" name="nama"
                 value="<?= clean($pelanggan['namaPelanggan']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="noTel">No. Telefon</label>
          <input class="form-control" type="tel" id="noTel" name="noTel"
                 value="<?= clean($pelanggan['noTel']) ?>" required>
        </div>

        <div class="divider"></div>
        <p style="font-size:.8rem;color:var(--text-light);margin-bottom:1rem;">Kosongkan jika tidak mahu tukar kata laluan.</p>

        <div class="form-group">
          <label class="form-label" for="kataLaluan">Kata Laluan Baru</label>
          <input class="form-control" type="password" id="kataLaluan" name="kataLaluan" placeholder="Kata laluan baru (pilihan)">
        </div>

        <div class="form-group">
          <label class="form-label" for="sahKataLaluan">Sahkan Kata Laluan Baru</label>
          <input class="form-control" type="password" id="sahKataLaluan" name="sahKataLaluan" placeholder="Ulang kata laluan baru">
        </div>

        <div class="divider"></div>
        <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
      </form>
    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>
</body>
</html>
