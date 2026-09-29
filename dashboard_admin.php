<?php
// =========================================================
// File: dashboard_admin.php
// =========================================================
require_once "koneksi.php";
require_once "auth.php";
require_role('admin');

$u = current_user();

$totalMhs   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM mahasiswa"))['jml'];
$totalProdi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM program_studi"))['jml'];
$totalFak   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM fakultas"))['jml'];

function h($t) { return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-emerald-700 text-white shadow">
  <div class="max-w-5xl mx-auto px-5 py-4 flex items-center justify-between">
    <div class="font-semibold">🎓 SIM Mahasiswa UMB — Admin</div>
    <div class="flex items-center gap-4 text-sm">
      <span>Halo, <?= h($u['nama']) ?></span>
      <a href="logout.php" class="bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-full">Keluar</a>
    </div>
  </div>
</nav>

<div class="max-w-5xl mx-auto px-5 py-10 space-y-8">

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl shadow p-6 text-center border-t-4 border-emerald-600">
      <div class="text-3xl font-bold text-emerald-700"><?= h($totalMhs) ?></div>
      <div class="text-sm text-slate-500 mt-1">Total Mahasiswa</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6 text-center border-t-4 border-emerald-600">
      <div class="text-3xl font-bold text-emerald-700"><?= h($totalProdi) ?></div>
      <div class="text-sm text-slate-500 mt-1">Program Studi</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6 text-center border-t-4 border-emerald-600">
      <div class="text-3xl font-bold text-emerald-700"><?= h($totalFak) ?></div>
      <div class="text-sm text-slate-500 mt-1">Fakultas</div>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <a href="admin_fakultas.php" class="bg-white rounded-2xl shadow p-6 hover:shadow-md transition block">
      <h3 class="font-bold text-slate-800 mb-1">Kelola Fakultas</h3>
      <p class="text-sm text-slate-500">Tambah, ubah, dan hapus data fakultas.</p>
    </a>
    <a href="admin_prodi.php" class="bg-white rounded-2xl shadow p-6 hover:shadow-md transition block">
      <h3 class="font-bold text-slate-800 mb-1">Kelola Program Studi</h3>
      <p class="text-sm text-slate-500">Tambah, ubah, dan hapus data program studi.</p>
    </a>
    <a href="admin_mahasiswa.php" class="bg-white rounded-2xl shadow p-6 hover:shadow-md transition block">
      <h3 class="font-bold text-slate-800 mb-1">Kelola Data Mahasiswa</h3>
      <p class="text-sm text-slate-500">Lihat seluruh mahasiswa dan ubah nama mahasiswa.</p>
    </a>
  </div>

</div>

</body>
</html>
<?php mysqli_close($conn); ?>