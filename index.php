<?php
    session_start();
    require_once __DIR__ . '/koneksi.php';

    // Flash message (misal setelah pasang iklan atau aksi lain)
    $flashSuccess = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_success']);

    // --------------------------------------------------------------------------
    // 1. Tangkap Parameter Filter & Pencarian
    // --------------------------------------------------------------------------
    $search    = trim($_GET['q'] ?? '');
    $catFilter = isset($_GET['c']) && is_numeric($_GET['c']) && (int) $_GET['c'] > 0 ? (int) $_GET['c'] : null;
    $locFilter = trim($_GET['loc'] ?? '');
    $sort      = in_array($_GET['sort'] ?? '', ['terbaru', 'termurah', 'termahal', 'rekomendasi'], true) ? $_GET['sort'] : 'terbaru';

    // --------------------------------------------------------------------------
    // 2. Helper URL Builder untuk Parameter Filter Preservasi
    // --------------------------------------------------------------------------
    if (! function_exists('filterUrl')) {
    function filterUrl(array $overrides = []): string
    {
        $params = $_GET;

        // Reset ke halaman 1 jika filter pencarian, kategori, lokasi, atau pengurutan berganti
        if (! array_key_exists('page', $overrides) && (isset($overrides['c']) || isset($overrides['q']) || isset($overrides['loc']) || isset($overrides['sort']))) {
            unset($params['page']);
        }

        foreach ($overrides as $key => $val) {
            if ($val === null || $val === '') {
                unset($params[$key]);
            } else {
                $params[$key] = $val;
            }
        }
        return 'index.php' . (! empty($params) ? '?' . http_build_query($params) : '');
    }
    }

    // --------------------------------------------------------------------------
    // 3. Ambil Kategori & Lokasi Dinamis dari Database
    // --------------------------------------------------------------------------
    $stmtCat    = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
    $categories = $stmtCat->fetchAll();

    $categoryMap = [];
    foreach ($categories as $c) {
    $categoryMap[(int) $c['id']] = $c;
    }
    $currentCategoryName = ($catFilter && isset($categoryMap[$catFilter])) ? $categoryMap[$catFilter]['name'] : null;

    // Ambil daftar lokasi unik dari iklan yang aktif
    $stmtLoc = $pdo->query("
    SELECT DISTINCT location
    FROM ads
    WHERE location IS NOT NULL AND TRIM(location) != ''
    ORDER BY location ASC
");
    $locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

    // --------------------------------------------------------------------------
    // 4. Query Dinamis untuk Daftar Iklan (Dengan Filter & Search)
    // --------------------------------------------------------------------------
    $where  = [];
    $params = [];

    if ($search !== '') {
    $where[]  = "(a.title LIKE ? OR a.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    }

    if ($catFilter !== null) {
    $where[]  = "a.category_id = ?";
    $params[] = $catFilter;
    }

    if ($locFilter !== '') {
    $where[]  = "a.location = ?";
    $params[] = $locFilter;
    }

    $orderBy = match ($sort) {
    'termurah'    => 'ORDER BY a.price ASC',
    'termahal'    => 'ORDER BY a.price DESC',
    'rekomendasi' => 'ORDER BY a.price DESC, a.id DESC',
    default       => 'ORDER BY a.created_at DESC',
    };

    // --------------------------------------------------------------------------
    // 4.1 Hitung Total Iklan Sesuai Filter untuk Pagination (Limit 20 per halaman)
    // --------------------------------------------------------------------------
    $page    = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $perPage = 20;

    $sqlCount = "SELECT COUNT(*) FROM ads a";
    if (! empty($where)) {
    $sqlCount .= " WHERE " . implode(" AND ", $where);
    }
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $totalRecords = (int) $stmtCount->fetchColumn();

    $totalPages = $totalRecords > 0 ? (int) ceil($totalRecords / $perPage) : 1;
    if ($page > $totalPages && $totalRecords > 0) {
    $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $sqlAds = "
    SELECT a.*, c.name AS category_name, c.icon AS category_icon,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY id ASC LIMIT 1) AS image_path
    FROM ads a
    LEFT JOIN categories c ON a.category_id = c.id
";

    if (! empty($where)) {
    $sqlAds .= " WHERE " . implode(" AND ", $where);
    }
    $sqlAds .= " " . $orderBy . " LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;

    $stmtAds = $pdo->prepare($sqlAds);
    $stmtAds->execute($params);
    $ads      = $stmtAds->fetchAll();
    $totalAds = count($ads);

    // Cek apakah sedang ada filter yang aktif
    $hasActiveFilter = ($search !== '' || $catFilter !== null || $locFilter !== '' || $sort !== 'terbaru');

    // --------------------------------------------------------------------------
    // 5. Query Rekomendasi Dinamis dari Database (0 Hardcoded)
    // --------------------------------------------------------------------------
    $stmtRec = $pdo->query("
    SELECT a.*, c.name AS category_name, c.icon AS category_icon,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY id ASC LIMIT 1) AS image_path
    FROM ads a
    LEFT JOIN categories c ON a.category_id = c.id
    ORDER BY a.price DESC, a.id DESC
    LIMIT 4
");
    $recommendations = $stmtRec->fetchAll();

    // Penentuan meta title dinamis
    $pageTitle = 'OLX Clone — Jual Beli Online Mudah & Terpercaya';
    if ($search !== '') {
    $pageTitle = 'Cari "' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '" — OLX Clone';
    } elseif ($currentCategoryName) {
    $pageTitle = 'Jual Beli ' . htmlspecialchars($currentCategoryName, ENT_QUOTES, 'UTF-8') . ' — OLX Clone';
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
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Temukan ribuan iklan barang bekas dan baru di OLX Clone. Jual beli mobil, properti, elektronik, dan lainnya dengan mudah, cepat, dan terpercaya.">
  <meta name="keywords" content="jual beli online, marketplace, iklan gratis, barang bekas, OLX, beli murah, jual cepat">
  <meta name="author" content="OLX Clone">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://olxclone.local/<?php echo $hasActiveFilter ? htmlspecialchars(filterUrl(), ENT_QUOTES, 'UTF-8') : ''; ?>">

  <!-- ==================== OPEN GRAPH (Facebook/WhatsApp) ==================== -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo $pageTitle; ?>">
  <meta property="og:description" content="Temukan ribuan iklan barang bekas dan baru. Jual beli mobil, properti, elektronik dengan mudah dan terpercaya.">
  <meta property="og:url" content="https://olxclone.local/">
  <meta property="og:image" content="https://olxclone.local/assets/images/og-preview.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">

  <!-- ==================== TWITTER CARD ==================== -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
  <meta name="twitter:description" content="Temukan ribuan iklan barang bekas dan baru di OLX Clone.">
  <meta name="twitter:image" content="https://olxclone.local/assets/images/og-preview.jpg">

  <!-- ==================== FAVICON ==================== -->
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">

  <!-- ==================== JSON-LD STRUCTURED DATA ==================== -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "OLX Clone",
      "url": "https://olxclone.local/",
      "description": "Jual beli online mudah dan terpercaya",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://olxclone.local/index.php?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
  </script>

  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "OLX Clone",
      "url": "https://olxclone.local/",
      "logo": "https://olxclone.local/assets/images/logo.png",
      "sameAs": [
        "https://facebook.com/olxclone",
        "https://instagram.com/olxclone",
        "https://twitter.com/olxclone"
      ]
    }
  </script>

  <!-- ==================== FONT AWESOME ICONS (NO EMOJI) ==================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- ==================== CSS EXTERNAL ==================== -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ================================================================
       HEADER — Logo, Search, Lokasi Dinamis, Auth
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          OLX<span>Clone</span>
        </a>

        <!-- Lokasi Dinamis Dropdown -->
        <div class="location-dropdown-wrapper">
          <button class="location-selector" aria-label="Pilih lokasi" type="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-location-dot"></i>
            <span><?php echo ! empty($locFilter) ? htmlspecialchars($locFilter, ENT_QUOTES, 'UTF-8') : 'Indonesia'; ?></span>
            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
          </button>
          <div class="location-menu" role="menu">
            <a href="<?php echo htmlspecialchars(filterUrl(['loc' => '']), ENT_QUOTES, 'UTF-8'); ?>" class="location-item <?php echo empty($locFilter) ? 'active' : ''; ?>">
              <i class="fa-solid fa-earth-asia"></i> Semua Indonesia
            </a>
            <?php foreach ($locations as $loc): ?>
              <a href="<?php echo htmlspecialchars(filterUrl(['loc' => $loc]), ENT_QUOTES, 'UTF-8'); ?>" class="location-item <?php echo($locFilter === $loc) ? 'active' : ''; ?>">
                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Search Bar -->
        <form class="search-form" action="index.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <?php if ($catFilter !== null): ?>
            <input type="hidden" name="c" value="<?php echo (int) $catFilter; ?>">
          <?php endif; ?>
          <?php if (! empty($locFilter)): ?>
            <input type="hidden" name="loc" value="<?php echo htmlspecialchars($locFilter, ENT_QUOTES, 'UTF-8'); ?>">
          <?php endif; ?>
          <input type="search" id="search-input" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari mobil, HP, properti, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth — Status Login dari Tabel users -->
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
                <a href="penjual.php?id=<?php echo (int) $_SESSION['user_id']; ?>" class="dropdown-item" role="menuitem">
                  <i class="fa-solid fa-store"></i> Toko Saya
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
       NAVIGASI KATEGORI — Tabel: categories (id, name, icon)
       Satu wrapper terintegrasi dengan ekspansi inline "Lainnya"
       ================================================================ -->
  <nav class="category-nav" aria-label="Navigasi kategori">
    <div class="container">
      <div class="category-nav-wrapper" id="category-nav-wrapper">

        <!-- Baris Utama Kategori -->
        <div class="category-nav-main-row">
          <ul class="category-nav-list">
            <li>
              <a href="<?php echo htmlspecialchars(filterUrl(['c' => '']), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo($catFilter === null) ? 'active' : ''; ?>">
                <span class="cat-icon"><i class="fa-solid fa-border-all"></i></span>
                Semua Kategori
              </a>
            </li>
            <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
              <li>
                <a href="<?php echo htmlspecialchars(filterUrl(['c' => $cat['id']]), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo($catFilter === (int) $cat['id']) ? 'active' : ''; ?>">
                  <span class="cat-icon"><i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i></span>
                  <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>

          <!-- Tombol Titik Tiga / Lainnya untuk Membuka Seluruh Kategori -->
          <div class="category-more-wrapper">
            <button type="button" class="category-more-btn" id="category-more-btn" aria-expanded="false" aria-controls="category-nav-expanded" title="Tampilkan Semua Kategori">
              <span class="cat-icon"><i class="fa-solid fa-ellipsis"></i></span>
              <span class="more-text">Lainnya</span>
              <i class="fa-solid fa-chevron-down more-arrow"></i>
            </button>
          </div>
        </div>

        <!-- Bagian yang Ikut Masuk Mengembang ke Bawah (Satu Wrapper) -->
        <div class="category-nav-expanded" id="category-nav-expanded" aria-hidden="true">
          <div class="category-nav-expanded-divider"></div>
          <div class="category-nav-expanded-heading">
            <i class="fa-solid fa-layer-group"></i> Seluruh Kategori OLX Clone
          </div>
          <ul class="category-nav-expanded-list">
            <li>
              <a href="<?php echo htmlspecialchars(filterUrl(['c' => '']), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo($catFilter === null) ? 'active' : ''; ?>">
                <span class="cat-icon"><i class="fa-solid fa-border-all"></i></span>
                Semua Kategori
              </a>
            </li>
            <?php foreach ($categories as $cat): ?>
              <li>
                <a href="<?php echo htmlspecialchars(filterUrl(['c' => $cat['id']]), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo($catFilter === (int) $cat['id']) ? 'active' : ''; ?>">
                  <span class="cat-icon"><i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i></span>
                  <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

      </div>
    </div>
  </nav>


  <!-- ================================================================
       KONTEN UTAMA
       ================================================================ -->
  <main id="main-content" role="main">

    <!-- Flash Alert Notification -->
    <?php if (! empty($flashSuccess)): ?>
      <div class="container" style="padding-top: 16px;">
        <div class="alert alert-success" role="alert">
          <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
          <div class="alert-content">
            <?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?>
          </div>
          <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
        </div>
      </div>
    <?php endif; ?>

    <!-- ============ HERO BANNER ============ -->
    <?php if (! $hasActiveFilter): ?>
      <section class="hero-banner" aria-label="Banner utama">
        <div class="container">
          <h1>Temukan Barang Impianmu</h1>
          <p>Jual beli barang bekas maupun baru, lebih mudah dan cepat di seluruh Indonesia.</p>
        </div>
      </section>
    <?php endif; ?>


    <!-- ============ JELAJAHI KATEGORI ============ -->
    <!-- <?php if (! $hasActiveFilter): ?>
      <section class="section" aria-labelledby="heading-kategori">
        <div class="container">

          <div class="section-header">
            <h2 id="heading-kategori">Jelajahi Kategori</h2>
            <a href="index.php">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
              <a href="<?php echo htmlspecialchars(filterUrl(['c' => $cat['id']]), ENT_QUOTES, 'UTF-8'); ?>" class="category-card <?php echo($catFilter === (int) $cat['id']) ? 'active' : ''; ?>">
                <span class="cat-icon" aria-hidden="true"><i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i></span>
                <span class="cat-name"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></span>
              </a>
            <?php endforeach; ?>
          </div>

        </div>
      </section>
    <?php endif; ?> -->


    <!-- ============ DAFTAR IKLAN (DINAMIS DATABASE) ============ -->
    <section class="section" aria-labelledby="heading-iklan">
      <div class="container">

        <!-- Header Section & Sorting Selector -->
        <div class="section-header">
          <div>
            <h2 id="heading-iklan">
              <?php
                  if ($search !== '') {
                      echo 'Hasil Pencarian: "' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '"';
                  } elseif ($currentCategoryName) {
                      echo 'Iklan ' . htmlspecialchars($currentCategoryName, ENT_QUOTES, 'UTF-8');
                  } elseif (! empty($locFilter)) {
                      echo 'Iklan di ' . htmlspecialchars($locFilter, ENT_QUOTES, 'UTF-8');
                  } else {
                      echo 'Iklan Terbaru';
                  }
              ?>
            </h2>
            <small style="color: var(--text-muted); font-size: 0.85rem;">
              Menampilkan <?php echo $totalRecords; ?> iklan yang ditemukan
            </small>
          </div>

          <div class="filter-sort-wrapper">
            <label for="sort-select"><i class="fa-solid fa-arrow-down-wide-short"></i> Urutkan:</label>
            <select id="sort-select" class="filter-sort-select" aria-label="Urutkan iklan">
              <option value="<?php echo htmlspecialchars(filterUrl(['sort' => 'terbaru']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo($sort === 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
              <option value="<?php echo htmlspecialchars(filterUrl(['sort' => 'termurah']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo($sort === 'termurah') ? 'selected' : ''; ?>>Harga Terendah</option>
              <option value="<?php echo htmlspecialchars(filterUrl(['sort' => 'termahal']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo($sort === 'termahal') ? 'selected' : ''; ?>>Harga Tertinggi</option>
              <option value="<?php echo htmlspecialchars(filterUrl(['sort' => 'rekomendasi']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo($sort === 'rekomendasi') ? 'selected' : ''; ?>>Rekomendasi</option>
            </select>
          </div>
        </div>

        <!-- Filter Status Bar (Hanya tampil bila ada filter aktif) -->
        <?php if ($hasActiveFilter): ?>
          <div class="filter-status-bar">
            <div class="filter-status-info">
              <span class="filter-status-title"><i class="fa-solid fa-filter"></i> Filter Aktif:</span>
              <div class="filter-tags-list">
                <?php if ($search !== ''): ?>
                  <a href="<?php echo htmlspecialchars(filterUrl(['q' => '']), ENT_QUOTES, 'UTF-8'); ?>" class="filter-tag" title="Hapus pencarian kata kunci">
                    Pencarian: "<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                    <i class="fa-solid fa-xmark"></i>
                  </a>
                <?php endif; ?>

                <?php if ($catFilter !== null && $currentCategoryName): ?>
                  <a href="<?php echo htmlspecialchars(filterUrl(['c' => '']), ENT_QUOTES, 'UTF-8'); ?>" class="filter-tag" title="Hapus filter kategori">
                    Kategori: <?php echo htmlspecialchars($currentCategoryName, ENT_QUOTES, 'UTF-8'); ?>
                    <i class="fa-solid fa-xmark"></i>
                  </a>
                <?php endif; ?>

                <?php if (! empty($locFilter)): ?>
                  <a href="<?php echo htmlspecialchars(filterUrl(['loc' => '']), ENT_QUOTES, 'UTF-8'); ?>" class="filter-tag" title="Hapus filter lokasi">
                    Lokasi: <?php echo htmlspecialchars($locFilter, ENT_QUOTES, 'UTF-8'); ?>
                    <i class="fa-solid fa-xmark"></i>
                  </a>
                <?php endif; ?>

                <?php if ($sort !== 'terbaru'): ?>
                  <a href="<?php echo htmlspecialchars(filterUrl(['sort' => 'terbaru']), ENT_QUOTES, 'UTF-8'); ?>" class="filter-tag" title="Reset urutan ke terbaru">
                    Urutan: <?php echo ucfirst($sort); ?>
                    <i class="fa-solid fa-xmark"></i>
                  </a>
                <?php endif; ?>

                <a href="index.php" class="filter-clear-all"><i class="fa-solid fa-rotate-left"></i> Reset Semua</a>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Grid Iklan Dinamis -->
        <?php if ($totalAds > 0): ?>
          <div class="ad-grid">
            <?php foreach ($ads as $ad): ?>
              <article class="ad-card">
                <a href="detail.php?id=<?php echo (int) $ad['id']; ?>" aria-label="<?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?> - Rp <?php echo number_format($ad['price'], 0, ',', '.'); ?>">
                  <div class="ad-card-image">
                    <?php if (! empty($ad['image_path']) && file_exists(__DIR__ . '/' . $ad['image_path'])): ?>
                      <img src="<?php echo htmlspecialchars($ad['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    <?php else: ?>
                      <div class="img-placeholder" aria-hidden="true">
                        <i class="<?php echo ! empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon'], ENT_QUOTES, 'UTF-8') : 'fa-solid fa-box-open'; ?>"></i>
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
                      <time datetime="<?php echo substr($ad['created_at'], 0, 10); ?>"><?php echo date('d M', strtotime($ad['created_at'])); ?></time>
                    </div>
                  </div>
                </a>
              </article>
            <?php endforeach; ?>
          </div>

          <?php if ($totalRecords > $perPage): ?>
            <!-- Pagination Navigasi di Kiri Bawah Sesuai Permintaan -->
            <div class="pagination-wrapper pagination-left">
              <nav class="pagination" aria-label="Navigasi Halaman Iklan">
                <!-- Tombol Sebelumnya (Prev) -->
                <?php if ($page > 1): ?>
                  <a href="<?php echo htmlspecialchars(filterUrl(['page' => $page - 1]), ENT_QUOTES, 'UTF-8'); ?>" class="pagination-btn" aria-label="Halaman Sebelumnya" title="Halaman Sebelumnya">
                    <i class="fa-solid fa-chevron-left"></i>
                  </a>
                <?php else: ?>
                  <span class="pagination-btn disabled" aria-disabled="true" title="Halaman Pertama">
                    <i class="fa-solid fa-chevron-left"></i>
                  </span>
                <?php endif; ?>

                <!-- Angka Halaman -->
                <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);

                    if ($startPage > 1) {
                        echo '<a href="' . htmlspecialchars(filterUrl(['page' => 1]), ENT_QUOTES, 'UTF-8') . '" class="pagination-link">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="pagination-ellipsis">&hellip;</span>';
                        }
                    }

                    for ($p = $startPage; $p <= $endPage; $p++) {
                        if ($p === $page) {
                            echo '<span class="pagination-link active" aria-current="page">' . $p . '</span>';
                        } else {
                            echo '<a href="' . htmlspecialchars(filterUrl(['page' => $p]), ENT_QUOTES, 'UTF-8') . '" class="pagination-link">' . $p . '</a>';
                        }
                    }

                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="pagination-ellipsis">&hellip;</span>';
                        }
                        echo '<a href="' . htmlspecialchars(filterUrl(['page' => $totalPages]), ENT_QUOTES, 'UTF-8') . '" class="pagination-link">' . $totalPages . '</a>';
                    }
                ?>

                <!-- Tombol Selanjutnya (Next) -->
                <?php if ($page < $totalPages): ?>
                  <a href="<?php echo htmlspecialchars(filterUrl(['page' => $page + 1]), ENT_QUOTES, 'UTF-8'); ?>" class="pagination-btn" aria-label="Halaman Selanjutnya" title="Halaman Selanjutnya">
                    <i class="fa-solid fa-chevron-right"></i>
                  </a>
                <?php else: ?>
                  <span class="pagination-btn disabled" aria-disabled="true" title="Halaman Terakhir">
                    <i class="fa-solid fa-chevron-right"></i>
                  </span>
                <?php endif; ?>
              </nav>

              <div class="pagination-summary">
                Menampilkan <strong><?php echo($offset + 1); ?> - <?php echo min($offset + $perPage, $totalRecords); ?></strong> dari <strong><?php echo $totalRecords; ?></strong> iklan
              </div>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <!-- State Kosong (Empty State) Saat Tidak Ada Iklan yang Cocok -->
          <div class="empty-state-box">
            <div class="empty-state-icon">
              <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h3 class="empty-state-title">Iklan Tidak Ditemukan</h3>
            <p class="empty-state-desc">
              Maaf, kami tidak menemukan iklan yang sesuai dengan kata kunci atau kriteria filter yang Anda pilih. Coba gunakan kata kunci umum atau reset filter.
            </p>
            <div class="empty-state-actions">
              <a href="index.php" class="btn btn-outline">
                <i class="fa-solid fa-rotate-left"></i> Reset Filter
              </a>
              <a href="pasang-iklan.php" class="btn btn-solid-primary">
                <i class="fa-solid fa-plus"></i> Pasang Iklan Sekarang
              </a>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </section>


    <!-- ============ PROMO CTA — Ajak user pasang iklan ============ -->
    <section class="section" aria-labelledby="heading-promo">
      <div class="container">
        <div class="promo-section">
          <div>
            <h2 id="heading-promo">Punya Barang Nganggur?</h2>
            <p>Jadikan uang tunai! Pasang iklan gratis sekarang dan jangkau jutaan pembeli di seluruh Indonesia.</p>
          </div>
          <a href="pasang-iklan.php" class="btn btn-accent">
            <i class="fa-solid fa-plus"></i> Jual Sekarang
          </a>
        </div>
      </div>
    </section>


    <!-- ============ IKLAN REKOMENDASI (DINAMIS DARI DATABASE) ============ -->
    <?php if (! empty($recommendations)): ?>
      <section class="section" aria-labelledby="heading-rekomendasi">
        <div class="container">

          <div class="section-header">
            <h2 id="heading-rekomendasi">Rekomendasi Untukmu</h2>
            <a href="<?php echo htmlspecialchars(filterUrl(['sort' => 'rekomendasi']), ENT_QUOTES, 'UTF-8'); ?>">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <div class="ad-grid">
            <?php foreach ($recommendations as $rec): ?>
              <article class="ad-card">
                <a href="detail.php?id=<?php echo (int) $rec['id']; ?>" aria-label="<?php echo htmlspecialchars($rec['title'], ENT_QUOTES, 'UTF-8'); ?> - Rp <?php echo number_format($rec['price'], 0, ',', '.'); ?>">
                  <div class="ad-card-image">
                    <?php if (! empty($rec['image_path']) && file_exists(__DIR__ . '/' . $rec['image_path'])): ?>
                      <img src="<?php echo htmlspecialchars($rec['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($rec['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    <?php else: ?>
                      <div class="img-placeholder" aria-hidden="true">
                        <i class="<?php echo ! empty($rec['category_icon']) ? htmlspecialchars($rec['category_icon'], ENT_QUOTES, 'UTF-8') : 'fa-solid fa-box-open'; ?>"></i>
                      </div>
                    <?php endif; ?>
                    <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">
                      <i class="fa-regular fa-heart"></i>
                    </button>
                  </div>
                  <div class="ad-card-body">
                    <p class="ad-card-price">Rp <?php echo number_format($rec['price'], 0, ',', '.'); ?></p>
                    <h3 class="ad-card-title"><?php echo htmlspecialchars($rec['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="ad-card-meta">
                      <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($rec['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <time datetime="<?php echo substr($rec['created_at'], 0, 10); ?>"><?php echo date('d M', strtotime($rec['created_at'])); ?></time>
                    </div>
                  </div>
                </a>
              </article>
            <?php endforeach; ?>
          </div>

        </div>
      </section>
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

        <!-- Kolom 2: Kategori Populer (Dinamis dari Tabel categories) -->
        <div class="footer-col">
          <h3>Kategori Populer</h3>
          <ul>
            <?php foreach (array_slice($categories, 0, 4) as $popularCat): ?>
              <li>
                <a href="<?php echo htmlspecialchars(filterUrl(['c' => $popularCat['id']]), ENT_QUOTES, 'UTF-8'); ?>">
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
