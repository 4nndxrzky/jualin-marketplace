<?php
    /**
 * ============================================================================
 * HALAMAN AUTENTIKASI MASUK (LOGIN)
 * Terkoneksi dengan Database: olx_clone
 * Tabel Target: users (email, password)
 * ============================================================================
 */

    session_start();
    require_once __DIR__ . '/koneksi.php';

    // Jika pengguna sudah login, langsung alihkan ke beranda
    if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
    }

    // Ambil pesan flash (misal dari registrasi berhasil atau logout)
    $successMessage = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_success']);

    $errors = [];
    $email  = $_COOKIE['olx_remember_email'] ?? '';

    // Proses Pengiriman Form Login (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // 1. Validasi Input Dasar
    if (empty($email)) {
        $errors[] = "Alamat email wajib diisi.";
    } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format alamat email tidak valid.";
    }

    if (empty($password)) {
        $errors[] = "Kata sandi wajib diisi.";
    }

    // 2. Autentikasi Pengguna ke Database
    if (empty($errors)) {
        // Query Prepared Statement mencari user berdasarkan email
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 3. Verifikasi Hash Kata Sandi BCRYPT
        if ($user && password_verify($password, $user['password'])) {
            // Mencegah serangan Session Fixation dengan regenerasi Session ID
            session_regenerate_id(true);

            // Simpan identitas pengguna ke Session
            $_SESSION['user_id']    = (int) $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Tangani opsi "Ingat Saya" (menyimpan email di cookie selama 30 hari)
            if ($remember_me) {
                setcookie('olx_remember_email', $email, time() + (86400 * 30), "/", "", false, true);
            } else {
                setcookie('olx_remember_email', '', time() - 3600, "/");
            }

            // Pesan selamat datang di halaman beranda
            $_SESSION['flash_success'] = "Selamat datang kembali, " . $user['name'] . "!";
            header("Location: index.php");
            exit;
        } else {
            // Pesan kesalahan umum demi alasan keamanan (mencegah email enumeration attack)
            $errors[] = "Alamat email atau kata sandi yang Anda masukkan salah.";
        }
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
  <title>Masuk ke Akun — OLX Clone</title>
  <meta name="description" content="Masuk ke akun OLX Clone untuk memasang iklan gratis, mengelola listing jual beli, serta chat langsung dengan calon pembeli terpercaya.">
  <meta name="keywords" content="login OLX Clone, masuk akun, jual beli online, pasang iklan gratis">
  <meta name="author" content="OLX Clone">
  <!-- Best Practice SEO: noindex, follow untuk halaman autentikasi -->
  <meta name="robots" content="noindex, follow">
  <link rel="canonical" href="https://olxclone.local/login.php">

  <!-- ==================== OPEN GRAPH ==================== -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="Masuk ke Akun — OLX Clone">
  <meta property="og:description" content="Masuk ke akun OLX Clone Anda untuk mulai jual beli barang bekas dan baru dengan mudah dan aman.">
  <meta property="og:url" content="https://olxclone.local/login.php">
  <meta property="og:image" content="assets/images/og-preview.jpg">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">

  <!-- ==================== TWITTER CARD ==================== -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="Masuk ke Akun — OLX Clone">
  <meta name="twitter:description" content="Masuk ke akun OLX Clone Anda untuk mengelola iklan dan pesan jual beli.">
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
      "name": "Masuk ke Akun — OLX Clone",
      "url": "https://olxclone.local/login.php",
      "description": "Halaman otentikasi masuk pengguna terdaftar OLX Clone.",
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
            "name": "Masuk Akun",
            "item": "https://olxclone.local/login.php"
          }
        ]
      }
    }
  </script>

  <!-- ==================== FONT AWESOME ICONS ==================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

        <!-- Auth Action Navigasi -->
        <div class="header-actions">
          <a href="index.php" class="btn btn-outline" aria-label="Kembali ke beranda">
            <i class="fa-solid fa-arrow-left"></i> Beranda
          </a>
          <a href="register.php" class="btn btn-primary" aria-label="Daftar akun baru">
            Daftar
          </a>
        </div>

      </div>
    </div>
  </header>


  <!-- ================================================================
       KONTEN UTAMA: HALAMAN LOGIN
       ================================================================ -->
  <main class="auth-page" id="main-content" role="main">
    <div class="auth-wrapper">

      <section class="auth-card" aria-labelledby="auth-heading">

        <!-- Header Box Form -->
        <div class="auth-header-box">
          <span class="auth-icon" aria-hidden="true"><i class="fa-solid fa-key"></i></span>
          <h1 id="auth-heading" class="auth-title">Masuk ke Akun Anda</h1>
          <p class="auth-subtitle">Kelola iklan, chat dengan calon pembeli, dan pantau barang favoritmu.</p>
        </div>

        <!-- NOTIFIKASI SUKSES FLASH (MISAL SETELAH REGISTER ATAU LOGOUT) -->
        <?php if (! empty($successMessage)): ?>
          <div class="alert alert-success" role="alert">
            <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
            <div class="alert-content">
              <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
          </div>
        <?php endif; ?>

        <!-- NOTIFIKASI ERROR (JIKA ADA) -->
        <?php if (! empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert-content">
              <strong>Gagal Masuk:</strong>
              <ul style="margin: 6px 0 0 16px; list-style: disc;">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
          </div>
        <?php endif; ?>

        <!-- FORM LOGIN -->
        <form class="auth-form" action="login.php" method="POST" autocomplete="on">

          <!-- Input Group: Email (users.email) -->
          <div class="form-group">
            <label for="email" class="form-label">Alamat Email</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="nama@email.com"
                required
                autocomplete="email"
                inputmode="email"
                value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                aria-required="true">
            </div>
          </div>

          <!-- Input Group: Password (users.password) -->
          <div class="form-group">
            <div class="form-label-row">
              <label for="password" class="form-label">Kata Sandi</label>
              <a href="lupa-password.php" class="auth-link-sm" tabindex="0">Lupa kata sandi?</a>
            </div>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
              <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Masukkan kata sandi..."
                required
                autocomplete="current-password"
                aria-required="true">
              <button
                type="button"
                class="toggle-password"
                aria-label="Tampilkan atau sembunyikan kata sandi">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <!-- Pilihan Tambahan: Ingat Saya -->
          <div class="auth-options">
            <label class="checkbox-label" for="remember_me">
              <input type="checkbox" id="remember_me" name="remember_me" value="1" <?php echo ! empty($_COOKIE['olx_remember_email']) ? 'checked' : '' ?>>
              <span>Ingat saya di perangkat ini</span>
            </label>
          </div>

          <!-- Tombol Submit Utama -->
          <button type="submit" class="btn btn-solid-primary btn-block btn-auth">
            Masuk Sekarang
          </button>

        </form>

        <!-- Link Navigasi Daftar Akun Baru -->
        <p class="auth-footer-text">
          Belum punya akun OLX Clone?
          <a href="register.php">Daftar Akun Baru</a>
        </p>

        <!-- Informasi Keamanan (Trust Badge) -->
        <div class="auth-trust-badge" role="status">
          <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
          <span>Data pribadi dan transaksi Anda terlindungi enkripsi 256-bit.</span>
        </div>

        <!-- Legal Disclaimer -->
        <p class="auth-disclaimer">
          Dengan mengklik "Masuk", Anda menyetujui
          <a href="syarat-ketentuan.php">Syarat & Ketentuan</a> dan
          <a href="kebijakan-privasi.php">Kebijakan Privasi</a> OLX Clone.
        </p>

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
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
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
