<?php
// =========================================================
// File: admin_mahasiswa.php
// Admin: lihat seluruh mahasiswa + ubah nama mahasiswa
// (nama_lengkap disimpan di tabel pengguna)
// =========================================================
require_once "koneksi.php";
require_once "auth.php";
require_role('admin');

$msg = "";

// ---- UBAH NAMA ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pengguna'])) {
    $id_pengguna = (int)$_POST['id_pengguna'];
    $nama_baru   = trim($_POST['nama']);
    if ($nama_baru !== '') {
        $stmt = mysqli_prepare($conn, "UPDATE pengguna SET nama_lengkap = ? WHERE id_pengguna = ? AND role = 'mahasiswa'");
        mysqli_stmt_bind_param($stmt, "si", $nama_baru, $id_pengguna);
        mysqli_stmt_execute($stmt);
        $msg = "Nama mahasiswa berhasil diperbarui.";
    }
}

// ---- PENCARIAN ----
$keyword = trim($_GET['q'] ?? '');
$keywordSql = mysqli_real_escape_string($conn, $keyword);

$query = "
    SELECT m.npm, u.id_pengguna, u.nama_lengkap AS nama, p.nama_prodi
    FROM mahasiswa m
    JOIN pengguna u ON m.id_pengguna = u.id_pengguna
    JOIN program_studi p ON m.kode_prodi = p.kode_prodi
";
if ($keyword !== '') {
    $query .= " WHERE u.nama_lengkap LIKE '%$keywordSql%' OR m.npm LIKE '%$keywordSql%'";
}
$query .= " ORDER BY m.npm";
$list = mysqli_query($conn, $query);

function h($t) { return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Data Mahasiswa</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-emerald-700 text-white shadow">
  <div class="max-w-5xl mx-auto px-5 py-4 flex items-center justify-between">
    <div class="font-semibold">🎓 SIM Mahasiswa UMB — Admin</div>
    <a href="dashboard_admin.php" class="text-sm hover:text-emerald-200">&larr; Kembali</a>
  </div>
</nav>

<div class="max-w-5xl mx-auto px-5 py-10 space-y-6">

  <?php if ($msg): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-2"><?= h($msg) ?></div><?php endif; ?>

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <h1 class="text-2xl font-bold text-slate-800">Data Mahasiswa</h1>
    <form method="GET" class="flex gap-2">
      <input type="text" name="q" value="<?= h($keyword) ?>" placeholder="Cari NPM atau nama..."
        class="border border-slate-300 rounded-full px-4 py-2 text-sm w-64">
      <button class="bg-emerald-600 text-white px-5 py-2 rounded-full text-sm font-medium hover:bg-emerald-700">Cari</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl shadow overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-emerald-700 text-white">
        <tr>
          <th class="px-4 py-3">NPM</th>
          <th class="px-4 py-3">Nama</th>
          <th class="px-4 py-3">Program Studi</th>
          <th class="px-4 py-3 w-72">Ubah Nama</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (mysqli_num_rows($list) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($list)): ?>
            <tr class="hover:bg-emerald-50/50">
              <td class="px-4 py-3 font-medium"><?= h($row['npm']) ?></td>
              <td class="px-4 py-3"><?= h($row['nama']) ?></td>
              <td class="px-4 py-3"><?= h($row['nama_prodi']) ?></td>
              <td class="px-4 py-3">
                <form method="POST" class="flex gap-2">
                  <input type="hidden" name="id_pengguna" value="<?= h($row['id_pengguna']) ?>">
                  <input type="text" name="nama" value="<?= h($row['nama']) ?>" required
                    class="flex-1 border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                  <button type="submit" class="bg-emerald-600 text-white text-xs font-medium px-3 rounded-lg hover:bg-emerald-700">
                    Simpan
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Tidak ada data ditemukan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>
<?php mysqli_close($conn); ?>