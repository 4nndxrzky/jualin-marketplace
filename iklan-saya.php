<?php
session_start();
require_once __DIR__ . '/koneksi.php';

// Proteksi Autentikasi: Wajib login untuk mengakses Iklan Saya
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan masuk ke akun Anda terlebih dahulu untuk mengelola iklan.";
    header("Location: login.php");
    exit;
}

$userId    = (int)$_SESSION['user_id'];
$userName  = $_SESSION['user_name'] ?? 'Pengguna';
$userEmail = $_SESSION['user_email'] ?? '';

// Flash message
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Query seluruh iklan yang diposting oleh pengguna ini
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS category_name, c.icon AS category_icon,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY id ASC LIMIT 1) AS primary_image,
           (SELECT COUNT(*) FROM ad_images WHERE ad_id = a.id) AS total_images
    FROM ads a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$userId]);
$myAds = $stmt->fetchAll();

$totalAds   = count($myAds);
$totalValue = array_sum(array_column($myAds, 'price'));

// Ambil daftar lokasi unik untuk header
$stmtLoc = $pdo->query("
    SELECT DISTINCT location 
    FROM ads 
    WHERE location IS NOT NULL AND TRIM(location) != '' 
    ORDER BY location ASC
");
$locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

// Ambil kategori untuk footer
$stmtCat    = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
$categories = $stmtCat->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS ==================== -->
  <title>Iklan Saya — OLX Clone | Kelola Listing Jual Beli</title>
  <meta name="description" content="Kelola seluruh iklan dan barang yang Anda pasang di OLX Clone. Edit rincian harga, foto, dan deskripsi barang dengan mudah.">
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
        <form class="search-form" action="index.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, properti, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth Actions -->
        <div class="header-actions">
          <div class="user-menu-wrapper">
            <button type="button" class="user-menu-btn" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar-sm"><?php echo strtoupper(substr($userName, 0, 1)); ?></span>
              <span class="user-menu-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
              <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <div class="user-dropdown" role="menu">
              <div style="padding: 10px 16px; border-bottom: 1px solid var(--gray-200);">
                <strong style="display: block; font-size: 0.88rem; color: var(--text-primary);"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></strong>
                <small style="color: var(--text-muted); font-size: 0.75rem;"><?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></small>
              </div>
              <a href="iklan-saya.php" class="dropdown-item" role="menuitem" style="color: var(--primary); font-weight: 700; background-color: var(--gray-100);">
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
        <li aria-current="page">Iklan Saya</li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN UTAMA: IKLAN SAYA
       ================================================================ -->
  <main id="main-content" class="container my-ads-layout" role="main">

    <!-- Flash Notifications -->
    <?php if (!empty($flashSuccess)): ?>
      <div class="alert alert-success" role="alert">
        <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
        <div class="alert-content"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
      </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
      <div class="alert alert-danger" role="alert">
        <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></span>
        <div class="alert-content"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
        <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
      </div>
    <?php endif; ?>

    <!-- Header Judul & Tombol Pasang Iklan -->
    <div class="my-ads-header">
      <div class="my-ads-title-box">
        <h1>Iklan Saya</h1>
        <p>Kelola dan tinjau semua listing iklan yang telah Anda publikasikan.</p>
      </div>
      <a href="pasang-iklan.php" class="btn btn-solid-primary">
        <i class="fa-solid fa-plus"></i> Pasang Iklan Baru
      </a>
    </div>

    <!-- Ringkasan Statistik Listing Pengguna -->
    <div class="my-ads-stats-grid">
      <div class="my-ads-stat-card">
        <div class="my-ads-stat-icon">
          <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="my-ads-stat-info">
          <h4>Total Listing</h4>
          <p><?php echo $totalAds; ?> Iklan</p>
        </div>
      </div>

      <div class="my-ads-stat-card">
        <div class="my-ads-stat-icon green">
          <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="my-ads-stat-info">
          <h4>Total Nilai Listing</h4>
          <p>Rp <?php echo number_format($totalValue, 0, ',', '.'); ?></p>
        </div>
      </div>

      <div class="my-ads-stat-card">
        <div class="my-ads-stat-icon">
          <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="my-ads-stat-info">
          <h4>Status Akun</h4>
          <p style="font-size: 1.05rem; color: var(--success);"><i class="fa-solid fa-shield-halved"></i> Terverifikasi</p>
        </div>
      </div>
    </div>

    <!-- Daftar Iklan Pengguna -->
    <?php if ($totalAds > 0): ?>
      <div class="my-ads-card">
        <div class="my-ads-list">
          <?php foreach ($myAds as $ad): ?>
            <article class="my-ad-item">
              <div class="my-ad-main-info">
                <!-- Thumbnail Foto -->
                <div class="my-ad-thumb">
                  <?php if (!empty($ad['primary_image']) && file_exists(__DIR__ . '/' . $ad['primary_image'])): ?>
                    <img src="<?php echo htmlspecialchars($ad['primary_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?>">
                  <?php else: ?>
                    <i class="<?php echo !empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon']) : 'fa-solid fa-box-open'; ?> placeholder-icon"></i>
                  <?php endif; ?>
                </div>

                <!-- Informasi Iklan -->
                <div class="my-ad-details">
                  <a href="detail.php?id=<?php echo (int)$ad['id']; ?>" class="my-ad-title">
                    <?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                  <p class="my-ad-price">Rp <?php echo number_format($ad['price'], 0, ',', '.'); ?></p>
                  <div class="my-ad-meta">
                    <span>
                      <i class="<?php echo htmlspecialchars($ad['category_icon'] ?? 'fa-solid fa-tag'); ?>" style="color: var(--primary);"></i>
                      <?php echo htmlspecialchars($ad['category_name'] ?? 'Kategori', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span>
                      <i class="fa-solid fa-location-dot"></i>
                      <?php echo htmlspecialchars($ad['location'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span>
                      <i class="fa-regular fa-calendar"></i>
                      <?php echo date('d M Y', strtotime($ad['created_at'])); ?>
                    </span>
                    <span>
                      <i class="fa-solid fa-camera"></i>
                      <?php echo (int)$ad['total_images']; ?> Foto
                    </span>
                    <span class="badge badge-verified" style="font-size: 0.7rem; padding: 2px 6px;">
                      Aktif
                    </span>
                  </div>
                </div>
              </div>

              <!-- Aksi Cepat (Lihat, Edit, Hapus) -->
              <div class="my-ad-actions">
                <a href="detail.php?id=<?php echo (int)$ad['id']; ?>" class="btn btn-sm btn-outline" title="Lihat tampilan iklan">
                  <i class="fa-solid fa-eye"></i> Lihat
                </a>
                <a href="edit-iklan.php?id=<?php echo (int)$ad['id']; ?>" class="btn btn-sm btn-edit-outline" title="Ubah informasi iklan">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
                <a href="hapus-iklan.php?id=<?php echo (int)$ad['id']; ?>" class="btn btn-sm btn-danger-outline" onclick="return confirm('Apakah Anda yakin ingin menghapus iklan \'<?php echo htmlspecialchars(addslashes($ad['title']), ENT_QUOTES, 'UTF-8'); ?>\'? Seluruh foto dan data iklan ini akan dihapus secara permanen.');" title="Hapus iklan ini">
                  <i class="fa-solid fa-trash-can"></i> Hapus
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <!-- State Kosong jika Pengguna Belum Memasang Iklan -->
      <div class="empty-state-box">
        <div class="empty-state-icon">
          <i class="fa-solid fa-box-open"></i>
        </div>
        <h3 class="empty-state-title">Belum Ada Iklan yang Dipasang</h3>
        <p class="empty-state-desc">
          Anda belum memiliki listing iklan yang aktif. Mulai jual mobil, motor, gadget, perabotan, atau properti Anda sekarang dengan mudah, cepat, dan gratis!
        </p>
        <div class="empty-state-actions">
          <a href="pasang-iklan.php" class="btn btn-solid-primary">
            <i class="fa-solid fa-plus"></i> Pasang Iklan Sekarang
          </a>
        </div>
      </div>
    <?php endif; ?>

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
            <?php foreach (array_slice($categories, 0, 4) as $popularCat): ?>
              <li>
                <a href="index.php?c=<?php echo (int)$popularCat['id']; ?>">
                  <?php echo htmlspecialchars($popularCat['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </li>
            <?php endforeach; ?>
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
          <a href="syarat-ketentuan.php">Syarat & Ketentuan</a> &middot;
          <a href="kebijakan-privasi.php">Kebijakan Privasi</a> &middot;
          <a href="sitemap.xml">Sitemap</a>
        </nav>
      </div>

    </div>
  </footer>

  <!-- ==================== JAVASCRIPT ==================== -->
  <script src="assets/js/main.js"></script>
</body>

</html>
