<?php
/**
 * Halaman Profil / Toko Penjual Publik (penjual.php)
 * OLX Clone - Codepolitan
 * 
 * Menampilkan seluruh etalase iklan yang telah diposting oleh penjual tertentu,
 * dilengkapi identitas penjual, kontak WhatsApp, serta kontrol pagination (limit 20).
 */

session_start();
require_once __DIR__ . '/koneksi.php';

$sellerId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($sellerId <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil profil penjual dari tabel users
$stmtSeller = $pdo->prepare("SELECT id, name, email, whatsapp, created_at FROM users WHERE id = ? LIMIT 1");
$stmtSeller->execute([$sellerId]);
$seller = $stmtSeller->fetch();

if (!$seller) {
    $_SESSION['flash_error'] = "Profil penjual tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Format nomor WhatsApp untuk tombol chat penjual
$sellerWa = trim($seller['whatsapp'] ?? '');
$waUrl    = '';
if (!empty($sellerWa)) {
    $waClean = preg_replace('/[^0-9]/', '', $sellerWa);
    if (str_starts_with($waClean, '0')) {
        $waClean = '62' . substr($waClean, 1);
    } elseif (str_starts_with($waClean, '8')) {
        $waClean = '62' . $waClean;
    }
    $waMessage = "Halo " . $seller['name'] . ", saya melihat etalase iklan Anda di OLX Clone. Apakah barang-barang yang Anda jual masih tersedia?";
    $waUrl     = "https://wa.me/{$waClean}?text=" . rawurlencode($waMessage);
}

// Filter pengurutan & kategori
$sort      = in_array($_GET['sort'] ?? '', ['terbaru', 'termurah', 'termahal', 'rekomendasi'], true) ? $_GET['sort'] : 'terbaru';
$catFilter = isset($_GET['c']) && is_numeric($_GET['c']) ? (int)$_GET['c'] : null;

// Helper URL untuk menjaga parameter penjual & filter
if (!function_exists('sellerUrl')) {
    function sellerUrl(array $overrides = []): string
    {
        $params = $_GET;
        if (!array_key_exists('page', $overrides) && (isset($overrides['c']) || isset($overrides['sort']))) {
            unset($params['page']);
        }
        foreach ($overrides as $k => $v) {
            if ($v === null || $v === '') {
                unset($params[$k]);
            } else {
                $params[$k] = $v;
            }
        }
        return 'penjual.php' . (!empty($params) ? '?' . http_build_query($params) : '');
    }
}

// Ambil kategori unik yang dijual oleh penjual ini
$stmtSellerCats = $pdo->prepare("
    SELECT DISTINCT c.id, c.name, c.icon, COUNT(a.id) as total_ads
    FROM categories c
    INNER JOIN ads a ON a.category_id = c.id
    WHERE a.user_id = ?
    GROUP BY c.id, c.name, c.icon
    ORDER BY c.id ASC
");
$stmtSellerCats->execute([$sellerId]);
$sellerCategories = $stmtSellerCats->fetchAll();

// Pagination (Limit 20 per halaman sesuai standar project)
$page    = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;

$where  = ["a.user_id = ?"];
$params = [$sellerId];

if ($catFilter !== null) {
    $where[]  = "a.category_id = ?";
    $params[] = $catFilter;
}

$sqlCount = "SELECT COUNT(*) FROM ads a WHERE " . implode(" AND ", $where);
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();

$totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $perPage) : 1;
if ($page > $totalPages && $totalRecords > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$orderBy = match ($sort) {
    'termurah'    => 'ORDER BY a.price ASC',
    'termahal'    => 'ORDER BY a.price DESC',
    'rekomendasi' => 'ORDER BY a.price DESC, a.id DESC',
    default       => 'ORDER BY a.created_at DESC',
};

$sqlAds = "
    SELECT a.*, c.name AS category_name, c.icon AS category_icon,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY id ASC LIMIT 1) AS image_path
    FROM ads a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE " . implode(" AND ", $where) . "
    {$orderBy}
    LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

$stmtAds = $pdo->prepare($sqlAds);
$stmtAds->execute($params);
$ads = $stmtAds->fetchAll();

// Lokasi untuk selector header
$stmtLoc = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND TRIM(location) != '' ORDER BY location ASC");
$locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

// Kategori untuk footer
$stmtAllCats = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
$allCategories = $stmtAllCats->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS ==================== -->
  <title>Iklan dari <?php echo htmlspecialchars($seller['name'], ENT_QUOTES, 'UTF-8'); ?> — OLX Clone</title>
  <meta name="description" content="Lihat seluruh etalase barang yang dijual oleh <?php echo htmlspecialchars($seller['name'], ENT_QUOTES, 'UTF-8'); ?> di OLX Clone. Belanja aman langsung dari penjual terpercaya.">
  <meta name="robots" content="index, follow">

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
          <?php if (isset($_SESSION['user_id'])): ?>
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
                <a href="edit-profil.php" class="dropdown-item" role="menuitem">
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
          <?php else: ?>
            <a href="login.php" class="btn btn-outline">Masuk</a>
          <?php endif; ?>
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
        <li aria-current="page">Penjual: <?php echo htmlspecialchars($seller['name'], ENT_QUOTES, 'UTF-8'); ?></li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN UTAMA: PROFIL PENJUAL & ETALASE IKLAN
       ================================================================ -->
  <main class="container" id="main-content" role="main" style="padding-bottom: 60px;">

    <!-- Banner Profil Penjual -->
    <div class="seller-store-header">
      <div class="seller-store-main">
        <div class="profile-avatar-large">
          <?php echo strtoupper(substr($seller['name'], 0, 1)); ?>
        </div>
        <div class="profile-header-info">
          <h1><?php echo htmlspecialchars($seller['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
          <div class="profile-header-badges">
            <span class="profile-badge-pill verified">
              <i class="fa-solid fa-circle-check"></i> Penjual Terverifikasi
            </span>
            <span class="profile-badge-pill primary">
              <i class="fa-solid fa-box-open"></i> <?php echo $totalRecords; ?> Iklan Aktif
            </span>
            <span class="profile-badge-pill">
              <i class="fa-regular fa-calendar"></i> Member sejak <?php echo date('M Y', strtotime($seller['created_at'])); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Aksi Kontak Penjual -->
      <?php if (!empty($waUrl)): ?>
        <div class="seller-store-actions">
          <a href="<?php echo htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-solid-primary" style="padding: 12px 24px; font-size: 0.95rem;">
            <i class="fa-brands fa-whatsapp" style="font-size: 1.15rem;"></i> Hubungi Penjual
          </a>
        </div>
      <?php endif; ?>
    </div>


    <!-- Bar Header Etalase & Pengurutan -->
    <div class="section-header" style="margin-bottom: 20px;">
      <div>
        <h2 style="font-size: 1.35rem; color: var(--primary);">Semua Iklan dari <?php echo htmlspecialchars($seller['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <small style="color: var(--text-muted); font-size: 0.85rem;">
          Menampilkan <?php echo $totalRecords; ?> listing barang yang tersedia
        </small>
      </div>

      <div class="filter-sort-wrapper">
        <label for="sort-select"><i class="fa-solid fa-arrow-down-wide-short"></i> Urutkan:</label>
        <select id="sort-select" class="filter-sort-select" aria-label="Urutkan iklan" onchange="window.location.href=this.value;">
          <option value="<?php echo htmlspecialchars(sellerUrl(['sort' => 'terbaru']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($sort === 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
          <option value="<?php echo htmlspecialchars(sellerUrl(['sort' => 'termurah']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($sort === 'termurah') ? 'selected' : ''; ?>>Harga Terendah</option>
          <option value="<?php echo htmlspecialchars(sellerUrl(['sort' => 'termahal']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($sort === 'termahal') ? 'selected' : ''; ?>>Harga Tertinggi</option>
          <option value="<?php echo htmlspecialchars(sellerUrl(['sort' => 'rekomendasi']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($sort === 'rekomendasi') ? 'selected' : ''; ?>>Rekomendasi</option>
        </select>
      </div>
    </div>


    <!-- Grid Iklan Penjual -->
    <?php if ($totalRecords > 0): ?>
      <div class="ad-grid">
        <?php foreach ($ads as $ad): ?>
          <article class="ad-card">
            <a href="detail.php?id=<?php echo (int)$ad['id']; ?>" aria-label="<?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?> - Rp <?php echo number_format($ad['price'], 0, ',', '.'); ?>">
              <div class="ad-card-image">
                <?php if (!empty($ad['image_path']) && file_exists(__DIR__ . '/' . $ad['image_path'])): ?>
                  <img src="<?php echo htmlspecialchars($ad['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                <?php else: ?>
                  <div class="img-placeholder" aria-hidden="true">
                    <i class="<?php echo !empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon']) : 'fa-solid fa-box-open'; ?>"></i>
                  </div>
                <?php endif; ?>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">
                  <i class="fa-regular fa-heart"></i>
                </button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp <?php echo number_format($ad['price'], 0, ',', '.'); ?></p>
                <h3 class="ad-card-title"><?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($ad['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <time datetime="<?php echo substr($ad['created_at'], 0, 10); ?>"><?php echo date('d M Y', strtotime($ad['created_at'])); ?></time>
                </div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination di Kiri Bawah (Limit 20 Konten) Sesuai Permintaan -->
      <?php if ($totalRecords > $perPage): ?>
        <div class="pagination-wrapper pagination-left">
          <nav class="pagination" aria-label="Navigasi Halaman Penjual">
            <!-- Tombol Prev -->
            <?php if ($page > 1): ?>
              <a href="<?php echo htmlspecialchars(sellerUrl(['page' => $page - 1]), ENT_QUOTES, 'UTF-8'); ?>" class="pagination-btn" aria-label="Halaman Sebelumnya" title="Halaman Sebelumnya">
                <i class="fa-solid fa-chevron-left"></i>
              </a>
            <?php else: ?>
              <span class="pagination-btn disabled" aria-disabled="true" title="Halaman Pertama">
                <i class="fa-solid fa-chevron-left"></i>
              </span>
            <?php endif; ?>

            <!-- Nomor Halaman -->
            <?php
              $startPage = max(1, $page - 2);
              $endPage   = min($totalPages, $page + 2);

              if ($startPage > 1) {
                  echo '<a href="' . htmlspecialchars(sellerUrl(['page' => 1]), ENT_QUOTES, 'UTF-8') . '" class="pagination-link">1</a>';
                  if ($startPage > 2) {
                      echo '<span class="pagination-ellipsis">&hellip;</span>';
                  }
              }

              for ($p = $startPage; $p <= $endPage; $p++) {
                  if ($p === $page) {
                      echo '<span class="pagination-link active" aria-current="page">' . $p . '</span>';
                  } else {
                      echo '<a href="' . htmlspecialchars(sellerUrl(['page' => $p]), ENT_QUOTES, 'UTF-8') . '" class="pagination-link">' . $p . '</a>';
                  }
              }

              if ($endPage < $totalPages) {
                  if ($endPage < $totalPages - 1) {
                      echo '<span class="pagination-ellipsis">&hellip;</span>';
                  }
                  echo '<a href="' . htmlspecialchars(sellerUrl(['page' => $totalPages]), ENT_QUOTES, 'UTF-8') . '" class="pagination-link">' . $totalPages . '</a>';
              }
            ?>

            <!-- Tombol Next -->
            <?php if ($page < $totalPages): ?>
              <a href="<?php echo htmlspecialchars(sellerUrl(['page' => $page + 1]), ENT_QUOTES, 'UTF-8'); ?>" class="pagination-btn" aria-label="Halaman Selanjutnya" title="Halaman Selanjutnya">
                <i class="fa-solid fa-chevron-right"></i>
              </a>
            <?php else: ?>
              <span class="pagination-btn disabled" aria-disabled="true" title="Halaman Terakhir">
                <i class="fa-solid fa-chevron-right"></i>
              </span>
            <?php endif; ?>
          </nav>

          <div class="pagination-summary">
            Menampilkan <strong><?php echo ($offset + 1); ?> - <?php echo min($offset + $perPage, $totalRecords); ?></strong> dari <strong><?php echo $totalRecords; ?></strong> iklan
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- State Kosong jika Penjual Tidak Memiliki Iklan -->
      <div class="empty-state-box">
        <div class="empty-state-icon">
          <i class="fa-solid fa-box-open"></i>
        </div>
        <h3 class="empty-state-title">Penjual Belum Memiliki Iklan Aktif</h3>
        <p class="empty-state-desc">
          Saat ini <?php echo htmlspecialchars($seller['name'], ENT_QUOTES, 'UTF-8'); ?> belum memiliki iklan yang aktif atau listing barang telah terjual.
        </p>
        <div class="empty-state-actions">
          <a href="index.php" class="btn btn-solid-primary">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
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
            <?php foreach (array_slice($allCategories, 0, 4) as $popularCat): ?>
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

  <!-- SCRIPT UTAMA -->
  <script src="assets/js/main.js"></script>

</body>

</html>
