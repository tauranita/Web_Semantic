<?php
// =========================================================
// File: login.php
// =========================================================
require_once "koneksi.php";
require_once "auth.php";

if (is_login()) {
    header("Location: dashboard_" . $_SESSION['role'] . ".php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id_pengguna, username, password, nama_lengkap, role FROM pengguna WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        $valid = false;
        if ($user) {
            $hash = $user['password'];
            // Hash bcrypt asli (mis. dari password_hash()) diawali $2y$/$2a$/$2b$
            if (preg_match('/^\$2[aby]\$/', $hash)) {
                $valid = password_verify($password, $hash);
            } else {
                // Fallback untuk data latihan yang masih plain text
                $valid = ($password === $hash);
            }
        }

        if ($valid) {
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['nama']        = $user['nama_lengkap'];
            $_SESSION['role']        = $user['role'];

            header("Location: dashboard_" . $user['role'] . ".php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | SIM Mahasiswa UMB</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-sm">
  <div class="text-center mb-6">
    <span class="bg-emerald-700 text-white rounded-xl w-12 h-12 inline-flex items-center justify-center text-xl">🎓</span>
    <h1 class="text-xl font-bold text-slate-800 mt-3">Masuk ke SIM Mahasiswa</h1>
    <p class="text-sm text-slate-500">Universitas Muhammadiyah Bengkulu</p>
  </div>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-2 mb-4">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="bg-white rounded-2xl shadow p-6 space-y-4">
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
      <input type="text" name="username" required
        class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
      <input type="password" name="password" required
        class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <button type="submit"
      class="w-full bg-emerald-700 text-white font-medium py-2.5 rounded-lg hover:bg-emerald-800 transition">
      Masuk
    </button>
  </form>

  <div class="text-center mt-4">
    <a href="index.php" class="text-sm text-slate-500 hover:text-emerald-700">&larr; Kembali ke beranda</a>
  </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>