<?php
// =========================================================
// File: dashboard_prodi.php
// Role prodi: lihat mahasiswa di prodinya + ubah nama prodi
// =========================================================
require_once "koneksi.php";
require_once "auth.php";
require_role('prodi');

$u = current_user();
$msg = "";

// Cari kode_prodi yang dikelola akun ini
$stmt = mysqli_prepare($conn, "
    SELECT p.kode_prodi, p.nama_prodi
    FROM pengelola_prodi pp
    JOIN program_studi p ON pp.kode_prodi = p.kode_prodi
    WHERE pp.id_pengguna = ?
");
mysqli_stmt_bind_param($stmt, "i", $u['id_pengguna']);
mysqli_stmt_execute($stmt);
$prodi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$prodi) {
    die("Akun ini belum dihubungkan ke program studi manapun.");
}

// Ubah nama prodi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_prodi'])) {
    $nama_baru = trim($_POST['nama_prodi']);
    if ($nama_baru !== '') {
        $stmt2 = mysqli_prepare($conn, "UPDATE program_studi SET nama_prodi = ? WHERE kode_prodi = ?");
        mysqli_stmt_bind_param($stmt2, "ss", $nama_baru, $prodi['kode_prodi']);
        mysqli_stmt_execute($stmt2);
        $prodi['nama_prodi'] = $nama_baru;
        $msg = "Nama program studi berhasil diperbarui.";
    }
}

// Daftar mahasiswa di prodi ini
$stmt3 = mysqli_prepare($conn, "
    SELECT m.npm, u.nama_lengkap AS nama, m.jenis_kelamin, m.tempat_lahir, m.tanggal_lahir, m.tanggal_masuk, m.alamat
    FROM mahasiswa m
    JOIN pengguna u ON m.id_pengguna = u.id_pengguna
    WHERE m.kode_prodi = ?
    ORDER BY m.npm
");
mysqli_stmt_bind_param($stmt3, "s", $prodi['kode_prodi']);
mysqli_stmt_execute($stmt3);
$mahasiswa = mysqli_stmt_get_result($stmt3);

function h($t) { return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Prodi</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-emerald-700 text-white shadow">
  <div class="max-w-5xl mx-auto px-5 py-4 flex items-center justify-between">
    <div class="font-semibold">🎓 SIM Mahasiswa UMB — Prodi</div>
    <div class="flex items-center gap-4 text-sm">
      <span>Halo, <?= h($u['nama']) ?></span>
      <a href="logout.php" class="bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-full">Keluar</a>
    </div>
  </div>
</nav>

<div class="max-w-5xl mx-auto px-5 py-10 space-y-8">

  <?php if ($msg): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-2">
      <?= h($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Ubah nama prodi -->
  <div class="bg-white rounded-2xl shadow p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-1">Program Studi: <?= h($prodi['kode_prodi']) ?></h2>
    <p class="text-sm text-slate-500 mb-4">Ubah nama program studi yang Anda kelola.</p>
    <form method="POST" class="flex flex-col sm:flex-row gap-3">
      <input type="text" name="nama_prodi" value="<?= h($prodi['nama_prodi']) ?>" required
        class="flex-1 border border-slate-300 rounded-lg px-4 py-2 text-sm">
      <button type="submit" class="bg-emerald-700 text-white font-medium px-5 py-2.5 rounded-lg hover:bg-emerald-800">
        Simpan
      </button>
    </form>
  </div>

  <!-- Daftar mahasiswa -->
  <div>
    <h2 class="text-lg font-bold text-slate-800 mb-3">Mahasiswa Program Studi Ini</h2>
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-emerald-700 text-white">
          <tr>
            <th class="px-4 py-3">NPM</th>
            <th class="px-4 py-3">Nama</th>
            <th class="px-4 py-3">JK</th>
            <th class="px-4 py-3">Tempat, Tgl Lahir</th>
            <th class="px-4 py-3">Tanggal Masuk</th>
            <th class="px-4 py-3">Alamat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (mysqli_num_rows($mahasiswa) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($mahasiswa)): ?>
              <tr class="hover:bg-emerald-50/50">
                <td class="px-4 py-3 font-medium"><?= h($row['npm']) ?></td>
                <td class="px-4 py-3"><?= h($row['nama']) ?></td>
                <td class="px-4 py-3"><?= $row['jenis_kelamin'] === 'L' ? 'L' : 'P' ?></td>
                <td class="px-4 py-3"><?= h($row['tempat_lahir']) ?>, <?= h(date('d-m-Y', strtotime($row['tanggal_lahir']))) ?></td>
                <td class="px-4 py-3"><?= h(date('d-m-Y', strtotime($row['tanggal_masuk']))) ?></td>
                <td class="px-4 py-3"><?= h($row['alamat']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada mahasiswa.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

</body>
</html>
<?php mysqli_close($conn); ?>