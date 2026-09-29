<?php
// =========================================================
// File: koneksi.php
// Koneksi Database MySQL - InfinityFree
// Menyediakan $conn (dipakai file baru) dan $koneksi (alias, gaya lama)
// =========================================================

$host     = "sql102.infinityfree.com";
$user     = "if0_42928435";
$password = "cE2IqWhmjPg";
$database = "if0_42928435_websemantik";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

$koneksi = $conn; // alias
?>