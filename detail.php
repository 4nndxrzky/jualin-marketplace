<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

// Ambil kategori secara dinamis dari tabel categories
$stmtCat = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
$categories = $stmtCat->fetchAll();

// Ambil data iklan berdasarkan parameter ID di URL
$adId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$ad = null;
$adImages = [];

if ($adId > 0) {
    $stmtAd = $pdo->prepare("
        SELECT a.*, c.name AS category_name, c.icon AS category_icon,
               u.name AS seller_name, u.email AS seller_email, u.created_at AS seller_joined
        FROM ads a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmtAd->execute([$adId]);
    $ad = $stmtAd->fetch();

    if ($ad) {
        $stmtImgs = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ? ORDER BY id ASC");
        $stmtImgs->execute([$adId]);
        $adImages = $stmtImgs->fetchAll();
    }
}

// Fallback data contoh jika iklan tidak ditemukan
if (!$ad) {
    $ad = [
        'id'            => 1,
        'title'         => 'Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat',
        'price'         => 185000000,
        'location'      => 'Jakarta Selatan',
        'description'   => "Dijual cepat mobil keluarga idaman: Toyota Avanza 1.3 G Manual tahun 2020 warna Putih Mutiara.\n\nKondisi Kendaraan:\n- Odometer asli 38.500 km (slow moving), service record lengkap berkala di bengkel resmi Toyota Astra Motor.\n- Mesin halus, kering, no rembes, tarikan enteng, dan bensin sangat irit.\n- AC double blower sangat dingin dan berfungsi normal.\n- Kaki-kaki senyap tanpa bunyi, ban 4 buah masih tebal 85% (Bridgestone) + ban serep belum pernah turun.\n- Interior original fabric bersih, wangi, tidak merokok. Headunit touchscreen support Bluetooth & USB.\n- Bodi mulus 95%, cat original pabrik, bebas tabrakan besar dan bebas banjir.\n\nKelengkapan Dokumen & Legalitas:\n- STNK, BPKB, dan Faktur Pembelian asli lengkap di tangan.\n- Buku manual, buku servis, dan kunci kontak serep lengkap.\n- Pajak hidup panjang sampai Oktober 2026, plat B Jakarta Selatan (Ganjil).\n\nHarga nego santai dan sopan setelah cek unit di lokasi.",
        'category_name' => 'Mobil',
        'category_icon' => 'fa-solid fa-car',
        'seller_name'   => 'Rizky Pratama',
        'seller_email'  => 'rizky@email.com',
        'seller_joined' => '2024-01-15 10:00:00',
        'created_at'    => '2026-09-20 14:30:00'
    ];
}

$pageTitle = htmlspecialchars($ad['title']) . " — OLX Clone";
$priceFormatted = "Rp " . number_format($ad['price'], 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS (DINAMIS) ==================== -->
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($ad['description']), 0, 160)) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($ad['title']) ?>, jual beli online, OLX Clone">
  <meta name="author" content="<?= htmlspecialchars($ad['seller_name'] ?? 'Penjual OLX Clone') ?>">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://olxclone.local/detail.php?id=<?= (int) $ad['id'] ?>">

  <!-- ==================== OPEN GRAPH ==================== -->
  <meta property="og:type" content="product">
  <meta property="og:title" content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags($ad['description']), 0, 120)) ?>">
  <meta property="og:url" content="https://olxclone.local/detail.php?id=<?= (int) $ad['id'] ?>">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">
  <meta property="product:price:amount" content="<?= (float) $ad['price'] ?>">
  <meta property="product:price:currency" content="IDR">

  <!-- ==================== FAVICON ==================== -->
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">

  <!-- ==================== FONT AWESOME ICONS ==================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- ==================== CSS EXTERNAL ==================== -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ================================================================
       HEADER — Logo, Search, Lokasi, Auth (Konsisten dengan index.php)
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          OLX<span>Clone</span>
        </a>

        <!-- Lokasi Selector -->
        <button class="location-selector" aria-label="Pilih lokasi" type="button">
          <i class="fa-solid fa-location-dot"></i> <span><?= htmlspecialchars($ad['location'] ?? 'Indonesia') ?></span> <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
        </button>

        <!-- Search Bar -->
        <form class="search-form" action="search.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, laptop, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth Actions — Dinamis dari Status Login users -->
        <div class="header-actions">
          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-menu-wrapper">
              <button type="button" class="user-menu-btn" aria-haspopup="true" aria-expanded="false">
                <span class="user-avatar-sm"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></span>
                <span class="user-menu-name"><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
              </button>
              <div class="user-dropdown" role="menu">
                <div style="padding: 10px 16px; border-bottom: 1px solid var(--gray-200);">
                  <strong style="display: block; font-size: 0.88rem; color: var(--text-primary);"><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <small style="color: var(--text-muted); font-size: 0.75rem;"><?= htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <a href="pasang-iklan.php" class="dropdown-item" role="menuitem">
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
       NAVIGASI KATEGORI (Konsisten dengan index.php)
       ================================================================ -->
  <nav class="category-nav" aria-label="Navigasi kategori">
    <div class="container">
      <ul class="category-nav-list">
        <?php foreach ($categories as $cat): ?>
          <li>
            <a href="kategori.php?c=<?= (int) $cat['id'] ?>">
              <span class="cat-icon"><i class="<?= htmlspecialchars($cat['icon']) ?>"></i></span>
              <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>


  <!-- ================================================================
       BREADCRUMB (SEO & User Navigation)
       ================================================================ -->
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <ol class="breadcrumb">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="kategori.php?c=<?= (int) ($ad['category_id'] ?? 1) ?>"><?= htmlspecialchars($ad['category_name'] ?? 'Kategori') ?></a></li>
        <li aria-current="page"><?= htmlspecialchars($ad['title']) ?></li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       NOTIFIKASI FLASH SUKSES
       ================================================================ -->
  <?php if (!empty($flashSuccess)): ?>
    <div class="container" style="padding-top: 10px;">
      <div class="alert alert-success" role="alert">
        <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
        <div class="alert-content">
          <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
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
            <?php if (!empty($adImages) && file_exists(__DIR__ . '/' . $adImages[0]['image_path'])): ?>
              <img src="<?= htmlspecialchars($adImages[0]['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8') ?>" id="main-gallery-img" style="width: 100%; max-height: 480px; object-fit: contain; background: #000;">
            <?php else: ?>
              <div class="gallery-placeholder" aria-hidden="true">
                <i class="<?= !empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon']) : 'fa-solid fa-box-open' ?>" style="font-size: 5rem; color: var(--primary);"></i>
                <span>Foto Tampilan Produk</span>
              </div>
            <?php endif; ?>
            <span class="ad-card-badge">Aktif</span>
            <span class="gallery-counter" aria-label="Jumlah foto">
              <i class="fa-solid fa-camera"></i> 1 / <?= max(1, count($adImages)) ?>
            </span>
          </figure>

          <!-- Thumbnail Strip (ad_images table) -->
          <?php if (!empty($adImages)): ?>
            <ul class="gallery-thumbs" role="tablist" aria-label="Thumbnail foto iklan">
              <?php foreach ($adImages as $idx => $img): ?>
                <li class="gallery-thumb-item <?= $idx === 0 ? 'active' : '' ?>" role="tab" aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>" tabindex="0" title="Foto <?= $idx + 1 ?>">
                  <img src="<?= htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Thumbnail <?= $idx + 1 ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <ul class="gallery-thumbs" role="tablist" aria-label="Thumbnail foto iklan">
              <li class="gallery-thumb-item active" role="tab" aria-selected="true" tabindex="0" title="Foto 1: Utama">
                <i class="<?= !empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon']) : 'fa-solid fa-box-open' ?>"></i>
              </li>
              <li class="gallery-thumb-item" role="tab" aria-selected="false" tabindex="-1" title="Foto 2: Tambahan">
                <i class="fa-solid fa-image"></i>
              </li>
              <li class="gallery-thumb-item" role="tab" aria-selected="false" tabindex="-1" title="Foto 3: Tambahan">
                <i class="fa-solid fa-image"></i>
              </li>
            </ul>
          <?php endif; ?>

        </section>


        <!-- ===== 2. TOOLBAR DETAIL (Bagikan, Laporkan, Wishlist) ===== -->
        <div class="detail-toolbar">
          <div class="detail-toolbar-left">
            <span>ID Iklan: <strong>#<?= str_pad((string)$ad['id'], 5, '0', STR_PAD_LEFT) ?></strong></span>
            <span>•</span>
            <time datetime="<?= substr($ad['created_at'], 0, 10) ?>">Diposting: <?= date('d F Y', strtotime($ad['created_at'])) ?></time>
          </div>
          <div class="detail-toolbar-right">
            <button type="button" class="btn-icon-action" aria-label="Bagikan iklan ini">
              <i class="fa-solid fa-share-nodes"></i> Bagikan
            </button>
            <button type="button" class="btn-icon-action" aria-label="Simpan ke favorit">
              <i class="fa-regular fa-heart"></i> Favorit
            </button>
            <button type="button" class="btn-icon-action" aria-label="Laporkan iklan ini">
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
              <dd><a href="kategori.php?c=<?= (int) ($ad['category_id'] ?? 1) ?>"><?= htmlspecialchars($ad['category_name'] ?? 'Umum') ?></a></dd>
            </div>
            <div class="spec-item">
              <dt>Harga</dt>
              <dd><?= $priceFormatted ?></dd>
            </div>
            <div class="spec-item">
              <dt>Lokasi</dt>
              <dd><?= htmlspecialchars($ad['location'] ?? 'Indonesia') ?></dd>
            </div>
            <div class="spec-item">
              <dt>Status Listing</dt>
              <dd>Tersedia / Aktif</dd>
            </div>
            <div class="spec-item">
              <dt>Penjual</dt>
              <dd><?= htmlspecialchars($ad['seller_name'] ?? 'Penjual Terpercaya') ?></dd>
            </div>
            <div class="spec-item">
              <dt>Tanggal Pasang</dt>
              <dd><?= date('d M Y', strtotime($ad['created_at'])) ?></dd>
            </div>
          </dl>
        </section>


        <!-- ===== 4. DESKRIPSI IKLAN (ads.description) ===== -->
        <section class="detail-box" aria-labelledby="heading-desc">
          <h2 id="heading-desc" class="detail-box-title">Deskripsi Lengkap</h2>

          <div class="description-body">
            <?= nl2br(htmlspecialchars($ad['description'])) ?>
          </div>
        </section>


        <!-- ===== 5. LOKASI PENJUAL (ads.location) ===== -->
        <section class="detail-box" aria-labelledby="heading-loc">
          <h2 id="heading-loc" class="detail-box-title">Lokasi Barang</h2>

          <div class="location-box">
            <p class="location-info">
              <i class="fa-solid fa-location-dot"></i> <span><?= htmlspecialchars($ad['location'] ?? 'Indonesia') ?></span>
            </p>
            <div class="map-placeholder" aria-label="Peta lokasi perkiraan">
              <i class="fa-solid fa-map-location-dot" style="font-size: 2.5rem; color: var(--primary);"></i>
              <p>Area Perkiraan: <?= htmlspecialchars($ad['location'] ?? 'Indonesia') ?></p>
              <small>(Lokasi presisi akan diberikan penjual saat janjian COD)</small>
            </div>
          </div>
        </section>

      </article>


      <!-- ==================== KOLOM KANAN (SIDEBAR STICKY) ==================== -->
      <aside class="detail-sidebar" aria-label="Ringkasan Iklan dan Kontak Penjual">

        <!-- Card 1: Harga & Judul -->
        <div class="sidebar-card sidebar-price-card">
          <p class="price-amount"><?= $priceFormatted ?></p>
          <h1 class="ad-title"><?= htmlspecialchars($ad['title']) ?></h1>
          <div class="ad-meta-info">
            <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($ad['location'] ?? 'Indonesia') ?></span>
            <time datetime="<?= substr($ad['created_at'], 0, 10) ?>"><?= date('d M Y', strtotime($ad['created_at'])) ?></time>
          </div>
        </div>

        <!-- Card 2: Profil Penjual (users table) -->
        <div class="sidebar-card seller-card">
          <div class="seller-profile">
            <div class="seller-avatar" aria-hidden="true">
              <?= strtoupper(substr($ad['seller_name'] ?? 'P', 0, 1)) ?>
            </div>
            <div class="seller-info">
              <h3 class="seller-name">
                <?= htmlspecialchars($ad['seller_name'] ?? 'Penjual Terpercaya') ?>
                <span class="badge badge-verified" title="Identitas terverifikasi">
                  <i class="fa-solid fa-circle-check"></i> Terverifikasi
                </span>
              </h3>
              <p class="seller-member-since">Member sejak <?= !empty($ad['seller_joined']) ? date('M Y', strtotime($ad['seller_joined'])) : '2024' ?></p>
            </div>
          </div>

          <!-- Aksi Kontak -->
          <div class="seller-actions">
            <a href="#chat" class="btn btn-solid-primary btn-block">
              <i class="fa-solid fa-comments"></i> Chat Penjual
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20<?= urlencode($ad['seller_name'] ?? 'Penjual') ?>,%20saya%20tertarik%20dengan%20iklan%20<?= urlencode($ad['title']) ?>%20di%20OLX%20Clone" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp btn-block">
              <i class="fa-brands fa-whatsapp"></i> Hubungi via WhatsApp
            </a>
            <button type="button" class="btn btn-outline btn-block" onclick="this.innerHTML='<i class=\'fa-solid fa-phone\'></i> 0812-3456-7890'">
              <i class="fa-solid fa-phone"></i> Tampilkan Telepon
            </button>
          </div>

          <a href="index.php" class="seller-profile-link">
            Lihat semua iklan penjual ini →
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
            <li>Waspada terhadap harga yang jauh di bawah pasaran.</li>
          </ul>
        </div>

      </aside>

    </div>


    <!-- ================================================================
         IKLAN TERKAIT (RELATED ADS)
         ================================================================ -->
    <section class="section" aria-labelledby="heading-related">
      <div class="section-header">
        <h2 id="heading-related">Iklan Terkait Lainnya</h2>
        <a href="kategori.php?c=<?= (int) ($ad['category_id'] ?? 1) ?>">Lihat Lainnya →</a>
      </div>

      <div class="ad-grid">

        <!-- Card 1 -->
        <article class="ad-card">
          <a href="detail.php?id=1" aria-label="Honda Brio Satya - Rp 142.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-car"></i></div>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 142.000.000</p>
              <h3 class="ad-card-title">Honda Brio Satya E CVT 2021 Abu-abu Metalik KM Rendah</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Jakarta Barat</span>
                <time datetime="2026-09-18">18 Sep</time>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 2 -->
        <article class="ad-card">
          <a href="detail.php?id=2" aria-label="Mitsubishi Xpander Ultimate - Rp 215.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-car-side"></i></div>
              <span class="ad-card-badge">Baru</span>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 215.000.000</p>
              <h3 class="ad-card-title">Mitsubishi Xpander Ultimate AT 2020 Hitam Full Original</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Tangerang</span>
                <time datetime="2026-09-17">17 Sep</time>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 3 -->
        <article class="ad-card">
          <a href="detail.php?id=3" aria-label="Daihatsu Sigra 1.2 R - Rp 128.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-car"></i></div>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 128.000.000</p>
              <h3 class="ad-card-title">Daihatsu Sigra 1.2 R Deluxe MT 2022 Silver Siap Pakai</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Depok</span>
                <time datetime="2026-09-16">16 Sep</time>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 4 -->
        <article class="ad-card">
          <a href="detail.php?id=4" aria-label="Suzuki Ertiga GL - Rp 165.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-car-side"></i></div>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 165.000.000</p>
              <h3 class="ad-card-title">Suzuki Ertiga GL Automatic 2019 Merah Maroon Antik</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Bekasi</span>
                <time datetime="2026-09-15">15 Sep</time>
              </div>
            </div>
          </a>
        </article>

      </div>
    </section>

  </main>


  <!-- ================================================================
       FOOTER (Konsisten dengan index.php)
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
            <li><a href="kategori.php?c=1">Mobil Bekas</a></li>
            <li><a href="kategori.php?c=2">Motor Bekas</a></li>
            <li><a href="kategori.php?c=3">Rumah & Apartemen</a></li>
            <li><a href="kategori.php?c=4">HP & Laptop</a></li>
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
