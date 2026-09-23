<?php
/**
 * ============================================================================
 * KONFIGURASI KONEKSI DATABASE (PHP PDO)
 * Proyek: OLX Clone Marketplace
 * Database Target: olx_clone (berdasarkan olx_clone.sql)
 * Server: MySQL 8.0.30 (Laragon / localhost:3306)
 * ============================================================================
 */

// 1. Parameter Konfigurasi Database
$host     = 'localhost';
$port     = 3306;
$dbname   = 'olx_clone';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

// 2. Data Source Name (DSN)
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

// 3. Opsi Konfigurasi PDO (Best Practices)
$options = [
    // Melempar PDOException otomatis saat terjadi kegagalan query SQL
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // Format pengambilan data default adalah Array Asosiatif ['kolom' => 'nilai']
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Nonaktifkan emulasi prepared statements agar dieksekusi native oleh MySQL (kebal SQL Injection)
    PDO::ATTR_EMULATE_PREPARES   => false,

    // Mengubah nilai kosong/string kosong menjadi NULL jika kolom mengizinkan
    PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
];

// 4. Inisialisasi Koneksi dengan Try-Catch
try {
    $pdo = new PDO($dsn, $username, $password, $options);

    // Alias $db agar kompatibel dengan berbagai gaya penulisan variabel ($pdo maupun $db)
    $db = $pdo;

    // Uncomment baris berikut saat ingin mengetes koneksi langsung lewat browser/terminal:
    // echo "[OK] Koneksi database PDO ke '{$dbname}' berhasil!";
} catch (PDOException $e) {
    // Catat log error internal untuk developer
    error_log("Database Connection Error: " . $e->getMessage());

    // Tampilkan pesan yang ramah pengguna dan aman
    die("<h3>Koneksi Database Gagal!</h3>" .
        "<p>Pesan Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>" .
        "<p><em>Pastikan server MySQL di Laragon/XAMPP sudah aktif dan database '<strong>{$dbname}</strong>' sudah dibuat.</em></p>");
}

