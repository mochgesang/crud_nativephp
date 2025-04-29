<?php
session_start();

// Hapus session
$_SESSION = []; // Mengosongkan session
session_unset(); // Menghapus semua variabel session
session_destroy(); // Menghancurkan session

// Hapus cookie
if (isset($_COOKIE['id'])) {
    setcookie('id', '', time() - 3600, '/');
}
if (isset($_COOKIE['key'])) {
    setcookie('key', '', time() - 3600, '/');
}

// Arahkan kembali ke halaman login
header("Location: login.php");
exit;
?>