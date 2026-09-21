<?php
session_start();
require_once __DIR__ . '/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- ==================== META DASAR ==================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- ==================== SEO META TAGS (DINAMIS DARI TABEL ads) ==================== -->
  <title>Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat — Jual Beli Mobil Bekas | OLX Clone</title>
  <meta name="description" content="Jual Toyota Avanza 1.3 G MT 2020 warna putih di Jakarta Selatan seharga Rp 185.000.000. Kondisi mulus terawat tangan pertama, surat-surat lengkap, pajak hidup. Cek selengkapnya di OLX Clone!">
  <meta name="keywords" content="Toyota Avanza 2020 bekas, jual mobil Avanza Jakarta Selatan, mobil bekas murah, OLX Clone mobil, bursa mobil bekas">
  <meta name="author" content="Rizky Pratama">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://olxclone.local/detail.php?id=1">

  <!-- ==================== OPEN GRAPH (Social Media & WhatsApp) ==================== -->
  <meta property="og:type" content="product">
  <meta property="og:title" content="Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat — OLX Clone">
  <meta property="og:description" content="Jual Toyota Avanza 1.3 G MT 2020 di Jakarta Selatan seharga Rp 185.000.000. Tangan pertama, service record resmi.">
  <meta property="og:url" content="https://olxclone.local/detail.php?id=1">
  <meta property="og:image" content="https://olxclone.local/assets/images/sample-avanza.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">
  <meta property="product:price:amount" content="185000000">
  <meta property="product:price:currency" content="IDR">
  <meta property="product:availability" content="in stock">
  <meta property="product:condition" content="used">

  <!-- ==================== TWITTER CARD ==================== -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat — OLX Clone">
  <meta name="twitter:description" content="Rp 185.000.000 - Jakarta Selatan. Kondisi istimewa, tangan pertama, pajak panjang.">
  <meta name="twitter:image" content="https://olxclone.local/assets/images/sample-avanza.jpg">

  <!-- ==================== FAVICON ==================== -->
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">

  <!-- ==================== JSON-LD STRUCTURED DATA (Product + BreadcrumbList) ==================== -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
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
          "name": "Mobil",
          "item": "https://olxclone.local/kategori.php?c=mobil"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat",
          "item": "https://olxclone.local/detail.php?id=1"
        }
      ]
    }
  </script>

  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Product",
      "name": "Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat",
      "image": [
        "https://olxclone.local/assets/images/sample-avanza.jpg"
      ],
      "description": "Toyota Avanza 1.3 G MT 2020 warna putih mutiara. Pemakaian pribadi tangan pertama dari baru, service record bengkel resmi Toyota. Pajak panjang sampai Oktober 2026.",
      "sku": "AD-00001",
      "category": "Mobil",
      "offers": {
        "@type": "Offer",
        "url": "https://olxclone.local/detail.php?id=1",
        "priceCurrency": "IDR",
        "price": "185000000",
        "priceValidUntil": "2026-12-31",
        "itemCondition": "https://schema.org/UsedCondition",
        "availability": "https://schema.org/InStock",
        "seller": {
          "@type": "Person",
          "name": "Rizky Pratama"
        }
      }
    }
  </script>

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
          📍 <span>Jakarta Selatan</span> ▾
        </button>

        <!-- Search Bar -->
        <form class="search-form" action="search.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, laptop, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari">🔍</button>
        </form>

        <!-- Auth Actions — Dinamis dari Status Login users -->
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
       NAVIGASI KATEGORI (Konsisten dengan index.php)
       Tabel: categories (id, name, icon)
       ================================================================ -->
  <nav class="category-nav" aria-label="Navigasi kategori">
    <div class="container">
      <ul class="category-nav-list">
        <li><a href="kategori.php?c=mobil" aria-current="page"><span class="cat-icon">🚗</span> Mobil</a></li>
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
       BREADCRUMB (SEO & User Navigation)
       ================================================================ -->
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <ol class="breadcrumb">
        <li><a href="index.php">Beranda</a></li>
        <li><a href="kategori.php?c=mobil">Mobil</a></li>
        <li aria-current="page">Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat</li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN UTAMA DETAIL IKLAN (2 Kolom: Kiri Konten, Kanan Sidebar)
       Tabel terkait:
         - ads (id, user_id, category_id, title, description, price, location, created_at)
         - ad_images (id, ad_id, image_path)
         - users (id, name, created_at)
         - categories (id, name, icon)
       ================================================================ -->
  <main id="main-content" class="container" role="main">
    <div class="detail-layout">

      <!-- ==================== KOLOM KIRI (KONTEN UTAMA) ==================== -->
      <article class="detail-content">

        <!-- ===== 1. GALERI FOTO (ad_images) ===== -->
        <section class="gallery-container" aria-label="Galeri Gambar Iklan">

          <!-- Foto Utama -->
          <figure class="gallery-main">
            <div class="gallery-placeholder" aria-hidden="true">
              🚗
              <span>Tampak Depan Kendaraan</span>
            </div>
            <!-- Catatan: Pada implementasi PHP dinamis nanti, ganti dengan:
                 <img src="<?php echo htmlspecialchars($main_image['image_path']) ?>" alt="<?php echo htmlspecialchars($ad['title']) ?>" id="main-gallery-img"> -->
            <span class="ad-card-badge">Unggulan</span>
            <span class="gallery-counter" aria-label="Jumlah foto">📸 1 / 5</span>
          </figure>

          <!-- Thumbnail Strip (ad_images table) -->
          <ul class="gallery-thumbs" role="tablist" aria-label="Thumbnail foto iklan">
            <li class="gallery-thumb-item active" role="tab" aria-selected="true" tabindex="0" title="Foto 1: Tampak Depan">
              🚗
            </li>
            <li class="gallery-thumb-item" role="tab" aria-selected="false" tabindex="-1" title="Foto 2: Tampak Samping">
              🚙
            </li>
            <li class="gallery-thumb-item" role="tab" aria-selected="false" tabindex="-1" title="Foto 3: Interior & Dashboard">
              💺
            </li>
            <li class="gallery-thumb-item" role="tab" aria-selected="false" tabindex="-1" title="Foto 4: Bagasi Belakang">
              🧳
            </li>
            <li class="gallery-thumb-item" role="tab" aria-selected="false" tabindex="-1" title="Foto 5: Ruang Mesin">
              ⚙️
            </li>
          </ul>

        </section>


        <!-- ===== 2. TOOLBAR DETAIL (Bagikan, Laporkan, Wishlist) ===== -->
        <div class="detail-toolbar">
          <div class="detail-toolbar-left">
            <span>ID Iklan: <strong>#10842</strong></span>
            <span>•</span>
            <time datetime="2026-09-20">Diposting: 20 September 2026</time>
          </div>
          <div class="detail-toolbar-right">
            <button type="button" class="btn-icon-action" aria-label="Bagikan iklan ini">
              🔗 Bagikan
            </button>
            <button type="button" class="btn-icon-action" aria-label="Simpan ke favorit">
              ♡ Favorit
            </button>
            <button type="button" class="btn-icon-action" aria-label="Laporkan iklan ini">
              🚩 Laporkan
            </button>
          </div>
        </div>


        <!-- ===== 3. SPESIFIKASI & DETAIL LENGKAP ===== -->
        <section class="detail-box" aria-labelledby="heading-specs">
          <h2 id="heading-specs" class="detail-box-title">Detail & Spesifikasi</h2>

          <dl class="specs-grid">
            <div class="spec-item">
              <dt>Kategori</dt>
              <dd><a href="kategori.php?c=mobil">Mobil Bekas</a></dd>
            </div>
            <div class="spec-item">
              <dt>Merek</dt>
              <dd>Toyota</dd>
            </div>
            <div class="spec-item">
              <dt>Model</dt>
              <dd>Avanza 1.3 G</dd>
            </div>
            <div class="spec-item">
              <dt>Tahun Pembuatan</dt>
              <dd>2020</dd>
            </div>
            <div class="spec-item">
              <dt>Transmisi</dt>
              <dd>Manual (MT)</dd>
            </div>
            <div class="spec-item">
              <dt>Jarak Tempuh (KM)</dt>
              <dd>38.500 km</dd>
            </div>
            <div class="spec-item">
              <dt>Tipe Bahan Bakar</dt>
              <dd>Bensin</dd>
            </div>
            <div class="spec-item">
              <dt>Warna Kendaraan</dt>
              <dd>Putih Mutiara</dd>
            </div>
            <div class="spec-item">
              <dt>Kondisi</dt>
              <dd>Bekas (Mulus & Terawat)</dd>
            </div>
            <div class="spec-item">
              <dt>Pajak STNK</dt>
              <dd>Hidup (s/d Oktober 2026)</dd>
            </div>
          </dl>
        </section>


        <!-- ===== 4. DESKRIPSI IKLAN (ads.description) ===== -->
        <section class="detail-box" aria-labelledby="heading-desc">
          <h2 id="heading-desc" class="detail-box-title">Deskripsi Lengkap</h2>

          <div class="description-body">
            <p>
              Dijual cepat mobil keluarga idaman: <strong>Toyota Avanza 1.3 G Manual tahun 2020 warna Putih Mutiara</strong>.
              Pemakaian pribadi tangan pertama dari baru, atas nama sendiri, bukan bekas taksi online atau operasional kantor.
            </p>

            <p><strong>Kondisi Kendaraan:</strong></p>
            <ul>
              <li>Odometer asli 38.500 km (slow moving), service record lengkap berkala di bengkel resmi Toyota Astra Motor.</li>
              <li>Mesin halus, kering, no rembes, tarikan enteng, dan bensin sangat irit.</li>
              <li>AC double blower sangat dingin dan berfungsi normal.</li>
              <li>Kaki-kaki senyap tanpa bunyi, ban 4 buah masih tebal 85% (Bridgestone) + ban serep belum pernah turun.</li>
              <li>Interior original fabric bersih, wangi, tidak merokok. Headunit touchscreen support Bluetooth & USB.</li>
              <li>Bodi mulus 95%, cat original pabrik, bebas tabrakan besar dan bebas banjir (bisa dicek montir kepercayaan atau inspeksi Otospector).</li>
            </ul>

            <p><strong>Kelengkapan Dokumen & Legalitas:</strong></p>
            <ul>
              <li>STNK, BPKB, dan Faktur Pembelian asli lengkap di tangan.</li>
              <li>Buku manual, buku servis, dan kunci kontak serep lengkap.</li>
              <li>Pajak hidup panjang sampai Oktober 2026, plat B Jakarta Selatan (Ganjil).</li>
            </ul>

            <p>
              Harga nego santai dan sopan setelah cek unit di lokasi. Siap antar untuk test drive di sekitar Cilandak/TB Simatupang, Jakarta Selatan.
              Silakan hubungi via WhatsApp atau Chat OLX langsung!
            </p>
          </div>
        </section>


        <!-- ===== 5. LOKASI PENJUAL (ads.location) ===== -->
        <section class="detail-box" aria-labelledby="heading-loc">
          <h2 id="heading-loc" class="detail-box-title">Lokasi Barang</h2>

          <div class="location-box">
            <p class="location-info">
              📍 <span>Cilandak, Jakarta Selatan, DKI Jakarta</span>
            </p>
            <div class="map-placeholder" aria-label="Peta lokasi perkiraan">
              <span>🗺️</span>
              <p>Area Perkiraan: Sekitar Cilandak Barat / TB Simatupang</p>
              <small>(Lokasi presisi akan diberikan penjual saat janjian COD)</small>
            </div>
          </div>
        </section>

      </article>


      <!-- ==================== KOLOM KANAN (SIDEBAR STICKY) ==================== -->
      <aside class="detail-sidebar" aria-label="Ringkasan Iklan dan Kontak Penjual">

        <!-- Card 1: Harga & Judul (ads.price, ads.title) -->
        <div class="sidebar-card sidebar-price-card">
          <p class="price-amount">Rp 185.000.000</p>
          <h1 class="ad-title">Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat</h1>
          <div class="ad-meta-info">
            <span>📍 Jakarta Selatan</span>
            <time datetime="2026-09-20">20 Sep 2026</time>
          </div>
        </div>

        <!-- Card 2: Profil Penjual (users table) -->
        <div class="sidebar-card seller-card">
          <div class="seller-profile">
            <div class="seller-avatar" aria-hidden="true">
              R
            </div>
            <div class="seller-info">
              <h3 class="seller-name">
                Rizky Pratama
                <span class="badge badge-verified" title="Identitas terverifikasi">✓ Terverifikasi</span>
              </h3>
              <p class="seller-member-since">Member sejak Januari 2024</p>
            </div>
          </div>

          <!-- Aksi Kontak -->
          <div class="seller-actions">
            <a href="#chat" class="btn btn-solid-primary btn-block">
              💬 Chat Penjual
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20Rizky,%20saya%20tertarik%20dengan%20Toyota%20Avanza%202020%20di%20OLX%20Clone" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp btn-block">
              📱 Hubungi via WhatsApp
            </a>
            <button type="button" class="btn btn-outline btn-block" onclick="this.innerHTML='📞 0812-3456-7890'">
              📞 Tampilkan Telepon
            </button>
          </div>

          <a href="profil.php?id=1" class="seller-profile-link">
            Lihat semua iklan penjual ini →
          </a>
        </div>

        <!-- Card 3: Tips Keamanan Bertransaksi (Fitur Khas OLX) -->
        <div class="sidebar-card safety-card">
          <div class="safety-header">
            <span>🛡️</span>
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
         Tabel terkait: ads + ad_images + categories
         ================================================================ -->
    <section class="section" aria-labelledby="heading-related">
      <div class="section-header">
        <h2 id="heading-related">Iklan Terkait Lainnya</h2>
        <a href="kategori.php?c=mobil">Lihat Mobil Lainnya →</a>
      </div>

      <div class="ad-grid">

        <!-- Card 1 -->
        <article class="ad-card">
          <a href="detail.php?id=5" aria-label="Honda Brio Satya E 2021 - Rp 142.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true">🚗</div>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 142.000.000</p>
              <h3 class="ad-card-title">Honda Brio Satya E CVT 2021 Abu-abu Metalik KM Rendah</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location">📍 Jakarta Barat</span>
                <time datetime="2026-09-18">18 Sep</time>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 2 -->
        <article class="ad-card">
          <a href="detail.php?id=13" aria-label="Mitsubishi Xpander Ultimate 2020 - Rp 215.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true">🚙</div>
              <span class="ad-card-badge">Baru</span>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 215.000.000</p>
              <h3 class="ad-card-title">Mitsubishi Xpander Ultimate AT 2020 Hitam Full Original</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location">📍 Tangerang</span>
                <time datetime="2026-09-17">17 Sep</time>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 3 -->
        <article class="ad-card">
          <a href="detail.php?id=14" aria-label="Daihatsu Sigra 1.2 R Deluxe 2022 - Rp 128.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true">🚗</div>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 128.000.000</p>
              <h3 class="ad-card-title">Daihatsu Sigra 1.2 R Deluxe MT 2022 Silver Siap Pakai</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location">📍 Depok</span>
                <time datetime="2026-09-16">16 Sep</time>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 4 -->
        <article class="ad-card">
          <a href="detail.php?id=15" aria-label="Suzuki Ertiga GL AT 2019 - Rp 165.000.000">
            <div class="ad-card-image">
              <div class="img-placeholder" aria-hidden="true">🚙</div>
              <button class="ad-card-wishlist" aria-label="Simpan ke wishlist" type="button">♡</button>
            </div>
            <div class="ad-card-body">
              <p class="ad-card-price">Rp 165.000.000</p>
              <h3 class="ad-card-title">Suzuki Ertiga GL Automatic 2019 Merah Maroon Antik</h3>
              <div class="ad-card-meta">
                <span class="ad-card-location">📍 Bekasi</span>
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

