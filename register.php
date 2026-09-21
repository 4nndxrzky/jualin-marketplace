<?php
/**
 * ============================================================================
 * HALAMAN REGISTRASI PENGGUNA (REGISTER)
 * Terkoneksi dengan Database: olx_clone
 * Tabel Target: users (name, email, password)
 * ============================================================================
 */

session_start();
require_once __DIR__ . '/koneksi.php';

// Jika pengguna sudah login, langsung alihkan ke beranda
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errors = [];
$name   = '';
$email  = '';

// Proses Pengiriman Form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name                  = trim($_POST['name'] ?? '');
    $email                 = trim($_POST['email'] ?? '');
    $password              = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';
    $agree_terms           = isset($_POST['agree_terms']);

    // 1. Validasi Nama
    if (empty($name)) {
        $errors[] = "Nama lengkap wajib diisi.";
    } elseif (mb_strlen($name) > 100) {
        $errors[] = "Nama lengkap maksimal 100 karakter.";
    }

    // 2. Validasi Email & Pengecekan Unik di Database
    if (empty($email)) {
        $errors[] = "Alamat email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format alamat email tidak valid.";
    } elseif (mb_strlen($email) > 100) {
        $errors[] = "Alamat email maksimal 100 karakter.";
    } else {
        // Query Prepared Statement: Cek duplikasi email di tabel users
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors[] = "Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.";
        }
    }

    // 3. Validasi Password
    if (empty($password)) {
        $errors[] = "Kata sandi wajib diisi.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Kata sandi minimal harus terdiri dari 8 karakter.";
    } elseif ($password !== $password_confirmation) {
        $errors[] = "Konfirmasi kata sandi tidak cocok.";
    }

    // 4. Validasi Persetujuan Syarat & Ketentuan
    if (!$agree_terms) {
        $errors[] = "Anda wajib menyetujui Syarat & Ketentuan serta Kebijakan Privasi.";
    }

    // 5. Eksekusi Simpan ke Database Jika Bebas Error
    if (empty($errors)) {
        // Enkripsi password menggunakan BCRYPT (menghasilkan hash 60 karakter)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $insertStmt = $db->prepare("
            INSERT INTO users (name, email, password)
            VALUES (?, ?, ?)
        ");
        $insertStmt->execute([$name, $email, $hashedPassword]);

        // Berikan notifikasi sukses via Flash Message Session
        $_SESSION['flash_success'] = "Pendaftaran berhasil! Akun Anda telah dibuat. Silakan masuk.";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS ==================== -->
  <title>Daftar Akun Baru — OLX Clone | Jual Beli Mudah & Cepat</title>
  <meta name="description" content="Daftar akun OLX Clone gratis sekarang! Pasang iklan jual beli mobil, motor, properti, dan gadget bekas maupun baru dengan mudah, aman, dan terpercaya.">
  <meta name="keywords" content="daftar OLX Clone, buat akun baru, registrasi marketplace, jual beli online, pasang iklan gratis">
  <meta name="author" content="OLX Clone">
  <!-- Best Practice SEO: noindex, follow untuk form otentikasi -->
  <meta name="robots" content="noindex, follow">
  <link rel="canonical" href="https://olxclone.local/register.php">

  <!-- ==================== OPEN GRAPH ==================== -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="Daftar Akun Baru — OLX Clone">
  <meta property="og:description" content="Bergabunglah bersama ribuan penjual dan pembeli terpercaya di OLX Clone. Pasang iklan gratis hari ini!">
  <meta property="og:url" content="https://olxclone.local/register.php">
  <meta property="og:image" content="assets/images/og-preview.jpg">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">

  <!-- ==================== TWITTER CARD ==================== -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="Daftar Akun Baru — OLX Clone">
  <meta name="twitter:description" content="Buat akun OLX Clone gratis dan mulai jual beli barang dengan cepat.">
  <meta name="twitter:image" content="assets/images/og-preview.jpg">

  <!-- ==================== FAVICON ==================== -->
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">

  <!-- ==================== JSON-LD STRUCTURED DATA ==================== -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Daftar Akun Baru — OLX Clone",
      "url": "https://olxclone.local/register.php",
      "description": "Halaman pendaftaran akun baru pengguna OLX Clone.",
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Beranda",
            "item": "https://olxclone.local/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Daftar Akun",
            "item": "https://olxclone.local/register.php"
          }
        ]
      }
    }
  </script>

  <!-- ==================== CSS EXTERNAL ==================== -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ================================================================
       HEADER
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          OLX<span>Clone</span>
        </a>

        <!-- Search Bar -->
        <form class="search-form" action="search.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, laptop, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari">🔍</button>
        </form>

        <!-- Auth Action Navigasi -->
        <div class="header-actions">
          <a href="index.php" class="btn btn-outline" aria-label="Kembali ke beranda">← Beranda</a>
          <a href="login.php" class="btn btn-primary" aria-label="Masuk ke akun yang sudah ada">
            Masuk
          </a>
        </div>

      </div>
    </div>
  </header>


  <!-- ================================================================
       KONTEN UTAMA: HALAMAN REGISTER
       ================================================================ -->
  <main class="auth-page" id="main-content" role="main">
    <div class="auth-wrapper">

      <section class="auth-card" aria-labelledby="register-heading">

        <!-- Header Box Form -->
        <div class="auth-header-box">
          <span class="auth-icon" aria-hidden="true">🚀</span>
          <h1 id="register-heading" class="auth-title">Daftar Akun Baru</h1>
          <p class="auth-subtitle">Bergabung sekarang dan pasang iklan pertamamu dalam hitungan menit!</p>
        </div>

        <!-- NOTIFIKASI ERROR (JIKA ADA) -->
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <span class="alert-icon" aria-hidden="true">⚠️</span>
            <div class="alert-content">
              <strong>Pendaftaran Gagal:</strong>
              <ul style="margin: 6px 0 0 16px; list-style: disc;">
                <?php foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
          </div>
        <?php endif; ?>

        <!-- FORM REGISTRASI -->
        <form class="auth-form" action="register.php" method="POST" autocomplete="on">

          <!-- 1. Input Group: Nama Lengkap (users.name) -->
          <div class="form-group">
            <label for="name" class="form-label">Nama Lengkap</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">👤</span>
              <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                placeholder="Contoh: Budi Santoso"
                required
                autocomplete="name"
                maxlength="100"
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                aria-required="true">
            </div>
            <span class="form-hint">Gunakan nama asli untuk meningkatkan reputasi & kepercayaan pembeli.</span>
          </div>

          <!-- 2. Input Group: Alamat Email (users.email - UNIQUE) -->
          <div class="form-group">
            <label for="email" class="form-label">Alamat Email</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">✉️</span>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="nama@email.com"
                required
                autocomplete="email"
                inputmode="email"
                maxlength="100"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                aria-required="true">
            </div>
            <span class="form-hint">Email aktif untuk verifikasi akun dan pemberitahuan transaksi.</span>
          </div>

          <!-- 3. Input Group: Kata Sandi (users.password) -->
          <div class="form-group">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">🔒</span>
              <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Minimal 8 karakter..."
                required
                minlength="8"
                autocomplete="new-password"
                aria-required="true">
              <button
                type="button"
                class="toggle-password"
                aria-label="Tampilkan atau sembunyikan kata sandi">
                👁️
              </button>
            </div>
            <span class="form-hint">Kombinasi minimal 8 karakter (disarankan huruf besar, huruf kecil & angka).</span>
          </div>

          <!-- 4. Input Group: Konfirmasi Kata Sandi -->
          <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">🔒</span>
              <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-control"
                placeholder="Ketik ulang kata sandi..."
                required
                minlength="8"
                autocomplete="new-password"
                aria-required="true">
              <button
                type="button"
                class="toggle-password"
                aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi">
                👁️
              </button>
            </div>
          </div>

          <!-- 5. Persetujuan Syarat & Ketentuan -->
          <div class="auth-options">
            <label class="checkbox-label" for="agree_terms">
              <input type="checkbox" id="agree_terms" name="agree_terms" value="1" required checked aria-required="true">
              <span>
                Saya menyetujui
                <a href="syarat-ketentuan.php" target="_blank" rel="noopener">Syarat & Ketentuan</a> serta
                <a href="kebijakan-privasi.php" target="_blank" rel="noopener">Kebijakan Privasi</a> OLX Clone.
              </span>
            </label>
          </div>

          <!-- Tombol Submit Utama -->
          <button type="submit" class="btn btn-solid-primary btn-block btn-auth">
            Daftar Sekarang
          </button>

        </form>

        <!-- Garis Pemisah (Divider) -->
        <div class="auth-divider" role="separator">
          <span>atau daftar dengan</span>
        </div>

        <!-- Tombol Pendaftaran Alternatif / Sosial Media -->
        <div class="social-auth-grid">
          <button type="button" class="btn-social" aria-label="Daftar menggunakan akun Google">
            <span aria-hidden="true">🌐</span> Daftar dengan Google
          </button>
          <button type="button" class="btn-social" aria-label="Daftar menggunakan Nomor Handphone">
            <span aria-hidden="true">📱</span> Daftar dengan No. Handphone
          </button>
        </div>

        <!-- Link ke Halaman Login -->
        <p class="auth-footer-text">
          Sudah punya akun OLX Clone?
          <a href="login.php">Masuk di sini</a>
        </p>

        <!-- Informasi Keamanan & Privasi (Trust Badge) -->
        <div class="auth-trust-badge" role="status">
          <span aria-hidden="true">🛡️</span>
          <span>Data pribadi Anda aman dan tidak akan dibagikan tanpa izin.</span>
        </div>

      </section>

    </div>
  </main>


  <!-- ================================================================
       FOOTER
       ================================================================ -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">

      <div class="footer-grid">

        <!-- Kolom 1: Tentang -->
        <div class="footer-col">
          <h3>OLX Clone</h3>
          <ul>
            <li><a href="tentang.php">Tentang Kami</a></li>
            <li><a href="karir.php">Karir</a></li>
            <li><a href="kontak.php">Hubungi Kami</a></li>
            <li><a href="blog.php">Blog</a></li>
          </ul>
        </div>

        <!-- Kolom 2: Kategori Populer -->
        <div class="footer-col">
          <h3>Kategori Populer</h3>
          <ul>
            <li><a href="kategori.php?c=mobil">Mobil Bekas</a></li>
            <li><a href="kategori.php?c=motor">Motor Bekas</a></li>
            <li><a href="kategori.php?c=properti">Rumah & Apartemen</a></li>
            <li><a href="kategori.php?c=elektronik">HP & Laptop</a></li>
          </ul>
        </div>

        <!-- Kolom 3: Bantuan -->
        <div class="footer-col">
          <h3>Bantuan</h3>
          <ul>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="keamanan.php">Tips Keamanan</a></li>
            <li><a href="panduan.php">Panduan Jual Beli</a></li>
            <li><a href="laporkan.php">Laporkan Iklan</a></li>
          </ul>
        </div>

        <!-- Kolom 4: Ikuti Kami -->
        <div class="footer-col">
          <h3>Ikuti Kami</h3>
          <div class="footer-social">
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook">📘</a>
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram">📸</a>
            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter/X">🐦</a>
            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube">▶️</a>
          </div>
        </div>

      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <p>&copy; 2026 OLX Clone. Dibuat untuk belajar di Kelas Fullstack Codepolitan.</p>
        <nav aria-label="Footer legal">
          <a href="syarat-ketentuan.php">Syarat & Ketentuan</a> ·
          <a href="kebijakan-privasi.php">Kebijakan Privasi</a> ·
          <a href="sitemap.xml">Sitemap</a>
        </nav>
      </div>

    </div>
  </footer>

  <!-- ==================== JAVASCRIPT ==================== -->
  <script src="assets/js/main.js"></script>
</body>

</html>
