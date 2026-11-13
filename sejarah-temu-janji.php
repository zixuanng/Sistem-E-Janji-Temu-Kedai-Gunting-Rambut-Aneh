<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pdo         = getDB();
$idPelanggan = $_SESSION['pelanggan_id'];
$flash       = getFlash();

// Pagination
$perPage = 8;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$total   = $pdo->prepare("SELECT COUNT(*) FROM JANJITEMU WHERE idPelanggan = ?");
$total->execute([$idPelanggan]);
$totalRows  = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare("
    SELECT j.*, p.namaPerkhidmatan
    FROM JANJITEMU j
    JOIN PERKHIDMATAN p ON j.idPerkhidmatan = p.idPerkhidmatan
    WHERE j.idPelanggan = ?
    ORDER BY j.tarikh DESC, j.masa DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$idPelanggan, $perPage, $offset]);
$senarai = $stmt->fetchAll();

function statusBadge(string $s): string {
    return match(strtolower($s)) {
        'selesai'       => '<span class="badge badge-selesai">Selesai</span>',
        'tidak selesai' => '<span class="badge badge-tidak">Tidak Selesai</span>',
        default         => '<span class="badge badge-menunggu">Menunggu</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sejarah Temu Janji – Kedai Gunting Rambut Aneh</title>
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

  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
    <?= clean($flash['msg']) ?>
  </div>
  <?php endif; ?>

  <div class="flex-between mb-2">
    <h2>Sejarah Temu Janji</h2>
    <a href="buat-temu-janji.php" class="btn btn-primary btn-sm">+ Temu Janji Baru</a>
  </div>

  <?php if (empty($senarai)): ?>
    <div class="card">
      <div class="card-body text-center" style="padding:3rem 2rem;">
        <p style="color:var(--text-light);font-size:1rem;">Tiada rekod temu janji ditemui.</p>
        <a href="buat-temu-janji.php" class="btn btn-primary mt-2">Buat Temu Janji Sekarang</a>
      </div>
    </div>
  <?php else: ?>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Tarikh</th>
          <th>Slot Masa</th>
          <th>Perkhidmatan</th>
          <th>Catatan</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($senarai as $row): ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($row['tarikh'])) ?></td>
          <td><?= clean($row['masa']) ?></td>
          <td><?= clean($row['namaPerkhidmatan']) ?></td>
          <td><?= $row['catatan'] ? clean($row['catatan']) : '<span style="color:var(--text-light)">-</span>' ?></td>
          <td><?= statusBadge($row['status']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page-1 ?>" class="page-link">← Sebelum</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page+1 ?>" class="page-link">Seterusnya →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>

</body>
</html>
