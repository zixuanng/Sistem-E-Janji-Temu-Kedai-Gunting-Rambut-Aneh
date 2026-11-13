<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pdo           = getDB();
$idPelanggan   = $_SESSION['pelanggan_id'];
$errors        = [];
$success       = false;
$slots         = getTimeSlots();
$tarikhs       = '';
$bookedSlots   = [];

// Load services
$perkhidmatan  = $pdo->query("SELECT * FROM PERKHIDMATAN ORDER BY idPerkhidmatan")->fetchAll();

// Check if POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $tarikh          = clean($_POST['tarikh']          ?? '');
    $idPerkhidmatan  = clean($_POST['idPerkhidmatan']  ?? '');
    $masa            = clean($_POST['masa']            ?? '');
    $catatan         = clean($_POST['catatan']         ?? '');

    if (empty($tarikh))         $errors[] = 'Sila pilih tarikh.';
    if (empty($idPerkhidmatan)) $errors[] = 'Sila pilih perkhidmatan.';
    if (empty($masa))           $errors[] = 'Sila pilih slot masa.';

    if (empty($errors)) {
        // Check 1 appointment per customer per day rule
        $chkCust = $pdo->prepare("SELECT id FROM JANJITEMU WHERE idPelanggan = ? AND tarikh = ?");
        $chkCust->execute([$idPelanggan, $tarikh]);
        if ($chkCust->fetch()) {
            $errors[] = 'Anda telah membuat satu temu janji pada tarikh ini. Hanya satu slot temu janji dibenarkan setiap hari.';
        } else {
            // Check slot taken
            $chkSlot = $pdo->prepare("SELECT id FROM JANJITEMU WHERE tarikh = ? AND masa = ?");
            $chkSlot->execute([$tarikh, $masa]);
            if ($chkSlot->fetch()) {
                $errors[] = 'Slot masa ini telah ditempah. Sila pilih slot lain.';
            } else {
                $ins = $pdo->prepare("INSERT INTO JANJITEMU (idPelanggan,tarikh,idPerkhidmatan,masa,catatan,status) VALUES (?,?,?,?,?,'Menunggu')");
                $ins->execute([$idPelanggan, $tarikh, $idPerkhidmatan, $masa, $catatan]);
                flashMessage('success', 'Temu janji berjaya dibuat! Tarikh: '.$tarikh.' | Masa: '.$masa);
                header('Location: sejarah-temu-janji.php');
                exit;
            }
        }
    }
    $tarikhs = $tarikh;
    if (!empty($tarikh)) {
        $bookedSlots = getBookedSlots($pdo, $tarikh);
    }
}

// AJAX: return booked slots for a date
if (isset($_GET['get_slots']) && isset($_GET['tarikh'])) {
    $t = clean($_GET['tarikh']);
    $bs = getBookedSlots($pdo, $t);
    // Also check if this customer already has one on this date
    $chkCust = $pdo->prepare("SELECT masa FROM JANJITEMU WHERE idPelanggan = ? AND tarikh = ?");
    $chkCust->execute([$idPelanggan, $t]);
    $custSlot = $chkCust->fetchColumn();
    header('Content-Type: application/json');
    echo json_encode(['booked' => $bs, 'cust_slot' => $custSlot]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Temu Janji – Kedai Gunting Rambut Aneh</title>
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

  <div class="card">
    <div class="card-header flex-between">
      <h2>Buat Temu Janji</h2>
    </div>
    <div class="card-body">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): ?><?= $e ?><br><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="buat-temu-janji.php" id="bookingForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="masa" id="masaInput">

        <div class="form-group">
          <label class="form-label" for="tarikh">Tarikh</label>
          <input class="form-control" type="date" id="tarikh" name="tarikh"
                 min="<?= date('Y-m-d') ?>"
                 value="<?= clean($tarikhs) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="idPerkhidmatan">Perkhidmatan</label>
          <select class="form-control" id="idPerkhidmatan" name="idPerkhidmatan" required>
            <option value="">-- Pilih Perkhidmatan --</option>
            <?php foreach ($perkhidmatan as $p): ?>
              <option value="<?= clean($p['idPerkhidmatan']) ?>"
                <?= (($_POST['idPerkhidmatan'] ?? '') === $p['idPerkhidmatan']) ? 'selected' : '' ?>>
                <?= clean($p['namaPerkhidmatan']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Slot Masa</label>
          <div class="slots-grid" id="slotsGrid">
            <?php foreach ($slots as $slot): ?>
              <?php $taken = in_array($slot, $bookedSlots); ?>
              <button type="button"
                class="slot-btn <?= $taken ? 'taken' : '' ?>"
                data-time="<?= $slot ?>"
                <?= $taken ? 'disabled' : '' ?>>
                <?= $slot ?>
              </button>
            <?php endforeach; ?>
          </div>
          <div id="slotInfo" style="margin-top:.5rem;"></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="catatan">Catatan (Pilihan)</label>
          <textarea class="form-control" id="catatan" name="catatan"
                    placeholder="Sebarang permintaan khas atau nota tambahan..."><?= clean($_POST['catatan'] ?? '') ?></textarea>
        </div>

        <p class="form-hint mb-2">⚠ Satu slot temu janji bagi setiap pelanggan pada satu hari sahaja.</p>

        <div class="divider"></div>
        <button type="submit" class="btn btn-primary btn-block" id="submitBtn">Tempah</button>
      </form>

    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Kedai Gunting Rambut Aneh &mdash; Sistem E-Janji Temu
</footer>

<script>
const slotBtns  = document.querySelectorAll('.slot-btn');
const masaInput = document.getElementById('masaInput');
const tarikhEl  = document.getElementById('tarikh');
const slotsGrid = document.getElementById('slotsGrid');
const slotInfo  = document.getElementById('slotInfo');
const allSlots  = <?= json_encode($slots) ?>;

function resetSlots() {
  slotBtns.forEach(b => {
    b.classList.remove('selected','taken');
    b.disabled = false;
  });
  masaInput.value = '';
  slotInfo.innerHTML = '';
}

function markBooked(bookedArr, custSlot) {
  slotBtns.forEach(b => {
    const t = b.dataset.time;
    b.classList.remove('selected','taken');
    b.disabled = false;
    if (bookedArr.includes(t)) {
      b.classList.add('taken');
      b.disabled = true;
    }
  });
  if (custSlot) {
    slotInfo.innerHTML = '<span class="alert alert-info" style="display:inline-block;padding:.4rem .8rem;font-size:.78rem;">Anda telah ada temu janji pada jam <strong>'+custSlot+'</strong> pada tarikh ini.</span>';
  }
}

tarikhEl.addEventListener('change', function() {
  const t = this.value;
  if (!t) { resetSlots(); return; }
  fetch('buat-temu-janji.php?get_slots=1&tarikh=' + encodeURIComponent(t))
    .then(r => r.json())
    .then(d => markBooked(d.booked, d.cust_slot));
});

slotBtns.forEach(btn => {
  btn.addEventListener('click', function() {
    if (this.classList.contains('taken')) return;
    slotBtns.forEach(b => b.classList.remove('selected'));
    this.classList.add('selected');
    masaInput.value = this.dataset.time;
  });
});

// Pre-select if POST error
<?php if (!empty($tarikhs) && !empty($_POST['masa'])): ?>
document.querySelectorAll('.slot-btn').forEach(b => {
  if (b.dataset.time === '<?= clean($_POST['masa']) ?>') b.classList.add('selected');
});
masaInput.value = '<?= clean($_POST['masa']) ?>';
<?php endif; ?>
</script>

</body>
</html>
