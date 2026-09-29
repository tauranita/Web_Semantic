<?php
// =========================================================
// File: admin_prodi.php
// CRUD Program Studi (PK: kode_prodi, FK: kode_fakultas)
// =========================================================
require_once "koneksi.php";
require_once "auth.php";
require_once "admin_layout.php";
require_role('admin');

if (!isset($conn) && isset($koneksi)) { $conn = $koneksi; }
mysqli_report(MYSQLI_REPORT_OFF); // error ditangani manual lewat errno

$msg = ""; $error = "";

// Ambil semua baris; kalau query gagal, tampilkan penyebabnya (bukan error 500)
function ambil($conn, $sql, &$error) {
    $q = mysqli_query($conn, $sql);
    if (!$q) { $error = "Query gagal: " . mysqli_error($conn); return []; }
    return mysqli_fetch_all($q, MYSQLI_ASSOC);
}

$fakultas = ambil($conn, "SELECT kode_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas", $error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $kode = strtoupper(trim($_POST['kode_prodi'] ?? ''));
    $nama = trim($_POST['nama_prodi'] ?? '');
    $kf   = trim($_POST['kode_fakultas'] ?? '');

    if ($aksi === 'tambah') {
        if ($kode === '' || $nama === '' || $kf === '') {
            $error = "Kode, nama, dan fakultas wajib diisi.";
        } else {
            $st = mysqli_prepare($conn, "INSERT INTO program_studi (kode_prodi, nama_prodi, kode_fakultas) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($st, "sss", $kode, $nama, $kf);
            if (mysqli_stmt_execute($st)) $msg = "Program studi berhasil ditambahkan.";
            else $error = (mysqli_errno($conn) == 1062) ? "Kode prodi '$kode' sudah dipakai." : "Gagal menambah program studi: " . mysqli_error($conn);
        }
    } elseif ($aksi === 'ubah') {
        $lama = $_POST['kode_lama'] ?? '';
        if ($kode === '' || $nama === '' || $kf === '' || $lama === '') {
            $error = "Kode, nama, dan fakultas wajib diisi.";
        } else {
            $st = mysqli_prepare($conn, "UPDATE program_studi SET kode_prodi=?, nama_prodi=?, kode_fakultas=? WHERE kode_prodi=?");
            mysqli_stmt_bind_param($st, "ssss", $kode, $nama, $kf, $lama);
            if (mysqli_stmt_execute($st)) $msg = "Program studi berhasil diperbarui.";
            else $error = (mysqli_errno($conn) == 1062) ? "Kode prodi '$kode' sudah dipakai." : "Gagal memperbarui program studi: " . mysqli_error($conn);
        }
    } elseif ($aksi === 'hapus') {
        $st = mysqli_prepare($conn, "DELETE FROM program_studi WHERE kode_prodi=?");
        mysqli_stmt_bind_param($st, "s", $kode);
        if (mysqli_stmt_execute($st)) $msg = "Program studi berhasil dihapus.";
        else $error = (mysqli_errno($conn) == 1451)
            ? "Tidak bisa dihapus: masih ada mahasiswa yang terdaftar di program studi ini."
            : "Gagal menghapus program studi: " . mysqli_error($conn);
    }
}

// Filter fakultas (opsional)
$filter = trim($_GET['fakultas'] ?? '');
$sql = "
    SELECT p.kode_prodi, p.nama_prodi, p.kode_fakultas, f.nama_fakultas,
           (SELECT COUNT(*) FROM mahasiswa m WHERE m.kode_prodi = p.kode_prodi) AS jml_mhs
    FROM program_studi p
    JOIN fakultas f ON p.kode_fakultas = f.kode_fakultas
";
if ($filter !== '') {
    $st = mysqli_prepare($conn, $sql . " WHERE p.kode_fakultas = ? ORDER BY p.nama_prodi");
    mysqli_stmt_bind_param($st, "s", $filter);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
} else {
    $rows = ambil($conn, $sql . " ORDER BY f.nama_fakultas, p.nama_prodi", $error);
}

function opsi_fakultas($fakultas, $terpilih = '') {
    foreach ($fakultas as $f) {
        $sel = ($f['kode_fakultas'] === $terpilih) ? 'selected' : '';
        echo '<option value="' . h($f['kode_fakultas']) . '" ' . $sel . '>' . h($f['nama_fakultas']) . '</option>';
    }
}

layout_start("Kelola Program Studi", "prodi");
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Data Program Studi</h3>
        <p class="text-muted small mb-0">Tambah, ubah, dan hapus program studi.</p>
    </div>
    <div class="d-flex gap-2">
        <form method="GET">
            <select name="fakultas" class="form-select rounded-pill" onchange="this.form.submit()">
                <option value="">Semua Fakultas</option>
                <?php opsi_fakultas($fakultas, $filter); ?>
            </select>
        </form>
        <button class="btn btn-primary rounded-pill px-4 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalTambah" <?= $fakultas ? '' : 'disabled' ?>>
            <i class="bi bi-plus-lg me-1"></i> Tambah Prodi
        </button>
    </div>
</div>

<?php if (!$fakultas): ?>
    <div class="alert alert-warning py-2">Belum ada fakultas. Tambahkan fakultas dulu di menu <a href="admin_fakultas.php">Fakultas</a>.</div>
<?php endif; ?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= h($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger py-2"><?= h($error) ?></div><?php endif; ?>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:6%" class="text-center">No</th>
                    <th>Kode</th>
                    <th>Nama Program Studi</th>
                    <th>Fakultas</th>
                    <th class="text-center">Mahasiswa</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows): $no = 1; foreach ($rows as $r): ?>
                <tr>
                    <td class="text-center text-muted"><?= $no++ ?></td>
                    <td><span class="badge bg-light text-dark border font-monospace"><?= h($r['kode_prodi']) ?></span></td>
                    <td class="fw-semibold"><?= h($r['nama_prodi']) ?></td>
                    <td><?= h($r['nama_fakultas']) ?></td>
                    <td class="text-center"><?= (int)$r['jml_mhs'] ?></td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-outline-primary rounded-pill btn-edit"
                                data-kode="<?= h($r['kode_prodi']) ?>" data-nama="<?= h($r['nama_prodi']) ?>" data-kf="<?= h($r['kode_fakultas']) ?>"
                                data-bs-toggle="modal" data-bs-target="#modalEdit">
                            <i class="bi bi-pencil"></i> Ubah
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill btn-hapus"
                                data-kode="<?= h($r['kode_prodi']) ?>" data-nama="<?= h($r['nama_prodi']) ?>"
                                data-bs-toggle="modal" data-bs-target="#modalHapus">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data program studi.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Program Studi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="aksi" value="tambah">
      <div class="mb-3"><label class="form-label fw-semibold">Kode Prodi</label>
        <input type="text" name="kode_prodi" class="form-control" maxlength="10" required placeholder="mis. TI"></div>
      <div class="mb-3"><label class="form-label fw-semibold">Nama Program Studi</label>
        <input type="text" name="nama_prodi" class="form-control" maxlength="100" required placeholder="mis. Teknik Informatika"></div>
      <div class="mb-1"><label class="form-label fw-semibold">Fakultas</label>
        <select name="kode_fakultas" class="form-select" required><?php opsi_fakultas($fakultas); ?></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
  </form></div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Ubah Program Studi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="aksi" value="ubah">
      <input type="hidden" name="kode_lama" id="editKodeLama">
      <div class="mb-3"><label class="form-label fw-semibold">Kode Prodi</label>
        <input type="text" name="kode_prodi" id="editKode" class="form-control" maxlength="10" required></div>
      <div class="mb-3"><label class="form-label fw-semibold">Nama Program Studi</label>
        <input type="text" name="nama_prodi" id="editNama" class="form-control" maxlength="100" required></div>
      <div class="mb-1"><label class="form-label fw-semibold">Fakultas</label>
        <select name="kode_fakultas" id="editKf" class="form-select" required><?php opsi_fakultas($fakultas); ?></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Perubahan</button></div>
  </form></div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Hapus Program Studi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="aksi" value="hapus">
      <input type="hidden" name="kode_prodi" id="hapusKode">
      Yakin ingin menghapus <strong id="hapusNama"></strong>?
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Ya, Hapus</button></div>
  </form></div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', () => {
    document.getElementById('editKodeLama').value = b.dataset.kode;
    document.getElementById('editKode').value = b.dataset.kode;
    document.getElementById('editNama').value = b.dataset.nama;
    document.getElementById('editKf').value = b.dataset.kf;
}));
document.querySelectorAll('.btn-hapus').forEach(b => b.addEventListener('click', () => {
    document.getElementById('hapusKode').value = b.dataset.kode;
    document.getElementById('hapusNama').textContent = b.dataset.nama;
}));
</script>
<?php layout_end(); mysqli_close($conn); ?>