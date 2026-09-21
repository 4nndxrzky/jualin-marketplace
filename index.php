<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
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

        <!-- Lokasi — mengambil data dari ads.location -->
        <button class="location-selector" aria-label="Pilih lokasi" type="button">
          📍 <span>Indonesia</span> ▾
        </button>

        <!-- Search Bar — mencari dari ads.title, ads.description -->
        <form class="search-form" action="search.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, laptop, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari">🔍</button>
        </form>

        <!-- Auth — Status Login dari Tabel users -->
        <div class="header-actions">
          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-menu-wrapper">
              <button type="button" class="user-menu-btn" aria-haspopup="true" aria-expanded="false">
                <span class="user-avatar-sm"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></span>
                <span class="user-menu-name"><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <span aria-hidden="true">▾</span>
              </button>
              <div class="user-dropdown" role="menu">
                <div style="padding: 10px 16px; border-bottom: 1px solid var(--gray-200);">
                  <strong style="display: block; font-size: 0.88rem; color: var(--text-primary);"><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <small style="color: var(--text-muted); font-size: 0.75rem;"><?= htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <a href="pasang-iklan.php" class="dropdown-item" role="menuitem">📦 Iklan Saya</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item danger-item" role="menuitem">🚪 Keluar (Logout)</a>
              </div>
            </div>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline">Masuk</a>
          <?php endif; ?>
          <a href="pasang-iklan.php" class="btn btn-primary">
            ＋ Jual
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
        <li><a href="kategori.php?c=mobil"><span class="cat-icon">🚗</span> Mobil</a></li>
        <li><a href="kategori.php?c=motor"><span class="cat-icon">🏍️</span> Motor</a></li>
        <li><a href="kategori.php?c=properti"><span class="cat-icon">🏠</span> Properti</a></li>
        <li><a href="kategori.php?c=elektronik"><span class="cat-icon">📱</span> Elektronik</a></li>
        <li><a href="kategori.php?c=perabotan"><span class="cat-icon">🛋️</span> Perabotan</a></li>
        <li><a href="kategori.php?c=fashion"><span class="cat-icon">👕</span> Fashion</a></li>
        <li><a href="kategori.php?c=hobi-olahraga"><span class="cat-icon">⚽</span> Hobi & Olahraga</a></li>
        <li><a href="kategori.php?c=jasa"><span class="cat-icon">🔧</span> Jasa</a></li>
        <li><a href="kategori.php?c=lowongan"><span class="cat-icon">💼</span> Lowongan Kerja</a></li>
        <li><a href="kategori.php?c=lainnya"><span class="cat-icon">📦</span> Lainnya</a></li>
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
          <span class="alert-icon" aria-hidden="true">🎉</span>
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

          <a href="kategori.php?c=mobil" class="category-card">
            <span class="cat-icon" aria-hidden="true">🚗</span>
            <span class="cat-name">Mobil</span>
          </a>

          <a href="kategori.php?c=motor" class="category-card">
            <span class="cat-icon" aria-hidden="true">🏍️</span>
            <span class="cat-name">Motor</span>
          </a>

          <a href="kategori.php?c=properti" class="category-card">
            <span class="cat-icon" aria-hidden="true">🏠</span>
            <span class="cat-name">Properti</span>
          </a>

          <a href="kategori.php?c=elektronik" class="category-card">
            <span class="cat-icon" aria-hidden="true">📱</span>
            <span class="cat-name">Elektronik</span>
          </a>

          <a href="kategori.php?c=perabotan" class="category-card">
            <span class="cat-icon" aria-hidden="true">🛋️</span>
            <span class="cat-name">Perabotan</span>
          </a>

          <a href="kategori.php?c=fashion" class="category-card">
            <span class="cat-icon" aria-hidden="true">👕</span>
            <span class="cat-name">Fashion</span>
          </a>

          <a href="kategori.php?c=hobi-olahraga" class="category-card">
            <span class="cat-icon" aria-hidden="true">⚽</span>
            <span class="cat-name">Hobi & Olahraga</span>
          </a>

          <a href="kategori.php?c=jasa" class="category-card">
            <span class="cat-icon" aria-hidden="true">🔧</span>
            <span class="cat-name">Jasa</span>
          </a>

        </div>

      </div>
    </section>


    <!-- ============ IKLAN TERBARU ============ -->
    <!-- Tabel terkait: ads (title, price, location, created_at) + ad_images (image_path) + categories (name) + users (name) -->
    <section class="section" aria-labelledby="heading-terbaru">
      <div class="container">

        <div class="section-header">
          <h2 id="heading-terbaru">Iklan Terbaru</h2>
          <a href="iklan.php?sort=terbaru">Lihat Semua →</a>
        </div>

        <div class="ad-grid">

          <!-- Iklan Card 1 -->
          <article class="ad-card">
            <a href="detail.php?id=1" aria-label="Toyota Avanza 2020 - Rp 185.000.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🚗</div>
                <span class="ad-card-badge">Unggulan</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 185.000.000</p>
                <h3 class="ad-card-title">Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Jakarta Selatan</span>
                  <time datetime="2026-09-20">20 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 2 -->
          <article class="ad-card">
            <a href="detail.php?id=2" aria-label="iPhone 15 Pro Max - Rp 18.500.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">📱</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 18.500.000</p>
                <h3 class="ad-card-title">iPhone 15 Pro Max 256GB Natural Titanium Fullset</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Bandung</span>
                  <time datetime="2026-09-19">19 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 3 -->
          <article class="ad-card">
            <a href="detail.php?id=3" aria-label="Rumah Cluster Minimalis - Rp 750.000.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🏠</div>
                <span class="ad-card-badge">Baru</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 750.000.000</p>
                <h3 class="ad-card-title">Rumah Cluster Minimalis 2 Lantai di Tangerang Selatan</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Tangerang Selatan</span>
                  <time datetime="2026-09-19">19 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 4 -->
          <article class="ad-card">
            <a href="detail.php?id=4" aria-label="MacBook Air M2 - Rp 14.200.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">💻</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 14.200.000</p>
                <h3 class="ad-card-title">MacBook Air M2 2023 8/256GB Midnight Garansi Resmi</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Surabaya</span>
                  <time datetime="2026-09-18">18 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 5 -->
          <article class="ad-card">
            <a href="detail.php?id=5" aria-label="Honda Beat 2023 - Rp 16.500.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🏍️</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 16.500.000</p>
                <h3 class="ad-card-title">Honda Beat Street 2023 Hitam KM Rendah Pajak Panjang</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Yogyakarta</span>
                  <time datetime="2026-09-18">18 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 6 -->
          <article class="ad-card">
            <a href="detail.php?id=6" aria-label="Sofa L Minimalis - Rp 3.200.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🛋️</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 3.200.000</p>
                <h3 class="ad-card-title">Sofa L Minimalis Bahan Oscar Anti Air Bisa Custom</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Bekasi</span>
                  <time datetime="2026-09-17">17 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 7 -->
          <article class="ad-card">
            <a href="detail.php?id=7" aria-label="PS5 Slim Digital Edition - Rp 6.800.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🎮</div>
                <span class="ad-card-badge">Promo</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 6.800.000</p>
                <h3 class="ad-card-title">PS5 Slim Digital Edition + 2 Controller Fullset Box</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Medan</span>
                  <time datetime="2026-09-17">17 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Iklan Card 8 -->
          <article class="ad-card">
            <a href="detail.php?id=8" aria-label="Sepeda Lipat SELI - Rp 2.500.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🚲</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 2.500.000</p>
                <h3 class="ad-card-title">Sepeda Lipat 20 Inch 7 Speed Shimano Mulus Jarang Pakai</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Semarang</span>
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
          <a href="pasang-iklan.php" class="btn btn-accent">Jual Sekarang</a>
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
                <div class="img-placeholder" aria-hidden="true">📱</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 15.900.000</p>
                <h3 class="ad-card-title">Samsung Galaxy S24 Ultra 12/256 Titanium Gray SEIN</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Depok</span>
                  <time datetime="2026-09-15">15 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Rekomendasi Card 2 -->
          <article class="ad-card">
            <a href="detail.php?id=10" aria-label="Meja Kerja Standing Desk - Rp 1.850.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🪑</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 1.850.000</p>
                <h3 class="ad-card-title">Standing Desk Elektrik 120x60 Adjustable Height Minimalis</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Bogor</span>
                  <time datetime="2026-09-14">14 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Rekomendasi Card 3 -->
          <article class="ad-card">
            <a href="detail.php?id=11" aria-label="Kamera Canon EOS R50 - Rp 11.200.000">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">📷</div>
                <span class="ad-card-badge">Baru</span>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 11.200.000</p>
                <h3 class="ad-card-title">Canon EOS R50 Kit 18-45mm IS STM Garansi Resmi Datascrip</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Malang</span>
                  <time datetime="2026-09-13">13 Sep</time>
                </div>
              </div>
            </a>
          </article>

          <!-- Rekomendasi Card 4 -->
          <article class="ad-card">
            <a href="detail.php?id=12" aria-label="Kost Eksklusif Bulanan - Rp 2.000.000/bulan">
              <div class="ad-card-image">
                <div class="img-placeholder" aria-hidden="true">🏢</div>
                <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
              </div>
              <div class="ad-card-body">
                <p class="ad-card-price">Rp 2.000.000<small>/bulan</small></p>
                <h3 class="ad-card-title">Kost Eksklusif Full Furnished AC WiFi Dekat Kampus UI</h3>
                <div class="ad-card-meta">
                  <span class="ad-card-location">📍 Depok</span>
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
