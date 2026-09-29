<?php
// =========================================================
// File: dashboard_mahasiswa.php
// Dashboard mahasiswa (Bootstrap 5)
// =========================================================
require_once "koneksi.php";
require_once "auth.php";
require_role('mahasiswa');
mysqli_report(MYSQLI_REPORT_OFF);

$u = current_user();

$st = mysqli_prepare($conn, "
    SELECT m.npm, u.nama_lengkap AS nama, u.username, u.email, m.jenis_kelamin, m.tempat_lahir,
           m.tanggal_lahir, m.tanggal_masuk, m.alamat, p.kode_prodi, p.nama_prodi, f.nama_fakultas
    FROM mahasiswa m
    JOIN pengguna u ON m.id_pengguna = u.id_pengguna
    JOIN program_studi p ON m.kode_prodi = p.kode_prodi
    JOIN fakultas f ON p.kode_fakultas = f.kode_fakultas
    WHERE m.id_pengguna = ?
");
mysqli_stmt_bind_param($st, "i", $u['id_pengguna']);
mysqli_stmt_execute($st);
$d = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (!$d) { die("Data mahasiswa untuk akun ini tidak ditemukan."); }

// Jumlah mahasiswa satu prodi
$st2 = mysqli_prepare($conn, "SELECT COUNT(*) AS jml FROM mahasiswa WHERE kode_prodi = ?");
mysqli_stmt_bind_param($st2, "s", $d['kode_prodi']);
mysqli_stmt_execute($st2);
$jmlProdi = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($st2))['jml'];

function h($t) { return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8'); }
function tgl_indo($tgl) {
    if (empty($tgl) || $tgl === '0000-00-00') return '-';
    $bln = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $p = explode('-', $tgl);
    return (int)$p[2] . ' ' . $bln[(int)$p[1]] . ' ' . $p[0];
}

// Perhitungan akademik
$today = new DateTime('today');
$semester = '-'; $lamaStudi = '-'; $umur = '-';
if (!empty($d['tanggal_masuk'])) {
    $diff = (new DateTime($d['tanggal_masuk']))->diff($today);
    $bulan = $diff->y * 12 + $diff->m;
    $semester = max(1, intdiv($bulan, 6) + 1);
    $lamaStudi = $diff->y . ' th ' . $diff->m . ' bln';
}
if (!empty($d['tanggal_lahir'])) {
    $umur = (new DateTime($d['tanggal_lahir']))->diff($today)->y . ' tahun';
}

// Inisial untuk avatar
$kata = preg_split('/\s+/', trim($d['nama']));
$inisial = strtoupper(mb_substr($kata[0], 0, 1) . (isset($kata[1]) ? mb_substr($kata[1], 0, 1) : ''));
$jk = $d['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Mahasiswa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    body { font-family:'Plus Jakarta Sans',sans-serif; background:#f8fafc; color:#0f172a; min-height:100vh; }
    .navbar-custom { background:rgba(255,255,255,.85); backdrop-filter:blur(12px); border-bottom:1px solid #e2e8f0; position:sticky; top:0; z-index:1030; }
    .hero { background:linear-gradient(135deg,#4f46e5 0%,#3b82f6 100%); color:#fff; border-radius:18px; padding:1.75rem; }
    .avatar { width:76px; height:76px; border-radius:50%; background:rgba(255,255,255,.2); border:2px solid rgba(255,255,255,.5); display:flex; align-items:center; justify-content:center; font-size:1.7rem; font-weight:800; flex-shrink:0; }
    .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:1.1rem 1.25rem; height:100%; transition:all .25s; }
    .stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 20px -5px rgba(0,0,0,.06); }
    .icon-shape { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.35rem; }
    .info-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 4px 15px rgba(0,0,0,.03); height:100%; }
    .info-card h6 { padding:1rem 1.25rem; margin:0; border-bottom:1px solid #e2e8f0; font-weight:700; }
    .info-row { display:flex; justify-content:space-between; gap:1rem; padding:.8rem 1.25rem; border-bottom:1px solid #f1f5f9; }
    .info-row:last-child { border-bottom:0; }
    .info-row .lbl { color:#64748b; font-size:.88rem; }
    .info-row .val { font-weight:600; text-align:right; }
    .btn-logout { background:#fee2e2; color:#dc2626; font-weight:600; border:none; border-radius:50rem; padding:.4rem 1.1rem; }
    .btn-logout:hover { background:#fca5a5; color:#991b1b; }
</style>
</head>
<body>

<nav class="navbar navbar-custom py-3">
  <div class="container px-lg-4 d-flex justify-content-between">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-5" href="dashboard_mahasiswa.php">
      <span class="bg-primary text-white rounded-3 p-1 px-2"><i class="bi bi-mortarboard-fill"></i></span>
      SIM Mahasiswa
    </a>
    <div class="d-flex align-items-center gap-2">
      <a href="edit_mahasiswa.php" class="btn btn-sm btn-outline-primary rounded-pill d-none d-sm-inline-block"><i class="bi bi-pencil me-1"></i>Ubah Data</a>
      <a href="logout.php" class="btn btn-logout btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
    </div>
  </div>
</nav>

<div class="container px-lg-4 py-4">

  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success py-2"><i class="bi bi-check-circle me-1"></i>Perubahan berhasil disimpan.</div>
  <?php endif; ?>

  <!-- Profil -->
  <div class="hero d-flex flex-column flex-md-row align-items-md-center gap-3 mb-4">
    <div class="avatar"><?= h($inisial) ?></div>
    <div class="flex-grow-1">
      <div class="small opacity-75">Selamat datang,</div>
      <h3 class="fw-bold mb-1"><?= h($d['nama']) ?></h3>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <span class="badge bg-light text-dark font-monospace px-3 py-2"><?= h($d['npm']) ?></span>
        <span class="badge px-3 py-2" style="background:rgba(255,255,255,.2)"><i class="bi bi-book me-1"></i><?= h($d['nama_prodi']) ?></span>
        <span class="badge px-3 py-2" style="background:rgba(255,255,255,.2)"><i class="bi bi-building me-1"></i><?= h($d['nama_fakultas']) ?></span>
      </div>
    </div>
    <a href="edit_mahasiswa.php" class="btn btn-light rounded-pill fw-semibold px-4"><i class="bi bi-pencil-square me-1"></i>Ubah Data</a>
  </div>

  <!-- Statistik -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card d-flex align-items-center justify-content-between">
      <div><span class="text-muted small fw-semibold">Semester</span><h3 class="fw-bold mb-0 mt-1"><?= h($semester) ?></h3></div>
      <div class="icon-shape bg-primary bg-opacity-10 text-primary"><i class="bi bi-journal-bookmark-fill"></i></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="stat-card d-flex align-items-center justify-content-between">
      <div><span class="text-muted small fw-semibold">Lama Studi</span><h5 class="fw-bold mb-0 mt-1"><?= h($lamaStudi) ?></h5></div>
      <div class="icon-shape bg-success bg-opacity-10 text-success"><i class="bi bi-hourglass-split"></i></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="stat-card d-flex align-items-center justify-content-between">
      <div><span class="text-muted small fw-semibold">Umur</span><h5 class="fw-bold mb-0 mt-1"><?= h($umur) ?></h5></div>
      <div class="icon-shape bg-warning bg-opacity-10 text-warning"><i class="bi bi-cake2-fill"></i></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="stat-card d-flex align-items-center justify-content-between">
      <div><span class="text-muted small fw-semibold">Mahasiswa Prodi</span><h3 class="fw-bold mb-0 mt-1"><?= $jmlProdi ?></h3></div>
      <div class="icon-shape bg-info bg-opacity-10 text-info"><i class="bi bi-people-fill"></i></div>
    </div></div>
  </div>

  <!-- Detail -->
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="info-card">
        <h6><i class="bi bi-person-vcard text-primary me-2"></i>Data Pribadi</h6>
        <div class="info-row"><span class="lbl">Nama Lengkap</span><span class="val"><?= h($d['nama']) ?></span></div>
        <div class="info-row"><span class="lbl">Jenis Kelamin</span><span class="val"><?= $jk ?></span></div>
        <div class="info-row"><span class="lbl">Tempat Lahir</span><span class="val"><?= h($d['tempat_lahir']) ?></span></div>
        <div class="info-row"><span class="lbl">Tanggal Lahir</span><span class="val"><?= tgl_indo($d['tanggal_lahir']) ?></span></div>
        <div class="info-row"><span class="lbl">Alamat</span><span class="val"><?= h($d['alamat']) ?></span></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="info-card">
        <h6><i class="bi bi-bank text-primary me-2"></i>Data Akademik</h6>
        <div class="info-row"><span class="lbl">NPM</span><span class="val font-monospace"><?= h($d['npm']) ?></span></div>
        <div class="info-row"><span class="lbl">Program Studi</span><span class="val"><?= h($d['nama_prodi']) ?></span></div>
        <div class="info-row"><span class="lbl">Fakultas</span><span class="val"><?= h($d['nama_fakultas']) ?></span></div>
        <div class="info-row"><span class="lbl">Tanggal Masuk</span><span class="val"><?= tgl_indo($d['tanggal_masuk']) ?></span></div>
        <div class="info-row"><span class="lbl">Semester Berjalan</span><span class="val"><?= h($semester) ?></span></div>
      </div>
    </div>
    <div class="col-12">
      <div class="info-card">
        <h6><i class="bi bi-shield-lock text-primary me-2"></i>Akun</h6>
        <div class="info-row"><span class="lbl">Username</span><span class="val font-monospace"><?= h($d['username']) ?></span></div>
        <div class="info-row"><span class="lbl">Email</span><span class="val"><?= $d['email'] ? h($d['email']) : '-' ?></span></div>
        <div class="info-row"><span class="lbl">Password</span><span class="val"><a href="edit_mahasiswa.php#password" class="text-decoration-none">Ganti password</a></span></div>
      </div>
    </div>
  </div>

</div>
</body>
</html>
<?php mysqli_close($conn); ?>