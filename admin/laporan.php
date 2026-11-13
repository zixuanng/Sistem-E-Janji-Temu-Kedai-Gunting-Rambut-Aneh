<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$pdo     = getDB();
$flash   = getFlash();
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Filter by date
$tarikhFilter = clean($_GET['tarikh'] ?? '');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verifyCsrf();
    $id     = (int)$_POST['jid'];
    $status = clean($_POST['status']);
    $allowed = ['Menunggu','Selesai','Tidak Selesai'];
    if (in_array($status, $allowed)) {
        $pdo->prepare("UPDATE JANJITEMU SET status=? WHERE id=?")->execute([$status,$id]);
        flashMessage('success','Status temu janji berjaya dikemaskini.');
    }
    header('Location: laporan.php' . ($tarikhFilter ? '?tarikh='.urlencode($tarikhFilter) : ''));
    exit;
}

// Count
$countSql  = "SELECT COUNT(*) FROM JANJITEMU j JOIN PELANGGAN c ON j.idPelanggan=c.idPelanggan";
$whereCond = '';
$params    = [];
if ($tarikhFilter) { $whereCond = " WHERE j.tarikh = ?"; $params[] = $tarikhFilter; }
$total      = (int)$pdo->prepare($countSql.$whereCond)->execute($params) ? $pdo->prepare($countSql.$whereCond)->execute($params) : 0;
$stTotal    = $pdo->prepare($countSql.$whereCond);
$stTotal->execute($params);
$totalRows  = (int)$stTotal->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$sql = "
  SELECT j.id, j.tarikh, j.masa, j.catatan, j.status,
         c.namaPelanggan, c.noTel, p.namaPerkhidmatan
  FROM JANJITEMU j
  JOIN PELANGGAN c ON j.idPelanggan = c.idPelanggan
  JOIN PERKHIDMATAN p ON j.idPerkhidmatan = p.idPerkhidmatan
  $whereCond
  ORDER BY j.tarikh ASC, j.masa ASC
  LIMIT ? OFFSET ?
";
$paramsFull = array_merge($params, [$perPage, $offset]);
$stmt = $pdo->prepare($sql);
$stmt->execute($paramsFull);
$senarai = $stmt->fetchAll();

// Summary counts
$sumStmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM JANJITEMU GROUP BY status");
$summary = [];
foreach ($sumStmt->fetchAll() as $row) { $summary[$row['status']] = $row['cnt']; }

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
  <title>Laporan Admin – Kedai Gunting Rambut Aneh</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="../index.php" class="navbar-brand">Kedai Gunting<br>Rambut Aneh</a>
  <ul class="navbar-nav">
    <li><a href="../index.php"    class="nav-link">Utama</a></li>
    <li><a href="laporan.php"     class="nav-link active">Laporan</a></li>
    <li><a href="../log-keluar.php" class="nav-link">Log Keluar</a></li>
  </ul>
</nav>

<div class="container section">
  <div class="flex-between mb-3">
    <h2>Laporan Admin</h2>
    <span style="font-size:.8rem;color:var(--text-light);">Selamat datang, Admin</span>
  </div>

  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
    <?= clean($flash['msg']) ?>
  </div>
  <?php endif; ?>

  <!-- Summary cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:2rem;">
    <div class="card" style="text-align:center;">
      <div class="card-body" style="padding:1.2rem;">
        <div style="font-size:1.8rem;font-weight:600;color:var(--espresso);"><?= $totalRows ?></div>
        <div style="font-size:.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;">Jumlah</div>
      </div>
    </div>
    <div class="card" style="text-align:center;">
      <div class="card-body" style="padding:1.2rem;">
        <div style="font-size:1.8rem;font-weight:600;color:#155724;"><?= $summary['Selesai'] ?? 0 ?></div>
        <div style="font-size:.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;">Selesai</div>
      </div>
    </div>
    <div class="card" style="text-align:center;">
      <div class="card-body" style="padding:1.2rem;">
        <div style="font-size:1.8rem;font-weight:600;color:#856404;"><?= $summary['Menunggu'] ?? 0 ?></div>
        <div style="font-size:.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;">Menunggu</div>
      </div>
    </div>
    <div class="card" style="text-align:center;">
      <div class="card-body" style="padding:1.2rem;">
        <div style="font-size:1.8rem;font-weight:600;color:#721c24;"><?= $summary['Tidak Selesai'] ?? 0 ?></div>
        <div style="font-size:.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;">Tidak Selesai</div>
      </div>
    </div>
  </div>

  <!-- Filter -->
  <div class="card mb-3">
    <div class="card-body" style="padding:1rem 1.5rem;">
      <form method="GET" action="laporan.php" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
        <div>
          <label class="form-label">Tapis Mengikut Tarikh</label>
          <input class="form-control" type="date" name="tarikh" value="<?= clean($tarikhFilter) ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Tapis</button>
        <?php if ($tarikhFilter): ?>
          <a href="laporan.php" class="btn btn-outline btn-sm">Padam Tapis</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <h3 class="mb-2">Senarai Temu Janji</h3>

  <?php if (empty($senarai)): ?>
    <div class="card">
      <div class="card-body text-center" style="padding:2rem;">
        <p style="color:var(--text-light);">Tiada rekod temu janji ditemui.</p>
      </div>
    </div>
  <?php else: ?>

  <!-- Modal for status update -->
  <div id="statusModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--white);border-radius:10px;padding:2rem;min-width:300px;box-shadow:var(--shadow-lg);">
      <h3 class="mb-2">Kemaskini Status</h3>
      <form method="POST" action="laporan.php<?= $tarikhFilter ? '?tarikh='.urlencode($tarikhFilter) : '' ?>" id="statusForm">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="jid" id="modalJid">
        <div class="form-group">
          <label class="form-label">Status Baru</label>
          <select class="form-control" name="status" id="modalStatus">
            <option value="Menunggu">Menunggu</option>
            <option value="Selesai">Selesai</option>
            <option value="Tidak Selesai">Tidak Selesai</option>
          </select>
        </div>
        <div style="display:flex;gap:1rem;">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Tarikh</th>
          <th>Slot Masa</th>
          <th>Nama</th>
          <th>No. Telefon</th>
          <th>Perkhidmatan</th>
          <th>Catatan</th>
          <th>Status</th>
          <th>Tindakan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($senarai as $row): ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($row['tarikh'])) ?></td>
          <td><?= clean($row['masa']) ?></td>
          <td><?= clean($row['namaPelanggan']) ?></td>
          <td><?= clean($row['noTel']) ?></td>
          <td><?= clean($row['namaPerkhidmatan']) ?></td>
          <td><?= $row['catatan'] ? clean($row['catatan']) : '<span style="color:var(--text-light)">-</span>' ?></td>
          <td><?= statusBadge($row['status']) ?></td>
          <td>
            <button class="btn btn-sm btn-outline" onclick="openModal(<?= $row['id'] ?>, '<?= addslashes($row['status']) ?>')">
              Kemaskini
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page-1 ?><?= $tarikhFilter ? '&tarikh='.urlencode($tarikhFilter) : '' ?>" class="page-link">← Sebelum</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?><?= $tarikhFilter ? '&tarikh='.urlencode($tarikhFilter) : '' ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page+1 ?><?= $tarikhFilter ? '&tarikh='.urlencode($tarikhFilter) : '' ?>" class="page-link">Seterusnya →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>

<script>
function openModal(jid, status) {
  document.getElementById('modalJid').value    = jid;
  document.getElementById('modalStatus').value = status;
  const modal = document.getElementById('statusModal');
  modal.style.display = 'flex';
}
function closeModal() {
  document.getElementById('statusModal').style.display = 'none';
}
document.getElementById('statusModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

</body>
</html>
