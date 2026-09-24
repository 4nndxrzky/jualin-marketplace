<?php
    /**
 * Halaman Informasi: Tips Keamanan (keamanan.php)
 * Jualin - Codepolitan
 */

    session_start();
    require_once __DIR__ . '/koneksi.php';

    $stmtCategories = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
    $categories     = $stmtCategories->fetchAll();

    $stmtLoc   = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND TRIM(location) != '' ORDER BY location ASC");
    $locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

    $isLoggedIn = isset($_SESSION['user_id']);
    $userName   = $_SESSION['user_name'] ?? 'Pengguna';
    $userEmail  = $_SESSION['user_email'] ?? '';
    $userId     = (int) ($_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panduan Transaksi Aman & Tips Keamanan — Jualin</title>
  <meta name="description" content="Ketahui panduan transaksi aman di Jualin. Pelajari cara menghindari penipuan, bertransaksi COD yang aman, dan mengenali indikasi iklan palsu.">

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
       HEADER — Logo, Lokasi, Search, Auth, Jual
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
            <span><?php echo htmlspecialchars($_GET['loc'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8'); ?></span>
            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
          </button>
          <div class="location-menu" role="menu">
            <a href="index.php" class="location-item">
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
          <label for="search-input" class="sr-only">Cari di Jualin</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, properti, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth Header Actions -->
        <div class="header-actions">
          <?php if ($isLoggedIn): ?>
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
                <a href="edit-profil.php" class="dropdown-item" role="menuitem">
                  <i class="fa-solid fa-user-pen"></i> Edit Profil
                </a>
                <a href="iklan-saya.php" class="dropdown-item" role="menuitem">
                  <i class="fa-solid fa-box-open"></i> Iklan Saya
                </a>
                <a href="penjual.php?id=<?php echo $userId; ?>" class="dropdown-item" role="menuitem">
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

  <!-- ==================== BREADCRUMB ==================== -->
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <ol class="breadcrumb">
        <li><a href="index.php">Beranda</a></li>
        <li aria-current="page">Tips Keamanan</li>
      </ol>
    </nav>
  </div>

  <!-- ==================== KONTEN UTAMA ==================== -->
  <main class="container info-page-wrapper">
    <article class="info-card">
      <header class="info-header">
        <span class="info-header-badge"><i class="fa-solid fa-shield-halved"></i> Perlindungan Pengguna</span>
        <h1>Tips Transaksi Aman di Jualin</h1>
        <p>Prioritaskan keselamatan Anda saat bertransaksi jual beli dengan menerapkan panduan pencegahan berikut.</p>
      </header>

      <div class="info-body">
        <div class="info-grid-cards">
          <div class="info-subcard">
            <div class="info-subcard-icon"><i class="fa-solid fa-users"></i></div>
            <h4>1. Bertemu di Tempat Umum (COD)</h4>
            <p>Pilihlah tempat pertemuan yang ramai, terang, dan memiliki CCTV seperti mall, kafe, minimarket, atau kantor polisi setempat. Hindari lokasi terpencil.</p>
          </div>

          <div class="info-subcard">
            <div class="info-subcard-icon"><i class="fa-solid fa-ban"></i></div>
            <h4>2. Jangan Kirim Uang Muka (DP)</h4>
            <p>Jangan pernah mentransfer DP, uang tanda jadi, atau biaya pengiriman sebelum Anda bertemu langsung dan memegang barang yang diperjualbelikan.</p>
          </div>

          <div class="info-subcard">
            <div class="info-subcard-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
            <h4>3. Periksa Fisik & Dokumen</h4>
            <p>Uji menyeluruh semua fungsi fisik barang. Untuk kendaraan atau properti, pastikan BPKB, STNK, faktur, atau sertifikat asli dan cocok nomor rangkanya.</p>
          </div>

          <div class="info-subcard">
            <div class="info-subcard-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h4>4. Waspadai Harga Terlalu Murah</h4>
            <p>Jika harga yang ditawarkan jauh di bawah batas wajar pasaran dengan alasan darurat atau luar kota, patut diduga sebagai indikasi penipuan.</p>
          </div>
        </div>

        <h2><i class="fa-solid fa-circle-exclamation" style="color: var(--danger);"></i> Menemukan Iklan Mencurigakan?</h2>
        <p>Jika Anda menemukan listing iklan yang meminta transfer mencurigakan, menjual barang terlarang, atau terindikasi penipuan, segera laporkan iklan tersebut melalui halaman <a href="laporkan.php" style="color: var(--primary); font-weight: 700; text-decoration: underline;">Laporkan Iklan</a> agar tim moderator kami segera menindaklanjuti.</p>
      </div>
    </article>
  </main>

  <!-- ==================== FOOTER ==================== -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h3>Jualin</h3>
          <ul>
            <li><a href="tentang.php">Tentang Kami</a></li>
            <li><a href="karir.php">Karir</a></li>
            <li><a href="kontak.php">Hubungi Kami</a></li>
            <li><a href="blog.php">Blog</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h3>Kategori Populer</h3>
          <ul>
            <?php foreach (array_slice($categories, 0, 4) as $popularCat): ?>
              <li><a href="index.php?c=<?php echo (int) $popularCat['id']; ?>"><?php echo htmlspecialchars($popularCat['name'], ENT_QUOTES, 'UTF-8'); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="footer-col">
          <h3>Bantuan</h3>
          <ul>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="keamanan.php">Tips Keamanan</a></li>
            <li><a href="panduan.php">Panduan Jual Beli</a></li>
            <li><a href="laporkan.php">Laporkan Iklan</a></li>
          </ul>
        </div>
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

  <script src="assets/js/main.js"></script>
</body>
</html>

