<?php
// =========================================================
// File: index.php
// Landing Page publik - Sistem Data Mahasiswa
// Universitas Muhammadiyah Bengkulu
// =========================================================
require_once "koneksi.php";

// Hitung baris tabel; kalau tabel belum ada, tampil 0 (tidak error 500)
function hitung($conn, $tabel) {
    try {
        $r = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `$tabel`");
        return $r ? (int)mysqli_fetch_assoc($r)['jml'] : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

$totalMahasiswa = hitung($conn, 'mahasiswa');
$totalProdi     = hitung($conn, 'program_studi');
$totalFakultas  = hitung($conn, 'fakultas');
mysqli_close($conn);

function h($t) { return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Data Mahasiswa | Universitas Muhammadiyah Bengkulu</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="bg-slate-50 text-slate-800">

<nav class="bg-emerald-700 text-white sticky top-0 z-30 shadow">
  <div class="max-w-6xl mx-auto px-5 py-4 flex items-center justify-between">
    <div class="flex items-center gap-2 font-semibold text-lg">
      <span class="bg-white text-emerald-700 rounded-lg w-9 h-9 flex items-center justify-center">🎓</span>
      SIM Mahasiswa UMB
    </div>
    <a href="login.php" class="bg-white text-emerald-700 text-sm font-semibold px-5 py-2 rounded-full hover:bg-emerald-50">
      Masuk
    </a>
  </div>
</nav>

<header class="bg-gradient-to-br from-emerald-700 to-emerald-500 text-white">
  <div class="max-w-6xl mx-auto px-5 py-16 text-center">
    <h1 class="text-3xl md:text-4xl font-bold mb-3">Sistem Informasi Data Mahasiswa</h1>
    <p class="text-emerald-50 max-w-2xl mx-auto">
      Universitas Muhammadiyah Bengkulu — Fakultas Teknik, Program Studi Teknik Informatika.
      Masuk sesuai peran Anda (mahasiswa, program studi, atau admin) untuk mengakses data.
    </p>
    <a href="login.php" class="inline-block mt-6 bg-white text-emerald-700 font-semibold px-6 py-2.5 rounded-full hover:bg-emerald-50 transition">
      Masuk ke Sistem
    </a>
  </div>
</header>

<section class="max-w-6xl mx-auto px-5 -mt-10 relative z-10 mb-16">
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl shadow p-6 text-center border-t-4 border-emerald-600">
      <div class="text-3xl font-bold text-emerald-700"><?= h($totalMahasiswa) ?></div>
      <div class="text-sm text-slate-500 mt-1">Total Mahasiswa</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6 text-center border-t-4 border-emerald-600">
      <div class="text-3xl font-bold text-emerald-700"><?= h($totalProdi) ?></div>
      <div class="text-sm text-slate-500 mt-1">Program Studi</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6 text-center border-t-4 border-emerald-600">
      <div class="text-3xl font-bold text-emerald-700"><?= h($totalFakultas) ?></div>
      <div class="text-sm text-slate-500 mt-1">Fakultas</div>
    </div>
  </div>
</section>

<footer class="bg-slate-900 text-slate-300 py-8">
  <div class="max-w-6xl mx-auto px-5 text-center text-sm">
    &copy; <?= date('Y') ?> Sistem Informasi Data Mahasiswa — Universitas Muhammadiyah Bengkulu.
  </div>
</footer>

</body>
</html>