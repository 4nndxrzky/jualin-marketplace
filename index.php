<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

// Ambil kategori secara dinamis dari tabel categories
$stmtCat = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
$categories = $stmtCat->fetchAll();

// Ambil iklan terbaru dari database
$stmtAds = $pdo->query("
    SELECT a.*, c.name AS category_name, c.icon AS category_icon,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY id ASC LIMIT 1) AS image_path
    FROM ads a
    LEFT JOIN categories c ON a.category_id = c.id
    ORDER BY a.created_at DESC
    LIMIT 8
");
$latestAds = $stmtAds->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS ==================== -->
  <title>OLX Clone — Jual Beli Online Mudah & Terpercaya</title>
  <meta name="description" content="Temukan ribuan iklan barang bekas dan baru di OLX Clone. Jual beli mobil, properti, elektronik, dan lainnya dengan mudah, cepat, dan terpercaya.">
  <meta name="keywords" content="jual beli online, marketplace, iklan gratis, barang bekas, OLX, beli murah, jual cepat">
  <meta name="author" content="OLX Clone">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://olxclone.local/">

  <!-- ==================== OPEN GRAPH (Facebook/WhatsApp) ==================== -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="OLX Clone — Jual Beli Online Mudah & Terpercaya">
  <meta property="og:description" content="Temukan ribuan iklan barang bekas dan baru. Jual beli mobil, properti, elektronik dengan mudah dan terpercaya.">
  <meta property="og:url" content="https://olxclone.local/">
  <meta property="og:image" content="https://olxclone.local/assets/images/og-preview.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">

  <!-- ==================== TWITTER CARD ==================== -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="OLX Clone — Jual Beli Online Mudah & Terpercaya">
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
        "target": "https://olxclone.local/search?q={search_term_string}",
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

  <!-- ==================== FONT AWESOME ICONS ==================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- ==================== CSS EXTERNAL ==================== -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ================================================================
       HEADER — Logo, Search, Lokasi, Auth
       Tabel terkait: users (login/register)
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          OLX<span>Clone</span>
        </a>

        <!-- Lokasi -->
        <button class="location-selector" aria-label="Pilih lokasi" type="button">
          <i class="fa-solid fa-location-dot"></i> <span>Indonesia</span> <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
        </button>

        <!-- Search Bar -->
        <form class="search-form" action="search.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, laptop, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth — Status Login dari Tabel users -->
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
       NAVIGASI KATEGORI — Tabel: categories (id, name, icon)
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
       KONTEN UTAMA
       ================================================================ -->
  <main id="main-content" role="main">

    <?php if (!empty($flashSuccess)): ?>
      <div class="container" style="padding-top: 16px;">
        <div class="alert alert-success" role="alert">
          <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
          <div class="alert-content">
            <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
          </div>
          <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
        </div>
      </div>
    <?php endif; ?>

    <!-- ============ HERO BANNER ============ -->
    <section class="hero-banner" aria-label="Banner utama">
      <div class="container">
        <h1>Temukan Barang Impianmu</h1>
        <p>Jual beli barang bekas maupun baru, lebih mudah dan cepat di seluruh Indonesia.</p>
      </div>
    </section>


    <!-- ============ KATEGORI POPULER ============ -->
    <!-- Tabel terkait: categories (id, name, icon) -->
    <section class="section" aria-labelledby="heading-kategori">
      <div class="container">

        <div class="section-header">
          <h2 id="heading-kategori">Jelajahi Kategori</h2>
          <a href="kategori.php">Lihat Semua →</a>
        </div>

        <div class="category-grid">
          <?php foreach ($categories as $cat): ?>
            <a href="kategori.php?c=<?= (int) $cat['id'] ?>" class="category-card">
              <span class="cat-icon" aria-hidden="true"><i class="<?= htmlspecialchars($cat['icon']) ?>"></i></span>
              <span class="cat-name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
          <?php endforeach; ?>
        </div>

      </div>
    </section>


    <!-- ============ IKLAN TERBARU ============ -->
    <!-- Tabel terkait: ads + ad_images + categories + users -->
    <section class="section" aria-labelledby="heading-terbaru">
      <div class="container">

        <div class="section-header">
          <h2 id="heading-terbaru">Iklan Terbaru</h2>
          <a href="iklan.php?sort=terbaru">Lihat Semua →</a>
        </div>

        <div class="ad-grid">

          <?php if (!empty($latestAds)): ?>
            <!-- Menampilkan iklan nyata dari database -->
            <?php foreach ($latestAds as $ad): ?>
              <article class="ad-card">
                <a href="detail.php?id=<?= (int) $ad['id'] ?>" aria-label="<?= htmlspecialchars($ad['title']) ?> - Rp <?= number_format($ad['price'], 0, ',', '.') ?>">
                  <div class="ad-card-image">
                    <?php if (!empty($ad['image_path']) && file_exists(__DIR__ . '/' . $ad['image_path'])): ?>
                      <img src="<?= htmlspecialchars($ad['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                      <div class="img-placeholder" aria-hidden="true">
                        <i class="<?= !empty($ad['category_icon']) ? htmlspecialchars($ad['category_icon']) : 'fa-solid fa-box-open' ?>"></i>
                      </div>
                    <?php endif; ?>
                    <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">
                      <i class="fa-regular fa-heart"></i>
                    </button>
                  </div>
                  <div class="ad-card-body">
                    <p class="ad-card-price">Rp <?= number_format($ad['price'], 0, ',', '.') ?></p>
                    <h3 class="ad-card-title"><?= htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="ad-card-meta">
                      <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($ad['location'], ENT_QUOTES, 'UTF-8') ?></span>
                      <time datetime="<?= substr($ad['created_at'], 0, 10) ?>"><?= date('d M', strtotime($ad['created_at'])) ?></time>
                    </div>
                  </div>
                </a>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Iklan Contoh Tambahan (Fallback & Showcase Template) -->
          <!-- Iklan Card 1 -->
          <article class="ad-card">
            <a href="detail.php?id=1" aria-label="Toyota Avanza 2020 - Rp 185.000.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-car"></i></div>
                <span class="ad-card-badge">Unggulan</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 185.000.000</p>
                <h3 class="ad-card-title">Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Jakarta Selatan</span>
                  <time datetime="2026-09-20">20 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 2 -->
          <article class="ad-card">
            <a href="detail.php?id=2" aria-label="iPhone 15 Pro Max - Rp 18.500.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 18.500.000</p>
                <h3 class="ad-card-title">iPhone 15 Pro Max 256GB Natural Titanium Fullset</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Bandung</span>
                  <time datetime="2026-09-19">19 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 3 -->
          <article class="ad-card">
            <a href="detail.php?id=3" aria-label="Rumah Cluster Minimalis - Rp 750.000.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-house"></i></div>
                <span class="ad-card-badge">Baru</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 750.000.000</p>
                <h3 class="ad-card-title">Rumah Cluster Minimalis 2 Lantai di Tangerang Selatan</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Tangerang Selatan</span>
                  <time datetime="2026-09-19">19 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 4 -->
          <article class="ad-card">
            <a href="detail.php?id=4" aria-label="MacBook Air M2 - Rp 14.200.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-laptop"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 14.200.000</p>
                <h3 class="ad-card-title">MacBook Air M2 2023 8/256GB Midnight Garansi Resmi</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Surabaya</span>
                  <time datetime="2026-09-18">18 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 5 -->
          <article class="ad-card">
            <a href="detail.php?id=5" aria-label="Honda Beat 2023 - Rp 16.500.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-motorcycle"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 16.500.000</p>
                <h3 class="ad-card-title">Honda Beat Street 2023 Hitam KM Rendah Pajak Panjang</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Yogyakarta</span>
                  <time datetime="2026-09-18">18 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 6 -->
          <article class="ad-card">
            <a href="detail.php?id=6" aria-label="Sofa L Minimalis - Rp 3.200.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-couch"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 3.200.000</p>
                <h3 class="ad-card-title">Sofa L Minimalis Bahan Oscar Anti Air Bisa Custom</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Bekasi</span>
                  <time datetime="2026-09-17">17 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 7 -->
          <article class="ad-card">
            <a href="detail.php?id=7" aria-label="PS5 Slim Digital Edition - Rp 6.800.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-gamepad"></i></div>
                <span class="ad-card-badge">Promo</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 6.800.000</p>
                <h3 class="ad-card-title">PS5 Slim Digital Edition + 2 Controller Fullset Box</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Medan</span>
                  <time datetime="2026-09-17">17 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 8 -->
          <article class="ad-card">
            <a href="detail.php?id=8" aria-label="Sepeda Lipat SELI - Rp 2.500.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-bicycle"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 2.500.000</p>
                <h3 class="ad-card-title">Sepeda Lipat 20 Inch 7 Speed Shimano Mulus Jarang Pakai</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Semarang</span>
                  <time datetime="2026-09-16">16 Sep</time>
                </div>
              </div>
            </a>
          </article>

        </div>

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


    <!-- ============ IKLAN REKOMENDASI ============ -->
    <section class="section" aria-labelledby="heading-rekomendasi">
      <div class="container">

        <div class="section-header">
          <h2 id="heading-rekomendasi">Rekomendasi Untukmu</h2>
          <a href="iklan.php?sort=rekomendasi">Lihat Semua →</a>
        </div>

        <div class="ad-grid">

          <!-- Rekomendasi Card 1 -->
          <article class="ad-card">
            <a href="detail.php?id=9" aria-label="Samsung Galaxy S24 Ultra - Rp 15.900.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 15.900.000</p>
                <h3 class="ad-card-title">Samsung Galaxy S24 Ultra 12/256 Titanium Gray SEIN</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Depok</span>
                  <time datetime="2026-09-15">15 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Rekomendasi Card 2 -->
          <article class="ad-card">
            <a href="detail.php?id=10" aria-label="Meja Kerja Standing Desk - Rp 1.850.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-chair"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 1.850.000</p>
                <h3 class="ad-card-title">Standing Desk Elektrik 120x60 Adjustable Height Minimalis</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Bogor</span>
                  <time datetime="2026-09-14">14 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Rekomendasi Card 3 -->
          <article class="ad-card">
            <a href="detail.php?id=11" aria-label="Kamera Canon EOS R50 - Rp 11.200.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-camera"></i></div>
                <span class="ad-card-badge">Baru</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 11.200.000</p>
                <h3 class="ad-card-title">Canon EOS R50 Kit 18-45mm IS STM Garansi Resmi Datascrip</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Malang</span>
                  <time datetime="2026-09-13">13 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Rekomendasi Card 4 -->
          <article class="ad-card">
            <a href="detail.php?id=12" aria-label="Kost Eksklusif Bulanan - Rp 2.000.000/bulan">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true"><i class="fa-solid fa-building"></i></div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button"><i class="fa-regular fa-heart"></i></button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 2.000.000<small>/bulan</small></p>
                <h3 class="ad-card-title">Kost Eksklusif Full Furnished AC WiFi Dekat Kampus UI</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location"><i class="fa-solid fa-location-dot"></i> Depok</span>
                  <time datetime="2026-09-12">12 Sep</time>
                </div>
              </div>
            </a>
          </article>

        </div>

      </div>
    </section>

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
