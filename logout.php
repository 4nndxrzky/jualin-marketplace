<?php
/**
 * ============================================================================
 * PROSES LOGOUT PENGGUNA
 * Menghapus sesi aktif dan mengarahkan kembali ke halaman login dengan notifikasi
 * ============================================================================
 */

session_start();

// Hapus seluruh variabel sesi
$_SESSION = [];

// Hapus cookie sesi dari browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Mulai sesi baru secara aman untuk membawa pesan notifikasi flash
session_start();
$_SESSION['flash_success'] = "Anda telah berhasil keluar dari akun.";

// Arahkan kembali ke halaman login
header("Location: login.php");
exit;
