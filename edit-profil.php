<?php
/**
 * Halaman Edit Profil Pengguna (edit-profil.php)
 * OLX Clone - Codepolitan
 * 
 * Fitur:
 * 1. Autentikasi ketat (wajib login).
 * 2. Pembaruan data dasar: Nama Lengkap, Nomor WhatsApp, Email.
 * 3. Validasi keunikan email (tidak boleh memakai email akun lain).
 * 4. Pembaruan Kata Sandi terproteksi (verifikasi password lama).
 * 5. Sinkronisasi sesi login (nama dan email langsung ter-update di header).
 * 6. Tampilan antarmuka modern, responsif, dan 100% bebas emoji.
 */

session_start();
require_once __DIR__ . '/koneksi.php';

// Proteksi Autentikasi: Wajib login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan masuk ke akun Anda terlebih dahulu untuk mengedit profil.";
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Ambil data user terkini dari database
$stmtUser = $pdo->prepare("SELECT id, name, email, whatsapp, password, created_at FROM users WHERE id = ? LIMIT 1");
$stmtUser->execute([$userId]);
$currentUser = $stmtUser->fetch();

if (!$currentUser) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Hitung total iklan aktif yang dimiliki user
$stmtAds = $pdo->prepare("SELECT COUNT(*) FROM ads WHERE user_id = ?");
$stmtAds->execute([$userId]);
$totalUserAds = (int)$stmtAds->fetchColumn();

// Ambil lokasi unik untuk selector lokasi di header
$stmtLoc = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND TRIM(location) != '' ORDER BY location ASC");
$locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$errors = [];
$name     = $currentUser['name'];
$email    = $currentUser['email'];
$whatsapp = $currentUser['whatsapp'] ?? '';

// Proses Pembaruan Profil (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $whatsapp        = trim($_POST['whatsapp'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 1. Validasi Nama
    if (empty($name)) {
        $errors[] = "Nama lengkap wajib diisi.";
    } elseif (mb_strlen($name) > 100) {
        $errors[] = "Nama lengkap maksimal 100 karakter.";
    }

    // 2. Validasi Email
    if (empty($email)) {
        $errors[] = "Alamat email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format alamat email tidak valid.";
    } elseif (mb_strlen($email) > 100) {
        $errors[] = "Alamat email maksimal 100 karakter.";
    } else {
        // Cek keunikan email (tidak boleh bentrok dengan user lain)
        $emailCheckStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $emailCheckStmt->execute([$email, $userId]);
        if ($emailCheckStmt->fetch()) {
            $errors[] = "Alamat email sudah digunakan oleh akun lain.";
        }
    }

    // 3. Validasi WhatsApp (Opsional, tapi jika diisi dicek formatnya)
    if (!empty($whatsapp)) {
        // Bersihkan karakter selain angka dan tanda plus
        $cleanPhone = preg_replace('/[^0-9+]/', '', $whatsapp);
        if (strlen($cleanPhone) < 8 || strlen($cleanPhone) > 20) {
            $errors[] = "Nomor WhatsApp harus terdiri dari 8 hingga 20 digit angka.";
        } else {
            $whatsapp = $cleanPhone;
        }
    } else {
        $whatsapp = null;
    }

    // 4. Validasi Ganti Kata Sandi (Opsional)
    $wantsPasswordChange = (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword));

    if ($wantsPasswordChange) {
        if (empty($currentPassword)) {
            $errors[] = "Kata sandi saat ini wajib diisi untuk mengonfirmasi perubahan kata sandi.";
        } elseif (!password_verify($currentPassword, $currentUser['password'])) {
            $errors[] = "Kata sandi saat ini yang Anda masukkan salah.";
        }

        if (empty($newPassword)) {
            $errors[] = "Kata sandi baru tidak boleh kosong.";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "Kata sandi baru minimal 6 karakter.";
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = "Konfirmasi kata sandi baru tidak cocok.";
        }
    }

    // Eksekusi Update ke Database jika lolos validasi
    if (empty($errors)) {
        try {
            if ($wantsPasswordChange) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, whatsapp = ?, password = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([$name, $email, $whatsapp, $hashedPassword, $userId]);
            } else {
                $updateStmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, whatsapp = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([$name, $email, $whatsapp, $userId]);
            }

            // Perbarui sesi login aktif
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;

            $_SESSION['flash_success'] = "Profil akun Anda berhasil diperbarui!";
            header("Location: edit-profil.php");
            exit;

        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan pada database: " . $e->getMessage();
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
  <title>Edit Profil — OLX Clone | Pengaturan Akun</title>
  <meta name="description" content="Perbarui informasi profil akun OLX Clone Anda, nomor WhatsApp untuk calon pembeli, serta kelola kata sandi akun Anda.">
  <meta name="robots" content="noindex, follow">

  <!-- ==================== FAVICON ==================== -->
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">

  <!-- ==================== FONT AWESOME ICONS (NO EMOJI) ==================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- ==================== CSS EXTERNAL ==================== -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ================================================================
       HEADER — Logo, Lokasi, Search, Auth
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          OLX<span>Clone</span>
        </a>

        <!-- Lokasi Selector -->
        <div class="location-dropdown-wrapper">
          <button class="location-selector" aria-label="Pilih lokasi" type="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-location-dot"></i>
            <span>Indonesia</span>
            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
          </button>
          <div class="location-menu" role="menu">
            <a href="index.php" class="location-item active">
              <i class="fa-solid fa-earth-asia"></i> Semua Indonesia
            </a>
            <?php foreach ($locations as $loc): ?>
              <a href="index.php?loc=<?php echo urlencode($loc); ?>" class="location-item">
                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Search Bar -->
        <form class="search-bar" action="index.php" method="GET" role="search">
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, properti, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth Header Actions -->
        <div class="header-actions">
          <div class="user-menu-wrapper">
            <button type="button" class="user-menu-btn" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar-sm"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span>
              <span class="user-menu-name"><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
              <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <div class="user-dropdown" role="menu">
              <div style="padding: 10px 16px; border-bottom: 1px solid var(--gray-200);">
                <strong style="display: block; font-size: 0.88rem; color: var(--text-primary);"><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <small style="color: var(--text-muted); font-size: 0.75rem;"><?php echo htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8'); ?></small>
              </div>
              <a href="edit-profil.php" class="dropdown-item" role="menuitem" style="color: var(--primary); font-weight: 700; background-color: var(--gray-100);">
                <i class="fa-solid fa-user-pen"></i> Edit Profil
              </a>
              <a href="iklan-saya.php" class="dropdown-item" role="menuitem">
                <i class="fa-solid fa-box-open"></i> Iklan Saya
              </a>
              <div class="dropdown-divider"></div>
              <a href="logout.php" class="dropdown-item danger-item" role="menuitem">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
              </a>
            </div>
          </div>

          <a href="pasang-iklan.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Jual
          </a>
        </div>

      </div>
    </div>
  </header>


  <!-- ================================================================
       BREADCRUMB
       ================================================================ -->
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <ol class="breadcrumb">
        <li><a href="index.php">Beranda</a></li>
        <li aria-current="page">Edit Profil</li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN UTAMA: EDIT PROFIL
       ================================================================ -->
  <main class="container" id="main-content" role="main">
    <div class="profile-layout">

      <!-- ==================== KOLOM KIRI: FORM PROFIL ==================== -->
      <div class="profile-main">

        <!-- Header Kartu Profil -->
        <div class="profile-header-card">
          <div class="profile-avatar-large">
            <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
          </div>
          <div class="profile-header-info">
            <h1><?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="profile-header-badges">
              <span class="profile-badge-pill verified">
                <i class="fa-solid fa-shield-halved"></i> Akun Terverifikasi
              </span>
              <span class="profile-badge-pill primary">
                <i class="fa-solid fa-box-open"></i> <?php echo $totalUserAds; ?> Iklan Aktif
              </span>
              <span class="profile-badge-pill">
                <i class="fa-regular fa-calendar"></i> Bergabung <?php echo date('M Y', strtotime($currentUser['created_at'])); ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Flash Notifikasi Sukses -->
        <?php if ($flashSuccess): ?>
          <div class="alert alert-success" role="alert">
            <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
            <div class="alert-content">
              <strong>Berhasil:</strong> <?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup pesan">&times;</button>
          </div>
        <?php endif; ?>

        <!-- Notifikasi Error Validasi -->
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert-content">
              <strong>Gagal Memperbarui Profil:</strong>
              <ul style="margin: 6px 0 0 16px; list-style: disc;">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
          </div>
        <?php endif; ?>

        <!-- FORM UTAMA -->
        <form action="edit-profil.php" method="POST" novalidate>

          <!-- KARTU 1: INFORMASI AKUN -->
          <section class="profile-card">
            <h2 class="profile-card-title">
              <i class="fa-solid fa-id-card"></i> 1. Informasi Dasar Akun
            </h2>

            <!-- Nama Lengkap -->
            <div class="form-group" style="margin-bottom: 18px;">
              <label for="name" class="form-label">Nama Lengkap *</label>
              <div class="input-wrapper">
                <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                <input
                  type="text"
                  id="name"
                  name="name"
                  class="form-control"
                  placeholder="Contoh: Ananda Rizky"
                  required
                  maxlength="100"
                  value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <span class="form-hint">Nama ini akan ditampilkan pada seluruh iklan yang Anda pasang.</span>
            </div>

            <!-- Nomor WhatsApp -->
            <div class="form-group" style="margin-bottom: 18px;">
              <label for="whatsapp" class="form-label">Nomor WhatsApp / Kontak Telepon *</label>
              <div class="input-wrapper">
                <span class="input-icon" style="color: #25d366;"><i class="fa-brands fa-whatsapp"></i></span>
                <input
                  type="tel"
                  id="whatsapp"
                  name="whatsapp"
                  class="form-control"
                  placeholder="Contoh: 085693557069 atau 6285693557069"
                  maxlength="20"
                  value="<?php echo htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <span class="form-hint">Calon pembeli akan langsung terhubung ke WhatsApp Anda melalui tombol chat di halaman detail iklan.</span>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label for="email" class="form-label">Alamat Email *</label>
              <div class="input-wrapper">
                <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                <input
                  type="email"
                  id="email"
                  name="email"
                  class="form-control"
                  placeholder="nama@email.com"
                  required
                  maxlength="100"
                  value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <span class="form-hint">Digunakan untuk masuk ke akun OLX Clone Anda.</span>
            </div>
          </section>


          <!-- KARTU 2: PENGATURAN KATA SANDI (OPSIONAL) -->
          <section class="profile-card" style="margin-top: 20px;">
            <h2 class="profile-card-title">
              <i class="fa-solid fa-lock"></i> 2. Ubah Kata Sandi (Opsional)
            </h2>
            <p class="form-hint" style="margin-bottom: 16px;">
              Biarkan seluruh kolom kata sandi di bawah ini kosong jika Anda tidak bermaksud mengganti kata sandi akun Anda.
            </p>

            <!-- Kata Sandi Saat Ini -->
            <div class="form-group" style="margin-bottom: 18px;">
              <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
              <div class="input-wrapper">
                <span class="input-icon"><i class="fa-solid fa-key"></i></span>
                <input
                  type="password"
                  id="current_password"
                  name="current_password"
                  class="form-control"
                  placeholder="Masukkan kata sandi lama Anda"
                  autocomplete="current-password">
              </div>
            </div>

            <!-- Kata Sandi Baru -->
            <div class="form-group" style="margin-bottom: 18px;">
              <label for="new_password" class="form-label">Kata Sandi Baru</label>
              <div class="input-wrapper">
                <span class="input-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <input
                  type="password"
                  id="new_password"
                  name="new_password"
                  class="form-control"
                  placeholder="Minimal 6 karakter"
                  autocomplete="new-password">
              </div>
              <span class="form-hint">Gunakan kombinasi huruf dan angka untuk keamanan ekstra.</span>
            </div>

            <!-- Konfirmasi Kata Sandi Baru -->
            <div class="form-group">
              <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
              <div class="input-wrapper">
                <span class="input-icon"><i class="fa-solid fa-check-double"></i></span>
                <input
                  type="password"
                  id="confirm_password"
                  name="confirm_password"
                  class="form-control"
                  placeholder="Ulangi kata sandi baru"
                  autocomplete="new-password">
              </div>
            </div>
          </section>

          <!-- AKSI SUBMIT / BATAL -->
          <div class="profile-actions">
            <a href="iklan-saya.php" class="btn btn-outline">
              <i class="fa-solid fa-xmark"></i> Batal
            </a>
            <button type="submit" class="btn btn-solid-primary" style="padding: 12px 28px; font-size: 1rem;">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Profil
            </button>
          </div>

        </form>

      </div>


      <!-- ==================== KOLOM KANAN: SIDEBAR PROFIL ==================== -->
      <aside class="profile-sidebar" aria-label="Informasi Tambahan Profil">

        <!-- Card Ringkasan Cepat -->
        <div class="tips-card">
          <div class="tips-card-header">
            <i class="fa-solid fa-circle-user" style="color: var(--primary); font-size: 1.25rem;"></i>
            <h3>Ringkasan Akun</h3>
          </div>
          <div class="profile-stat-box">
            <span class="stat-label">Total Iklan Anda</span>
            <span class="stat-val"><?php echo $totalUserAds; ?> Iklan</span>
          </div>

          <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 10px;">
            <a href="iklan-saya.php" class="btn btn-outline" style="width: 100%; justify-content: center; font-size: 0.88rem;">
              <i class="fa-solid fa-box-open"></i> Kelola Iklan Saya
            </a>
            <a href="pasang-iklan.php" class="btn btn-solid-primary" style="width: 100%; justify-content: center; font-size: 0.88rem;">
              <i class="fa-solid fa-plus"></i> Pasang Iklan Baru
            </a>
          </div>
        </div>

        <!-- Card Panduan Keamanan Akun -->
        <div class="tips-card">
          <div class="tips-card-header">
            <i class="fa-solid fa-shield-halved" style="color: var(--accent); font-size: 1.2rem;"></i>
            <h3>Keamanan Akun</h3>
          </div>
          <ul class="tips-list">
            <li>
              <span class="tips-num">1</span>
              <div>
                <strong>WhatsApp Aktif:</strong>
                <p>Pastikan nomor WhatsApp Anda aktif agar calon pembeli dapat langsung bernegosiasi.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">2</span>
              <div>
                <strong>Kerahasiaan Kata Sandi:</strong>
                <p>Jangan pernah membagikan kata sandi Anda kepada siapa pun, termasuk pihak yang mengatasnamakan OLX Clone.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">3</span>
              <div>
                <strong>Perbarui Berkala:</strong>
                <p>Ubah kata sandi secara berkala terutama jika Anda sering mengakses akun di perangkat umum.</p>
              </div>
            </li>
          </ul>
        </div>

      </aside>

    </div>
  </main>


  <!-- ================================================================
       FOOTER
       ================================================================ -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> OLX Clone. Hak Cipta Dilindungi Undang-Undang.</p>
        <div class="footer-badges">
          <span class="badge-tag"><i class="fa-solid fa-shield-halved"></i> Transaksi Aman</span>
          <span class="badge-tag"><i class="fa-solid fa-check-double"></i> Bebas Biaya</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- SCRIPT UTAMA -->
  <script src="assets/js/main.js"></script>

</body>

</html>
