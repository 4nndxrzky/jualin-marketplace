<?php
    session_start();
    require_once __DIR__ . '/koneksi.php';

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_success']);

    // --------------------------------------------------------------------------
    // 1. Ambil Kategori & Lokasi Dinamis dari Database
    // --------------------------------------------------------------------------
    $stmtCat    = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
    $categories = $stmtCat->fetchAll();

    $stmtLoc = $pdo->query("
    SELECT DISTINCT location
    FROM ads
    WHERE location IS NOT NULL AND TRIM(location) != ''
    ORDER BY location ASC
");
    $locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

    // --------------------------------------------------------------------------
    // 2. Ambil Data Iklan Berdasarkan Parameter ID di URL
    // --------------------------------------------------------------------------
    $adId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
    $ad   = null;

    if ($adId > 0) {
    $stmtAd = $pdo->prepare("
        SELECT a.*, c.name AS category_name, c.icon AS category_icon,
               u.id AS seller_user_id, u.name AS seller_name, u.email AS seller_email,
               u.whatsapp AS seller_whatsapp, u.created_at AS seller_joined
        FROM ads a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmtAd->execute([$adId]);
    $ad = $stmtAd->fetch();
    }

    // Fallback dinamis jika iklan tidak ditemukan atau parameter id tidak diberikan
    if (! $ad) {
    $stmtFallback = $pdo->query("
        SELECT a.*, c.name AS category_name, c.icon AS category_icon,
               u.id AS seller_user_id, u.name AS seller_name, u.email AS seller_email,
               u.whatsapp AS seller_whatsapp, u.created_at AS seller_joined
        FROM ads a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.id DESC
        LIMIT 1
    ");
    $ad = $stmtFallback->fetch();
    }

    // Ambil seluruh foto iklan dari tabel ad_images
    $adImages = [];
    if ($ad) {
    $stmtImgs = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ? ORDER BY id ASC");
    $stmtImgs->execute([$ad['id']]);
    $adImages = $stmtImgs->fetchAll();
    }

    // --------------------------------------------------------------------------
    // 3. Format Kontak WhatsApp & Telepon Penjual (Dinamis dari Kolom whatsapp)
    // --------------------------------------------------------------------------
    $sellerWa = trim($ad['seller_whatsapp'] ?? '');
    if (empty($sellerWa)) {
    $sellerWa = '085693557069'; // Fallback default nomor penjual jika belum diset
    }

    // Format nomor internasional untuk link wa.me (diawali 62 dan angka saja)
    $waClean = preg_replace('/[^0-9]/', '', $sellerWa);
    if (str_starts_with($waClean, '0')) {
    $waClean = '62' . substr($waClean, 1);
    } elseif (str_starts_with($waClean, '8')) {
    $waClean = '62' . $waClean;
    }

    // Format tampilan lokal yang rapi untuk tombol Tampilkan Telepon (contoh: 0856-9355-7069)
    $waDisplay  = $sellerWa;
    $digitsOnly = preg_replace('/[^0-9]/', '', $sellerWa);
    if (strlen($digitsOnly) >= 10 && preg_match('/^(\d{4})(\d{4})(\d{3,5})$/', $digitsOnly, $matches)) {
    $waDisplay = "{$matches[1]}-{$matches[2]}-{$matches[3]}";
    }

    // Pesan pra-isi WhatsApp yang sopan & menyertakan rincian iklan
    $waMessage = "Halo " . ($ad['seller_name'] ?? 'Penjual') . ", saya tertarik dengan iklan \"" . $ad['title'] . "\" (ID: #" . str_pad((string) $ad['id'], 5, '0', STR_PAD_LEFT) . ") di Jualin seharga Rp " . number_format($ad['price'], 0, ',', '.') . ". Apakah masih tersedia?";
    $waUrl     = "https://wa.me/{$waClean}?text=" . rawurlencode($waMessage);

    // --------------------------------------------------------------------------
    // 4. Query Iklan Terkait Dinamis (Related Ads dari Database)
    // --------------------------------------------------------------------------
    $stmtRelated = $pdo->prepare("
        SELECT a.*, c.name AS category_name, c.icon AS category_icon,
               (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY id ASC LIMIT 1) AS image_path
        FROM ads a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.category_id = ? AND a.id != ?
        ORDER BY a.created_at DESC
        LIMIT 4
    ");
    $stmtRelated->execute([$ad['category_id'] ?? 0, $ad['id'] ?? 0]);
    $relatedAds = $stmtRelated->fetchAll();

    $pageTitle      = htmlspecialchars($ad['title'] ?? 'Detail Iklan', ENT_QUOTES, 'UTF-8') . " — Jualin";
    $priceFormatted = "Rp " . number_format($ad['price'] ?? 0, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS (DINAMIS) ==================== -->
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($ad['description'] ?? ''), 0, 160), ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($ad['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>, jual beli online, Jualin">
  <meta name="author" content="<?php echo htmlspecialchars($ad['seller_name'] ?? 'Penjual Jualin', ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://jualin.local/detail.php?id=<?php echo (int) ($ad['id'] ?? 1); ?>">

  <!-- ==================== OPEN GRAPH ==================== -->
  <meta property="og:type" content="product">
  <meta property="og:title" content="<?php echo $pageTitle; ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($ad['description'] ?? ''), 0, 120), ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:url" content="https://jualin.local/detail.php?id=<?php echo (int) ($ad['id'] ?? 1); ?>">
  <meta property="og:site_name" content="Jualin">
  <meta property="og:locale" content="id_ID">
  <meta property="product:price:amount" content="<?php echo (float) ($ad['price'] ?? 0); ?>">
  <meta property="product:price:currency" content="IDR">

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
       HEADER — Logo, Search, Lokasi, Auth
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="Jualin - Halaman Utama">
          Jual<span>in</span>
        </a>

        <!-- Lokasi Selector Dinamis -->
        <div class="location-dropdown-wrapper">
          <button class="location-selector" aria-label="Pilih lokasi" type="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-location-dot"></i>
            <span><?php echo htmlspecialchars($ad['location'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?></span>
            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
          </button>
          <div class="location-menu" role="menu">
            <a href="index.php" class="location-item">
              <i class="fa-solid fa-earth-asia"></i> Semua Indonesia
            </a>
            <?php foreach ($locations as $loc): ?>
              <a href="index.php?loc=<?php echo urlencode($loc); ?>" class="location-item <?php echo(($ad['location'] ?? '') === $loc) ? 'active' : ''; ?>">
                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Search Bar -->
        <form class="search-form" action="index.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di Jualin</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, properti, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth Actions — Dinamis dari Status Login users -->
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
       BREADCRUMB (Kategori Navbar di Halaman Detail Ditiadakan)
       ================================================================ -->
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <ol class="breadcrumb">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="index.php?c=<?php echo (int) ($ad['category_id'] ?? 1); ?>"><?php echo htmlspecialchars($ad['category_name'] ?? 'Kategori', ENT_QUOTES, 'UTF-8'); ?></a></li>
        <li aria-current="page"><?php echo htmlspecialchars($ad['title'] ?? 'Detail Iklan', ENT_QUOTES, 'UTF-8'); ?></li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       NOTIFIKASI FLASH SUKSES
       ================================================================ -->
  <?php if (! empty($flashSuccess)): ?>
    <div class="container" style="padding-top: 10px;">
      <div class="alert alert-success" role="alert">
        <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
        <div class="alert-content">
          <?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
      </div>
    </div>
  <?php endif; ?>


  <!-- ================================================================
       KONTEN UTAMA DETAIL IKLAN (2 Kolom: Kiri Konten, Kanan Sidebar)
       ================================================================ -->
  <main id="main-content" class="container" role="main">
    <div class="detail-layout">

      <!-- ==================== KOLOM KIRI (KONTEN UTAMA) ==================== -->
      <article class="detail-content">

        <!-- ===== 1. GALERI FOTO (ad_images) ===== -->
        <section class="gallery-container" aria-label="Galeri Gambar Iklan">

          <!-- Foto Utama -->
          <figure class="gallery-main">
            <?php if (! empty($adImages) && file_exists(__DIR__ . '/' . $adImages[0]['image_path'])): ?>
              <img src="<?php echo htmlspecialchars($adImages[0]['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($ad['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" id="main-gallery-img" style="width: 100%; max-height: 480px; object-fit: contain; background: #000;">
            <?php else: ?>
              <div class="gallery-placeholder" aria-hidden="true">
                <i class="<?php echo ! empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon'], ENT_QUOTES, 'UTF-8') : 'fa-solid fa-box-open'; ?>" style="font-size: 5rem; color: var(--primary);"></i>
                <span>Foto Tampilan Produk</span>
              </div>
            <?php endif; ?>
            <span class="ad-card-badge">Aktif</span>
            <span class="gallery-counter" aria-label="Jumlah foto">
              <i class="fa-solid fa-camera"></i> 1 / <?php echo max(1, count($adImages)); ?>
            </span>
          </figure>

          <!-- Thumbnail Strip (ad_images table) -->
          <?php if (! empty($adImages)): ?>
            <ul class="gallery-thumbs" role="tablist" aria-label="Thumbnail foto iklan">
              <?php foreach ($adImages as $idx => $img): ?>
                <li class="gallery-thumb-item <?php echo($idx === 0) ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo($idx === 0) ? 'true' : 'false'; ?>" tabindex="0" title="Foto <?php echo $idx + 1; ?>">
                  <img src="<?php echo htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Thumbnail <?php echo $idx + 1; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

        </section>


        <!-- ===== 2. TOOLBAR DETAIL (Bagikan, Laporkan, Wishlist) ===== -->
        <div class="detail-toolbar">
          <div class="detail-toolbar-left">
            <span>ID Iklan: <strong>#<?php echo str_pad((string) ($ad['id'] ?? 0), 5, '0', STR_PAD_LEFT); ?></strong></span>
            <span>&bull;</span>
            <time datetime="<?php echo substr($ad['created_at'] ?? '2026-09-20', 0, 10); ?>">Diposting: <?php echo date('d F Y', strtotime($ad['created_at'] ?? 'now')); ?></time>
          </div>
          <div class="detail-toolbar-right">
            <button type="button" class="btn-icon-action" aria-label="Bagikan iklan ini" onclick="if(navigator.share){navigator.share({title:document.title,url:window.location.href});}else{navigator.clipboard.writeText(window.location.href);alert('Tautan iklan berhasil disalin!');}">
              <i class="fa-solid fa-share-nodes"></i> Bagikan
            </button>
            <button type="button" class="btn-icon-action" aria-label="Simpan ke favorit" onclick="const i=this.querySelector('i');i.classList.toggle('fa-regular');i.classList.toggle('fa-solid');i.style.color=i.classList.contains('fa-solid')?'var(--danger)':'';">
              <i class="fa-regular fa-heart"></i> Favorit
            </button>
            <button type="button" class="btn-icon-action" aria-label="Laporkan iklan ini" onclick="alert('Terima kasih, laporan Anda telah kami terima untuk ditinjau tim moderasi.');">
              <i class="fa-regular fa-flag"></i> Laporkan
            </button>
          </div>
        </div>


        <!-- ===== 3. SPESIFIKASI & DETAIL LENGKAP ===== -->
        <section class="detail-box" aria-labelledby="heading-specs">
          <h2 id="heading-specs" class="detail-box-title">Detail & Spesifikasi</h2>

          <dl class="specs-grid">
            <div class="spec-item">
              <dt>Kategori</dt>
              <dd><a href="index.php?c=<?php echo (int) ($ad['category_id'] ?? 1); ?>"><?php echo htmlspecialchars($ad['category_name'] ?? 'Umum', ENT_QUOTES, 'UTF-8'); ?></a></dd>
            </div>
            <div class="spec-item">
              <dt>Harga</dt>
              <dd><?php echo $priceFormatted; ?></dd>
            </div>
            <div class="spec-item">
              <dt>Lokasi</dt>
              <dd><?php echo htmlspecialchars($ad['location'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div class="spec-item">
              <dt>Status Listing</dt>
              <dd>Tersedia / Aktif</dd>
            </div>
            <div class="spec-item">
              <dt>Penjual</dt>
              <dd><?php echo htmlspecialchars($ad['seller_name'] ?? 'Penjual Terpercaya', ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div class="spec-item">
              <dt>Tanggal Pasang</dt>
              <dd><?php echo date('d M Y', strtotime($ad['created_at'] ?? 'now')); ?></dd>
            </div>
          </dl>
        </section>


        <!-- ===== 4. DESKRIPSI IKLAN (ads.description) ===== -->
        <section class="detail-box" aria-labelledby="heading-desc">
          <h2 id="heading-desc" class="detail-box-title">Deskripsi Lengkap</h2>

          <div class="description-body">
            <?php echo nl2br(htmlspecialchars($ad['description'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
          </div>
        </section>


        <!-- ===== 5. LOKASI PENJUAL (ads.location) ===== -->
        <section class="detail-box" aria-labelledby="heading-loc">
          <h2 id="heading-loc" class="detail-box-title">Lokasi Barang</h2>

          <div class="location-box">
            <p class="location-info">
              <i class="fa-solid fa-location-dot"></i> <span><?php echo htmlspecialchars($ad['location'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?></span>
            </p>
            <div class="map-placeholder" aria-label="Peta lokasi perkiraan">
              <i class="fa-solid fa-map-location-dot" style="font-size: 2.5rem; color: var(--primary);"></i>
              <p>Area Perkiraan: <?php echo htmlspecialchars($ad['location'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?></p>
              <small>(Lokasi presisi akan diberikan penjual saat janjian COD)</small>
            </div>
          </div>
        </section>

      </article>


      <!-- ==================== KOLOM KANAN (SIDEBAR STICKY) ==================== -->
      <aside class="detail-sidebar" aria-label="Ringkasan Iklan dan Kontak Penjual">

        <!-- Card 1: Harga & Judul -->
        <div class="sidebar-card sidebar-price-card">
          <p class="price-amount"><?php echo $priceFormatted; ?></p>
          <h1 class="ad-title"><?php echo htmlspecialchars($ad['title'] ?? 'Iklan', ENT_QUOTES, 'UTF-8'); ?></h1>
          <div class="ad-meta-info">
            <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($ad['location'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?></span>
            <time datetime="<?php echo substr($ad['created_at'] ?? '2026-09-20', 0, 10); ?>"><?php echo date('d M Y', strtotime($ad['created_at'] ?? 'now')); ?></time>
          </div>
        </div>

        <!-- Card 2: Profil Penjual (users table + nomor whatsapp) -->
        <div class="sidebar-card seller-card">
          <div class="seller-profile">
            <a href="penjual.php?id=<?php echo (int) ($ad['user_id'] ?? $ad['seller_user_id'] ?? 0); ?>" class="seller-avatar" style="text-decoration: none;" aria-label="Profil Penjual">
              <?php echo strtoupper(substr($ad['seller_name'] ?? 'P', 0, 1)); ?>
            </a>
            <div class="seller-info">
              <h3 class="seller-name">
                <a href="penjual.php?id=<?php echo (int) ($ad['user_id'] ?? $ad['seller_user_id'] ?? 0); ?>" style="color: inherit; text-decoration: none;">
                  <?php echo htmlspecialchars($ad['seller_name'] ?? 'Penjual Terpercaya', ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <span class="badge badge-verified" title="Identitas terverifikasi">
                  <i class="fa-solid fa-circle-check"></i> Terverifikasi
                </span>
              </h3>
              <p class="seller-member-since">Member sejak <?php echo ! empty($ad['seller_joined']) ? date('M Y', strtotime($ad['seller_joined'])) : '2024'; ?></p>
            </div>
          </div>

          <!-- Aksi Kontak Dinamis (WhatsApp & Telepon dari database) -->
          <div class="seller-actions">
            <button type="button" class="btn btn-outline btn-block" id="btn-show-phone" data-phone="<?php echo htmlspecialchars($waDisplay, ENT_QUOTES, 'UTF-8'); ?>" data-phone-raw="<?php echo htmlspecialchars($sellerWa, ENT_QUOTES, 'UTF-8'); ?>">
              <i class="fa-solid fa-phone"></i> <span>Tampilkan Telepon</span>
            </button>
            <a href="<?php echo htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-solid-primary btn-block">
              <i class="fa-solid fa-comments"></i> Chat Penjual
            </a>
          </div>

          <a href="penjual.php?id=<?php echo (int) ($ad['user_id'] ?? $ad['seller_user_id'] ?? 0); ?>" class="seller-profile-link">
            Lihat semua iklan penjual ini <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>

        <!-- Card 3: Tips Keamanan Bertransaksi -->
        <div class="sidebar-card safety-card">
          <div class="safety-header">
            <i class="fa-solid fa-shield-halved" style="color: var(--primary); font-size: 1.2rem;"></i>
            <h3>Tips Transaksi Aman</h3>
          </div>
          <ul class="safety-list">
            <li>Ketemuan langsung dengan penjual di tempat umum dan ramai.</li>
            <li>Periksa kondisi fisik barang dan kelengkapan dokumen asli.</li>
            <li>Jangan pernah mengirimkan uang muka (DP) sebelum bertemu.</li>
            <li>Waspada terhadap harga yang jauh di bawah pasaran wajar.</li>
          </ul>
        </div>

      </aside>

    </div>


    <!-- ================================================================
         IKLAN TERKAIT (RELATED ADS DARI DATABASE - 0 HARDCODED)
         ================================================================ -->
    <?php if (! empty($relatedAds)): ?>
      <section class="section" aria-labelledby="heading-related">
        <div class="section-header">
          <h2 id="heading-related">Iklan Terkait Lainnya</h2>
          <a href="index.php?c=<?php echo (int) ($ad['category_id'] ?? 1); ?>">Lihat Lainnya <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="ad-grid">
          <?php foreach ($relatedAds as $relAd): ?>
            <article class="ad-card">
              <a href="detail.php?id=<?php echo (int) $relAd['id']; ?>" aria-label="<?php echo htmlspecialchars($relAd['title'], ENT_QUOTES, 'UTF-8'); ?> - Rp <?php echo number_format($relAd['price'], 0, ',', '.'); ?>">
                <div class="ad-card-image">
                  <?php if (! empty($relAd['image_path']) && file_exists(__DIR__ . '/' . $relAd['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($relAd['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($relAd['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                  <?php else: ?>
                    <div class="img-placeholder" aria-hidden="true">
                      <i class="<?php echo ! empty($relAd['category_icon']) ? htmlspecialchars($relAd['category_icon'], ENT_QUOTES, 'UTF-8') : 'fa-solid fa-box-open'; ?>"></i>
                    </div>
                  <?php endif; ?>
                  <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">
                    <i class="fa-regular fa-heart"></i>
                  </button>
                </div>
                <div class="ad-card-body">
                  <p class="ad-card-price">Rp <?php echo number_format($relAd['price'], 0, ',', '.'); ?></p>
                  <h3 class="ad-card-title"><?php echo htmlspecialchars($relAd['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                  <div class="ad-card-meta">
                    <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($relAd['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <time datetime="<?php echo substr($relAd['created_at'], 0, 10); ?>"><?php echo date('d M', strtotime($relAd['created_at'])); ?></time>
                  </div>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
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
          <h3>Jualin</h3>
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
                <a href="index.php?c=<?php echo (int) $popularCat['id']; ?>">
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
        <p>&copy; 2026 Jualin. Dibuat untuk belajar di Kelas Fullstack Codepolitan.</p>
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
