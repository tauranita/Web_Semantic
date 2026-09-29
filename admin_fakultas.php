<?php
// =========================================================
// File: admin_fakultas.php
// CRUD Fakultas (PK: kode_fakultas)
// =========================================================
require_once "koneksi.php";
require_once "auth.php";
require_once "admin_layout.php";
require_role('admin');

if (!isset($conn) && isset($koneksi)) { $conn = $koneksi; }
mysqli_report(MYSQLI_REPORT_OFF); // error ditangani manual lewat errno

$msg = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $kode = strtoupper(trim($_POST['kode_fakultas'] ?? ''));
    $nama = trim($_POST['nama_fakultas'] ?? '');

    if ($aksi === 'tambah') {
        if ($kode === '' || $nama === '') {
            $error = "Kode dan nama fakultas wajib diisi.";
        } else {
            $st = mysqli_prepare($conn, "INSERT INTO fakultas (kode_fakultas, nama_fakultas) VALUES (?, ?)");
            mysqli_stmt_bind_param($st, "ss", $kode, $nama);
            if (mysqli_stmt_execute($st)) $msg = "Fakultas berhasil ditambahkan.";
            else $error = (mysqli_errno($conn) == 1062) ? "Kode fakultas '$kode' sudah dipakai." : "Gagal menambah fakultas: " . mysqli_error($conn);
        }
    } elseif ($aksi === 'ubah') {
        $lama = $_POST['kode_lama'] ?? '';
        if ($kode === '' || $nama === '' || $lama === '') {
            $error = "Kode dan nama fakultas wajib diisi.";
        } else {
            $st = mysqli_prepare($conn, "UPDATE fakultas SET kode_fakultas=?, nama_fakultas=? WHERE kode_fakultas=?");
            mysqli_stmt_bind_param($st, "sss", $kode, $nama, $lama);
            if (mysqli_stmt_execute($st)) $msg = "Fakultas berhasil diperbarui.";
            else $error = (mysqli_errno($conn) == 1062) ? "Kode fakultas '$kode' sudah dipakai." : "Gagal memperbarui fakultas: " . mysqli_error($conn);
        }
    } elseif ($aksi === 'hapus') {
        $st = mysqli_prepare($conn, "DELETE FROM fakultas WHERE kode_fakultas=?");
        mysqli_stmt_bind_param($st, "s", $kode);
        if (mysqli_stmt_execute($st)) $msg = "Fakultas berhasil dihapus.";
        else $error = (mysqli_errno($conn) == 1451)
            ? "Tidak bisa dihapus: fakultas ini masih memiliki program studi. Hapus/pindahkan prodinya dulu."
            : "Gagal menghapus fakultas: " . mysqli_error($conn);
    }
}

$list = mysqli_query($conn, "
    SELECT f.kode_fakultas, f.nama_fakultas, COUNT(p.kode_prodi) AS jml_prodi
    FROM fakultas f
    LEFT JOIN program_studi p ON p.kode_fakultas = f.kode_fakultas
    GROUP BY f.kode_fakultas, f.nama_fakultas
    ORDER BY f.nama_fakultas
");
$rows = mysqli_fetch_all($list, MYSQLI_ASSOC);

layout_start("Kelola Fakultas", "fakultas");
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Data Fakultas</h3>
        <p class="text-muted small mb-0">Tambah, ubah, dan hapus fakultas.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i> Tambah Fakultas
    </button>
</div>

<?php if ($msg): ?><div class="alert alert-success py-2"><?= h($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger py-2"><?= h($error) ?></div><?php endif; ?>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:6%" class="text-center">No</th>
                    <th>Kode</th>
                    <th>Nama Fakultas</th>
                    <th class="text-center">Jumlah Prodi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows): $no = 1; foreach ($rows as $r): ?>
                <tr>
                    <td class="text-center text-muted"><?= $no++ ?></td>
                    <td><span class="badge bg-light text-dark border font-monospace"><?= h($r['kode_fakultas']) ?></span></td>
                    <td class="fw-semibold"><?= h($r['nama_fakultas']) ?></td>
                    <td class="text-center"><?= (int)$r['jml_prodi'] ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary rounded-pill btn-edit"
                                data-kode="<?= h($r['kode_fakultas']) ?>" data-nama="<?= h($r['nama_fakultas']) ?>"
                                data-bs-toggle="modal" data-bs-target="#modalEdit">
                            <i class="bi bi-pencil"></i> Ubah
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill btn-hapus"
                                data-kode="<?= h($r['kode_fakultas']) ?>" data-nama="<?= h($r['nama_fakultas']) ?>"
                                data-bs-toggle="modal" data-bs-target="#modalHapus">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data fakultas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Fakultas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="aksi" value="tambah">
      <div class="mb-3"><label class="form-label fw-semibold">Kode Fakultas</label>
        <input type="text" name="kode_fakultas" class="form-control" maxlength="10" required placeholder="mis. FT"></div>
      <div class="mb-1"><label class="form-label fw-semibold">Nama Fakultas</label>
        <input type="text" name="nama_fakultas" class="form-control" maxlength="100" required placeholder="mis. Fakultas Teknik"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
  </form></div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Ubah Fakultas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="aksi" value="ubah">
      <input type="hidden" name="kode_lama" id="editKodeLama">
      <div class="mb-3"><label class="form-label fw-semibold">Kode Fakultas</label>
        <input type="text" name="kode_fakultas" id="editKode" class="form-control" maxlength="10" required></div>
      <div class="mb-1"><label class="form-label fw-semibold">Nama Fakultas</label>
        <input type="text" name="nama_fakultas" id="editNama" class="form-control" maxlength="100" required></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Perubahan</button></div>
  </form></div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Hapus Fakultas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="aksi" value="hapus">
      <input type="hidden" name="kode_fakultas" id="hapusKode">
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
}));
document.querySelectorAll('.btn-hapus').forEach(b => b.addEventListener('click', () => {
    document.getElementById('hapusKode').value = b.dataset.kode;
    document.getElementById('hapusNama').textContent = b.dataset.nama;
}));
</script>
<?php layout_end(); mysqli_close($conn); ?>