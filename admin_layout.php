<?php
// =========================================================
// File: admin_layout.php
// Layout bersama halaman admin (Bootstrap 5, gaya sama dgn dashboard admin)
// =========================================================
if (!function_exists('h')) {
    function h($t) { return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8'); }
}

function layout_start($title, $active = '') {
    $menu = [
        'fakultas'  => ['admin_fakultas.php',  'bi-building',  'Fakultas'],
        'prodi'     => ['admin_prodi.php',     'bi-mortarboard', 'Program Studi'],
        'mahasiswa' => ['admin_mahasiswa.php', 'bi-people',    'Mahasiswa'],
    ];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --border-color:#e2e8f0; --text-muted:#64748b; --radius-md:14px; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f8fafc; color:#0f172a; min-height:100vh; }
        .navbar-custom { background:rgba(255,255,255,.85); backdrop-filter:blur(12px); border-bottom:1px solid var(--border-color); position:sticky; top:0; z-index:1030; }
        .nav-pill { color:#475569; font-weight:600; border-radius:50rem; padding:.4rem 1rem; text-decoration:none; font-size:.9rem; }
        .nav-pill:hover { background:#f1f5f9; color:#0f172a; }
        .nav-pill.active { background:#eef2ff; color:#4f46e5; }
        .data-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:0 4px 15px rgba(0,0,0,.03); overflow:hidden; }
        .table > :not(caption) > * > * { padding:.9rem 1rem; vertical-align:middle; }
        .table thead th { font-size:.78rem; text-transform:uppercase; letter-spacing:.8px; font-weight:700; color:var(--text-muted); background:#f1f5f9; }
        .btn-logout { background:#fee2e2; color:#dc2626; font-weight:600; border:none; border-radius:50rem; padding:.4rem 1.1rem; }
        .btn-logout:hover { background:#fca5a5; color:#991b1b; }
        .btn-primary { background:linear-gradient(135deg,#4f46e5,#3b82f6); border:none; }
    </style>
</head>
<body>
<nav class="navbar navbar-custom py-3">
    <div class="container-fluid px-lg-5 d-flex flex-wrap gap-2 justify-content-between">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-5" href="dashboard_admin.php">
            <span class="bg-primary text-white rounded-3 p-1 px-2"><i class="bi bi-shield-lock-fill"></i></span>
            Admin Portal
        </a>
        <div class="d-flex flex-wrap align-items-center gap-1">
            <?php foreach ($menu as $key => [$href, $icon, $label]): ?>
                <a href="<?= $href ?>" class="nav-pill <?= $active === $key ? 'active' : '' ?>">
                    <i class="bi <?= $icon ?> me-1"></i><?= $label ?>
                </a>
            <?php endforeach; ?>
            <a href="logout.php" class="btn btn-logout btn-sm ms-2"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
        </div>
    </div>
</nav>
<div class="container-fluid px-lg-5 py-4">
<?php
}

function layout_end() {
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}