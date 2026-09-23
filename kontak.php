<?php
/**
 * Halaman Informasi: Hubungi Kami (kontak.php)
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

$sentSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sentSuccess = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hubungi Kami — OLX Clone | Layanan Pelanggan</title>
  <meta name="description" content="Hubungi layanan pelanggan OLX Clone untuk bantuan kendala akun, pasang iklan, laporan penipuan, atau pertanyaan umum.">

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
        <li aria-current="page">Hubungi Kami</li>
      </ul>
    </div>
  </nav>

  <!-- ==================== KONTEN UTAMA ==================== -->
  <main class="container info-page-wrapper">
    <article class="info-card">
      <header class="info-header">
        <span class="info-header-badge"><i class="fa-solid fa-headset"></i> Pusat Bantuan</span>
        <h1>Hubungi Kami</h1>
        <p>Tim support kami siap membantu menjawab pertanyaan Anda dan mendengarkan masukan terkait kenyamanan jual beli di OLX Clone.</p>
      </header>

      <div class="info-body">
        <?php if ($sentSuccess): ?>
          <div class="alert alert-success" role="alert" style="margin-bottom: 24px;">
            <i class="fa-solid fa-circle-check"></i> Pesan Anda telah terkirim! Tim kami akan merespons melalui email dalam 1x24 jam kerja.
          </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
          <!-- Kolom Informasi Kontak -->
          <div>
            <h2><i class="fa-solid fa-address-book" style="color: var(--primary);"></i> Saluran Resmi</h2>
            <p>Silakan hubungi kami melalui kanal resmi berikut untuk respon cepat dari perwakilan kami:</p>

            <ul style="list-style: none; margin-left: 0; padding-left: 0;">
              <li style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div class="info-subcard-icon" style="width: 36px; height: 36px; font-size: 1rem; flex-shrink: 0;"><i class="fa-solid fa-envelope"></i></div>
                <div>
                  <strong>Email Bantuan:</strong><br>
                  <span style="color: var(--text-muted);">support@olxclone.local</span>
                </div>
              </li>
              <li style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div class="info-subcard-icon" style="width: 36px; height: 36px; font-size: 1rem; flex-shrink: 0;"><i class="fa-brands fa-whatsapp"></i></div>
                <div>
                  <strong>WhatsApp Customer Care:</strong><br>
                  <span style="color: var(--text-muted);">+62 812-3456-7890 (Senin - Jumat 09:00 - 18:00 WIB)</span>
                </div>
              </li>
              <li style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div class="info-subcard-icon" style="width: 36px; height: 36px; font-size: 1rem; flex-shrink: 0;"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                  <strong>Alamat Kantor:</strong><br>
                  <span style="color: var(--text-muted);">Gedung Cyber 2 Tower Lt. 18, Jl. HR Rasuna Said, Jakarta Selatan, 12950</span>
                </div>
              </li>
            </ul>
          </div>

          <!-- Kolom Form Kirim Pesan -->
          <div style="background-color: var(--gray-50); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
            <h3><i class="fa-solid fa-paper-plane" style="color: var(--primary);"></i> Kirim Pesan Langsung</h3>
            <form action="kontak.php" method="POST" style="margin-top: 16px;">
              <div class="form-group" style="margin-bottom: 14px;">
                <label for="contact-name" class="form-label">Nama Anda</label>
                <input type="text" id="contact-name" name="name" class="form-control" placeholder="Nama lengkap" required value="<?php echo htmlspecialchars($isLoggedIn ? $userName : ''); ?>">
              </div>
              <div class="form-group" style="margin-bottom: 14px;">
                <label for="contact-email" class="form-label">Alamat Email</label>
                <input type="email" id="contact-email" name="email" class="form-control" placeholder="nama@email.com" required value="<?php echo htmlspecialchars($isLoggedIn ? $userEmail : ''); ?>">
              </div>
              <div class="form-group" style="margin-bottom: 18px;">
                <label for="contact-message" class="form-label">Isi Pesan / Pertanyaan</label>
                <textarea id="contact-message" name="message" class="form-control" rows="4" placeholder="Tuliskan kendala atau pertanyaan Anda secara rinci..." required></textarea>
              </div>
              <button type="submit" class="btn btn-solid-primary btn-block">
                <i class="fa-solid fa-paper-plane"></i> Kirimkan Pesan
              </button>
            </form>
          </div>
        </div>

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
