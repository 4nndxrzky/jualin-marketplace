<?php
/**
 * Halaman Informasi & Formulir: Laporkan Iklan (laporkan.php)
 * OLX Clone - Codepolitan
 */

session_start();
require_once __DIR__ . '/koneksi.php';

$stmtCategories = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
$categories = $stmtCategories->fetchAll();

$stmtLoc = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND TRIM(location) != '' ORDER BY location ASC");
$locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? 'Pengguna';
$userEmail  = $_SESSION['user_email'] ?? '';
$userId     = (int) ($_SESSION['user_id'] ?? 0);

$adId = isset($_GET['ad_id']) && is_numeric($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$reportedAd = null;
if ($adId > 0) {
    $stmtAd = $pdo->prepare("SELECT id, title, price FROM ads WHERE id = ? LIMIT 1");
    $stmtAd->execute([$adId]);
    $reportedAd = $stmtAd->fetch();
}

$reportSubmitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportSubmitted = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporkan Iklan Mencurigakan — OLX Clone</title>
  <meta name="description" content="Formulir pengaduan resmi untuk melaporkan iklan penipuan, konten terlarang, atau spam di OLX Clone demi keamanan komunitas kita.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ==================== HEADER ==================== -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-main">
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          <span class="logo-olx">OLX</span><span class="logo-clone">CLONE</span>
        </a>

        <div class="location-wrapper" style="position: relative;">
          <button type="button" class="location-selector" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-location-dot location-icon"></i>
            <span class="location-text">Indonesia</span>
            <i class="fa-solid fa-chevron-down arrow-icon"></i>
          </button>
          <div class="location-menu" role="menu">
            <a href="index.php" class="location-item"><i class="fa-solid fa-map-pin"></i> Semua Indonesia</a>
            <?php foreach ($locations as $loc): ?>
              <a href="index.php?loc=<?php echo urlencode($loc); ?>" class="location-item"><i class="fa-solid fa-map-pin"></i> <?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <form class="search-form" action="index.php" method="GET" role="search" style="flex: 1; max-width: 450px;">
          <input type="text" name="q" class="search-input" placeholder="Cari mobil, gadget, properti..." aria-label="Kata kunci pencarian">
          <button type="submit" class="search-btn" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="header-actions">
          <?php if ($isLoggedIn): ?>
            <div class="user-menu-wrapper" style="position: relative;">
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
                <a href="edit-profil.php" class="dropdown-item" role="menuitem"><i class="fa-solid fa-user-pen"></i> Edit Profil</a>
                <a href="iklan-saya.php" class="dropdown-item" role="menuitem"><i class="fa-solid fa-box-open"></i> Iklan Saya</a>
                <a href="penjual.php?id=<?php echo $userId; ?>" class="dropdown-item" role="menuitem"><i class="fa-solid fa-store"></i> Toko Saya</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item danger-item" role="menuitem"><i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)</a>
              </div>
            </div>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline">Masuk</a>
          <?php endif; ?>
          <a href="pasang-iklan.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Jual</a>
        </div>
      </div>
    </div>
  </header>

  <!-- ==================== BREADCRUMB ==================== -->
  <nav class="breadcrumb-nav" aria-label="Jalur navigasi">
    <div class="container">
      <ul class="breadcrumb">
        <li><a href="index.php">Beranda</a></li>
        <li class="separator"><i class="fa-solid fa-chevron-right"></i></li>
        <li aria-current="page">Laporkan Iklan</li>
      </ul>
    </div>
  </nav>

  <!-- ==================== KONTEN UTAMA ==================== -->
  <main class="container info-page-wrapper">
    <article class="info-card">
      <header class="info-header">
        <span class="info-header-badge" style="background-color: rgba(220, 53, 69, 0.1); color: var(--danger);"><i class="fa-solid fa-flag"></i> Pengaduan Komunitas</span>
        <h1>Formulir Pengaduan Iklan</h1>
        <p>Bantu kami menjaga OLX Clone tetap aman dan tepercaya. Laporan Anda akan ditinjau secara rahasia oleh tim Trust & Safety kami.</p>
      </header>

      <div class="info-body">
        <?php if ($reportSubmitted): ?>
          <div class="alert alert-success" role="alert" style="margin-bottom: 24px;">
            <i class="fa-solid fa-circle-check"></i> Terima kasih. Laporan pengaduan Anda telah diterima dan sedang dalam antrean evaluasi tim moderator kami.
          </div>
        <?php endif; ?>

        <?php if ($reportedAd): ?>
          <div style="background-color: var(--gray-50); padding: 14px 18px; border-radius: var(--radius-sm); border: 1px solid var(--gray-200); margin-bottom: 20px;">
            <p style="margin: 0; font-size: 0.9rem;">
              <strong>Iklan yang dilaporkan:</strong>
              <a href="detail.php?id=<?php echo (int) $reportedAd['id']; ?>" style="color: var(--primary); text-decoration: underline; font-weight: 600;">
                <?php echo htmlspecialchars($reportedAd['title'], ENT_QUOTES, 'UTF-8'); ?>
              </a> (Rp <?php echo number_format($reportedAd['price'], 0, ',', '.'); ?>)
            </p>
          </div>
        <?php endif; ?>

        <form action="laporkan.php<?php echo $adId > 0 ? '?ad_id=' . $adId : ''; ?>" method="POST" style="max-width: 650px;">
          <div class="form-group" style="margin-bottom: 16px;">
            <label for="report-reason" class="form-label">Alasan Pelaporan *</label>
            <select id="report-reason" name="reason" class="form-control" required>
              <option value="">-- Pilih Jenis Pelanggaran --</option>
              <option value="penipuan">Indikasi Penipuan / Minta Transfer DP Terlebih Dahulu</option>
              <option value="barang_palsu">Barang Palsu / Replika / Ilegal</option>
              <option value="duplikat">Iklan Duplikat / Spam Berkali-kali</option>
              <option value="konten_tidak_pantas">Foto / Deskripsi Tidak Pantas</option>
              <option value="harga_tidak_wajar">Harga Palsu / Tidak Sesuai Realita</option>
              <option value="lainnya">Alasan Lainnya</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label for="report-detail" class="form-label">Rincian Kronologi / Bukti *</label>
            <textarea id="report-detail" name="detail" class="form-control" rows="4" placeholder="Jelaskan alasan kecurigaan atau bukti percakapan dengan penjual..." required></textarea>
            <span class="form-hint">Sertakan detail kronologis agar investigasi dapat dilakukan secara objektif.</span>
          </div>

          <div class="form-group" style="margin-bottom: 24px;">
            <label for="reporter-email" class="form-label">Email Anda (Untuk Pembaruan Laporan)</label>
            <input type="email" id="reporter-email" name="email" class="form-control" placeholder="nama@email.com" value="<?php echo htmlspecialchars($isLoggedIn ? $userEmail : ''); ?>">
          </div>

          <button type="submit" class="btn btn-solid-primary" style="padding: 12px 28px;">
            <i class="fa-solid fa-shield-halved"></i> Kirim Laporan Pengaduan
          </button>
        </form>
      </div>
    </article>
  </main>

  <!-- ==================== FOOTER ==================== -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h3>OLX Clone</h3>
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
        <p>&copy; 2026 OLX Clone. Dibuat untuk belajar di Kelas Fullstack Codepolitan.</p>
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
