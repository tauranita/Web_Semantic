<?php
// =========================================================
// File: cek.php  (SEMENTARA - hapus setelah selesai)
// Memeriksa file, sintaks, koneksi, dan tabel database.
// =========================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function ok($t)   { echo "<div style='color:#15803d'>✔ $t</div>"; }
function gagal($t){ echo "<div style='color:#b91c1c'>✘ $t</div>"; }
function judul($t){ echo "<h3 style='margin:18px 0 6px'>$t</h3>"; }

echo "<body style='font-family:sans-serif;max-width:760px;margin:30px auto;line-height:1.6'>";
echo "<h2>Diagnosa Website</h2>";
echo "<div>Versi PHP di server: <b>" . PHP_VERSION . "</b></div>";

// 1. Keberadaan file
judul("1. File yang dibutuhkan");
$files = ['koneksi.php','auth.php','login.php','logout.php','index.php',
          'dashboard_admin.php','dashboard_prodi.php','dashboard_mahasiswa.php',
          'edit_mahasiswa.php','admin_layout.php','admin_fakultas.php',
          'admin_prodi.php','admin_mahasiswa.php'];
foreach ($files as $f) {
    file_exists(__DIR__ . "/$f") ? ok($f) : gagal("$f <b>TIDAK ADA</b> di folder ini");
}
if (file_exists(__DIR__ . '/admin_dashboard.php')) {
    gagal("admin_dashboard.php (versi lama) masih ada - sebaiknya dihapus");
}

// 2. Cek sintaks tiap file
judul("2. Cek sintaks PHP (file rusak/terpotong)");
foreach ($files as $f) {
    $p = __DIR__ . "/$f";
    if (!file_exists($p)) continue;
    try {
        token_get_all(file_get_contents($p), TOKEN_PARSE);
        ok("$f sintaks OK");
    } catch (ParseError $e) {
        gagal("$f error sintaks baris " . $e->getLine() . ": " . htmlspecialchars($e->getMessage()));
    }
}

// 3. Koneksi
judul("3. Koneksi database");
$conn = null;
try {
    require __DIR__ . '/koneksi.php';
    if (isset($conn) && $conn) ok('$conn tersedia'); else gagal('$conn TIDAK terdefinisi (koneksi.php versi lama?)');
    if (isset($koneksi)) ok('$koneksi tersedia'); else echo "<div>ℹ $koneksi tidak ada (tidak masalah)</div>";
} catch (Throwable $e) {
    gagal("Koneksi gagal: " . htmlspecialchars($e->getMessage()));
}

// 4. Tabel & kolom
if ($conn) {
    judul("4. Tabel di database");
    $butuh = [
        'fakultas'        => ['kode_fakultas','nama_fakultas'],
        'program_studi'   => ['kode_prodi','nama_prodi','kode_fakultas'],
        'pengguna'        => ['id_pengguna','username','password','nama_lengkap','role'],
        'pengelola_prodi' => ['id_pengguna','kode_prodi'],
        'mahasiswa'       => ['npm','id_pengguna','jenis_kelamin','tempat_lahir','tanggal_lahir','tanggal_masuk','alamat','kode_prodi'],
    ];
    foreach ($butuh as $tabel => $kolom) {
        try {
            $res = mysqli_query($conn, "SHOW COLUMNS FROM `$tabel`");
            $ada = array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), 'Field');
            $kurang = array_diff($kolom, $ada);
            if ($kurang) gagal("Tabel <b>$tabel</b> ada, tapi kolom hilang: " . implode(', ', $kurang) . " (skema lama?)");
            else ok("Tabel <b>$tabel</b> sesuai skema v4");
        } catch (Throwable $e) {
            gagal("Tabel <b>$tabel</b> TIDAK ADA - import SQL v4 dulu");
        }
    }

    judul("5. Akun pengguna");
    try {
        $res = mysqli_query($conn, "SELECT username, role, LEFT(password,4) AS awal FROM pengguna");
        foreach (mysqli_fetch_all($res, MYSQLI_ASSOC) as $r) {
            $jenis = (strpos($r['awal'], '$2') === 0) ? 'hash bcrypt' : 'plain text / belum di-hash';
            echo "<div>• <b>" . htmlspecialchars($r['username']) . "</b> (" . $r['role'] . ") - $jenis</div>";
        }
    } catch (Throwable $e) {
        gagal("Tidak bisa membaca tabel pengguna: " . htmlspecialchars($e->getMessage()));
    }
}

echo "<p style='margin-top:24px;color:#64748b'>Selesai. <b>Hapus cek.php dari server</b> setelah dipakai.</p>";
echo "</body>";