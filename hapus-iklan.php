<?php
/**
 * Action Handler: Hapus Iklan Pengguna (hapus-iklan.php)
 * Proteksi Keamanan:
 * 1. Autentikasi ketat (wajib login).
 * 2. Otorisasi kepemilikan ketat (hanya pemilik sah yang dapat menghapus iklan miliknya).
 * 3. Pembersihan berkas fisik foto pada disk uploads/ads/ secara bersih.
 * 4. Transaksi database PDO untuk integritas tabel ads & ad_images.
 */

session_start();
require_once __DIR__ . '/koneksi.php';

// Proteksi Autentikasi: Wajib login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan masuk ke akun Anda terlebih dahulu.";
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$adId   = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($adId <= 0) {
    $_SESSION['flash_error'] = "ID iklan tidak valid.";
    header("Location: iklan-saya.php");
    exit;
}

// Verifikasi Kepemilikan Iklan
$stmt = $pdo->prepare("SELECT id, title FROM ads WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$adId, $userId]);
$ad = $stmt->fetch();

if (!$ad) {
    $_SESSION['flash_error'] = "Iklan tidak ditemukan atau Anda tidak memiliki hak akses untuk menghapus iklan tersebut.";
    header("Location: iklan-saya.php");
    exit;
}

// Ambil path semua foto yang terkait dengan iklan ini sebelum dihapus dari database
$stmtImages = $pdo->prepare("SELECT image_path FROM ad_images WHERE ad_id = ?");
$stmtImages->execute([$adId]);
$images = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

try {
    $pdo->beginTransaction();

    // 1. Hapus relasi foto di tabel ad_images
    $delImagesStmt = $pdo->prepare("DELETE FROM ad_images WHERE ad_id = ?");
    $delImagesStmt->execute([$adId]);

    // 2. Hapus data iklan di tabel ads
    $delAdStmt = $pdo->prepare("DELETE FROM ads WHERE id = ? AND user_id = ?");
    $delAdStmt->execute([$adId, $userId]);

    $pdo->commit();

    // 3. Bersihkan file fisik foto di folder uploads/ads/ setelah database berhasil dihapus
    foreach ($images as $imgPath) {
        if (!empty($imgPath)) {
            $filePath = __DIR__ . '/' . $imgPath;
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }

    $_SESSION['flash_success'] = "Iklan \"" . htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8') . "\" berhasil dihapus secara permanen.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_error'] = "Terjadi kesalahan saat menghapus iklan: " . $e->getMessage();
}

header("Location: iklan-saya.php");
exit;
