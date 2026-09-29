<?php
// =========================================================
// File: auth.php
// Helper session & pembatas akses berdasarkan role
// =========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_login() {
    return isset($_SESSION['id_pengguna']);
}

function require_login() {
    if (!is_login()) {
        header("Location: login.php");
        exit;
    }
}

// $roles: string satu role, atau array beberapa role
function require_role($roles) {
    require_login();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        die("Akses ditolak untuk role ini.");
    }
}

function current_user() {
    return [
        'id_pengguna' => $_SESSION['id_pengguna'] ?? null,
        'username'    => $_SESSION['username'] ?? null,
        'nama'        => $_SESSION['nama'] ?? null,
        'role'        => $_SESSION['role'] ?? null,
    ];
}
?>