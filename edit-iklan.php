<?php
session_start();
require_once __DIR__ . '/koneksi.php';

// Proteksi Autentikasi: Wajib login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan masuk ke akun Anda terlebih dahulu.";
    header("Location: login.php");
    exit;
}

$userId    = (int)$_SESSION['user_id'];
$userName  = $_SESSION['user_name'] ?? 'Pengguna';
$userEmail = $_SESSION['user_email'] ?? '';
$adId      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifikasi Kepemilikan Iklan (Keamanan Ketat: Hanya pemilik sah yang bisa mengedit)
$stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$adId, $userId]);
$ad = $stmt->fetch();

if (!$ad) {
    $_SESSION['flash_error'] = "Iklan tidak ditemukan atau Anda tidak memiliki hak akses untuk mengedit iklan tersebut.";
    header("Location: iklan-saya.php");
    exit;
}

// Ambil data kategori
$stmtCategories = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
$categories     = $stmtCategories->fetchAll();

// Ambil foto-foto yang sudah ada
$stmtImgs = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ? ORDER BY id ASC");
$stmtImgs->execute([$adId]);
$existingImages = $stmtImgs->fetchAll();

// Ambil daftar lokasi untuk header
$stmtLoc = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND TRIM(location) != '' ORDER BY location ASC");
$locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

$errors      = [];
$title       = $ad['title'];
$category_id = (int)$ad['category_id'];
$price       = (float)$ad['price'];
$location    = $ad['location'];
$description = $ad['description'];

// Proses Pembaruan Iklan (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $priceInput   = trim($_POST['price'] ?? '');
    $location     = trim($_POST['location'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $deleteImages = $_POST['delete_images'] ?? [];

    // 1. Validasi Judul (maksimal 50 karakter)
    if (empty($title)) {
        $errors[] = "Judul iklan wajib diisi.";
    } elseif (mb_strlen($title) > 50) {
        $errors[] = "Judul iklan maksimal 50 karakter.";
    }

    // 2. Validasi Kategori
    if ($category_id <= 0) {
        $errors[] = "Silakan pilih kategori iklan.";
    } else {
        $catCheck = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $catCheck->execute([$category_id]);
        if (!$catCheck->fetch()) {
            $errors[] = "Kategori yang dipilih tidak valid.";
        }
    }

    // 3. Validasi Harga
    if ($priceInput === '' || !is_numeric($priceInput) || (float)$priceInput < 0) {
        $errors[] = "Harga barang wajib berupa angka nominal positif yang valid.";
    } else {
        $price = (float)$priceInput;
    }

    // 4. Validasi Lokasi
    if (empty($location)) {
        $errors[] = "Lokasi barang wajib diisi.";
    } elseif (mb_strlen($location) > 100) {
        $errors[] = "Lokasi barang maksimal 100 karakter.";
    }

    // 5. Validasi Deskripsi
    if (empty($description)) {
        $errors[] = "Deskripsi barang wajib diisi.";
    }

    // 6. Penanganan Unggah Foto Baru Tambahan
    $newUploadedImages = [];
    $uploadDir = __DIR__ . '/uploads/ads/';

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedMimes = [
            'image/jpeg'  => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png'   => 'png',
            'image/webp'  => 'webp',
        ];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        $totalNew    = count($_FILES['images']['name']);

        // Hitung estimasi sisa foto
        $remainingExisting = count($existingImages) - count($deleteImages);
        if (($remainingExisting + $totalNew) > 5) {
            $errors[] = "Total foto setelah diunggah tidak boleh melebihi 5 foto.";
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $totalNew; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = "Gagal mengunggah foto baru ke-" . ($i + 1) . ".";
                continue;
            }

            $fileTmp  = $_FILES['images']['tmp_name'][$i];
            $fileSize = $_FILES['images']['size'][$i];
            $mimeType = $finfo->file($fileTmp);

            if (!array_key_exists($mimeType, $allowedMimes)) {
                $errors[] = "Format foto baru ke-" . ($i + 1) . " tidak didukung. Harap gunakan format JPG, PNG, atau WebP.";
                continue;
            }

            if ($fileSize > $maxFileSize) {
                $errors[] = "Ukuran foto baru ke-" . ($i + 1) . " melebihi batas 5MB.";
                continue;
            }

            $extension   = $allowedMimes[$mimeType];
            $newFileName = 'ad_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destination)) {
                $newUploadedImages[] = 'uploads/ads/' . $newFileName;
            } else {
                $errors[] = "Gagal memindahkan file foto baru ke-" . ($i + 1) . ".";
            }
        }
    }

    // Eksekusi Update Database dalam Transaksi PDO
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // A. Update data iklan di tabel ads
            $updateStmt = $pdo->prepare("
                UPDATE ads 
                SET category_id = ?, title = ?, description = ?, price = ?, location = ?
                WHERE id = ? AND user_id = ?
            ");
            $updateStmt->execute([
                $category_id,
                $title,
                $description,
                $price,
                $location,
                $adId,
                $userId
            ]);

            // B. Hapus foto lama yang dicentang pengguna
            if (!empty($deleteImages)) {
                $delSelectStmt = $pdo->prepare("SELECT id, image_path FROM ad_images WHERE id = ? AND ad_id = ?");
                $delStmt       = $pdo->prepare("DELETE FROM ad_images WHERE id = ? AND ad_id = ?");

                foreach ($deleteImages as $delImgId) {
                    $delImgId = (int)$delImgId;
                    $delSelectStmt->execute([$delImgId, $adId]);
                    $row = $delSelectStmt->fetch();

                    if ($row) {
                        $filePath = __DIR__ . '/' . $row['image_path'];
                        if (file_exists($filePath) && is_file($filePath)) {
                            @unlink($filePath);
                        }
                        $delStmt->execute([$delImgId, $adId]);
                    }
                }
            }

            // C. Simpan foto-foto baru yang diunggah
            if (!empty($newUploadedImages)) {
                $insImgStmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)");
                foreach ($newUploadedImages as $newPath) {
                    $insImgStmt->execute([$adId, $newPath]);
                }
            }

            $pdo->commit();

            $_SESSION['flash_success'] = "Iklan \"" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\" berhasil diperbarui!";
            header("Location: iklan-saya.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            // Bersihkan file baru jika transaksi gagal
            foreach ($newUploadedImages as $failedPath) {
                $failedFile = __DIR__ . '/' . $failedPath;
                if (file_exists($failedFile) && is_file($failedFile)) {
                    @unlink($failedFile);
                }
            }
            $errors[] = "Terjadi kesalahan pada sistem database: " . $e->getMessage();
        }
    }
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
  <title>Edit Iklan: <?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?> — OLX Clone</title>
  <meta name="robots" content="noindex, nofollow">

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
       HEADER
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
        <form class="search-form" action="index.php" method="GET" role="search" aria-label="Cari iklan">
          <label for="search-input" class="sr-only">Cari di OLX Clone</label>
          <input type="search" id="search-input" name="q" placeholder="Cari mobil, HP, properti, dan lainnya..." autocomplete="off">
          <button type="submit" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <!-- Auth Actions -->
        <div class="header-actions">
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
              <a href="iklan-saya.php" class="dropdown-item" role="menuitem">
                <i class="fa-solid fa-box-open"></i> Iklan Saya
              </a>
              <div class="dropdown-divider"></div>
              <a href="logout.php" class="dropdown-item danger-item" role="menuitem">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
              </a>
            </div>
          </div>
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
        <li><a href="iklan-saya.php">Iklan Saya</a></li>
        <li aria-current="page">Edit Iklan #<?php echo str_pad((string)$adId, 5, '0', STR_PAD_LEFT); ?></li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN FORM EDIT IKLAN
       ================================================================ -->
  <main id="main-content" class="container post-ad-layout" role="main">

    <div class="post-ad-main">

      <!-- Header Judul -->
      <div class="post-ad-header">
        <h1>Edit Iklan</h1>
        <p>Perbarui rincian, foto, harga, atau lokasi untuk listing #<?php echo str_pad((string)$adId, 5, '0', STR_PAD_LEFT); ?>.</p>
      </div>

      <!-- Pesan Kesalahan / Error -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
          <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></span>
          <div class="alert-content">
            <strong>Mohon perbaiki kesalahan berikut:</strong>
            <ul style="margin-top: 6px; padding-left: 18px;">
              <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <button type="button" class="alert-close" aria-label="Tutup pesan error">&times;</button>
        </div>
      <?php endif; ?>

      <form action="edit-iklan.php?id=<?php echo (int)$adId; ?>" method="POST" enctype="multipart/form-data" novalidate id="edit-ad-form">

        <!-- ===== KARTU 1: KATEGORI ===== -->
        <section class="post-ad-card">
          <h2 class="post-ad-card-title">
            <i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Pilih Kategori
          </h2>
          <div class="form-group">
            <label for="category_id" class="form-label">Kategori Barang <span style="color: var(--danger);">*</span></label>
            <div class="input-wrapper">
              <span class="input-icon"><i class="fa-solid fa-tags"></i></span>
              <select name="category_id" id="category_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo (int)$cat['id']; ?>" <?php echo ($category_id === (int)$cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <!-- ===== KARTU 2: RINCIAN IKLAN ===== -->
        <section class="post-ad-card">
          <h2 class="post-ad-card-title">
            <i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Rincian Iklan
          </h2>

          <!-- Judul Iklan -->
          <div class="form-group" style="margin-bottom: 18px;">
            <div class="char-counter-row">
              <label for="title" class="form-label">Judul Iklan <span style="color: var(--danger);">*</span></label>
              <span class="char-count" id="title-counter"><?php echo mb_strlen($title); ?> / 50 karakter</span>
            </div>
            <div class="input-wrapper">
              <span class="input-icon"><i class="fa-solid fa-heading"></i></span>
              <input type="text" id="title" name="title" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <span class="form-hint">Sebutkan nama barang, merk, dan tipe secara ringkas (maksimal 50 karakter).</span>
          </div>

          <!-- Deskripsi -->
          <div class="form-group">
            <label for="description" class="form-label">Deskripsi Lengkap <span style="color: var(--danger);">*</span></label>
            <textarea id="description" name="description" class="form-control" rows="6" style="padding-left: 14px; resize: vertical;" required><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <span class="form-hint">Jelaskan kondisi fisik barang, kelengkapan surat/dus, minus, dan riwayat pemakaian.</span>
          </div>
        </section>

        <!-- ===== KARTU 3: PENGELOLAAN FOTO ===== -->
        <section class="post-ad-card">
          <h2 class="post-ad-card-title">
            <i class="fa-solid fa-camera" style="color: var(--primary);"></i> Foto Barang
          </h2>

          <!-- Foto Lama yang Tersimpan -->
          <?php if (!empty($existingImages)): ?>
            <p style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
              Foto yang Tersimpan (Centang untuk menghapus foto):
            </p>
            <div class="existing-photos-grid">
              <?php foreach ($existingImages as $img): ?>
                <div class="existing-photo-item">
                  <img src="<?php echo htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto Listing">
                  <label class="existing-photo-delete-label">
                    <input type="checkbox" name="delete_images[]" value="<?php echo (int)$img['id']; ?>">
                    <span><i class="fa-solid fa-trash-can"></i> Hapus Foto</span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Dropzone Unggah Foto Tambahan -->
          <p style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
            Unggah Foto Baru Tambahan:
          </p>
          <div class="photo-upload-zone" id="photo-dropzone">
            <input type="file" id="ad-images-input" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
            <span class="photo-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
            <span class="photo-upload-text">Klik atau seret foto baru ke sini</span>
            <span class="photo-upload-subtext">Format: JPG, PNG, WebP (Maksimal 5MB per foto, total maksimal 5 foto)</span>
          </div>

          <div class="photo-slots-grid">
            <div class="photo-slot primary-slot"><i class="fa-solid fa-image"></i><span>Slot 1</span></div>
            <div class="photo-slot"><i class="fa-solid fa-image"></i><span>Slot 2</span></div>
            <div class="photo-slot"><i class="fa-solid fa-image"></i><span>Slot 3</span></div>
            <div class="photo-slot"><i class="fa-solid fa-image"></i><span>Slot 4</span></div>
            <div class="photo-slot"><i class="fa-solid fa-image"></i><span>Slot 5</span></div>
          </div>
        </section>

        <!-- ===== KARTU 4: HARGA & LOKASI ===== -->
        <section class="post-ad-card">
          <h2 class="post-ad-card-title">
            <i class="fa-solid fa-money-bill-wave" style="color: var(--primary);"></i> Harga & Lokasi
          </h2>

          <!-- Harga -->
          <div class="form-group" style="margin-bottom: 18px;">
            <label for="price" class="form-label">Harga Barang <span style="color: var(--danger);">*</span></label>
            <div class="price-input-wrapper">
              <span class="price-prefix">Rp</span>
              <input type="number" id="price" name="price" min="0" step="1000" value="<?php echo htmlspecialchars((string)(int)$price, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

          <!-- Lokasi -->
          <div class="form-group">
            <label for="location" class="form-label">Lokasi / Kota <span style="color: var(--danger);">*</span></label>
            <div class="input-wrapper">
              <span class="input-icon"><i class="fa-solid fa-location-dot"></i></span>
              <input type="text" id="location" name="location" class="form-control" maxlength="100" value="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <span class="form-hint">Contoh: Jakarta Selatan, Surabaya, Bandung, Medan</span>
          </div>
        </section>

        <!-- Tombol Aksi Simpan / Batal -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 10px;">
          <a href="iklan-saya.php" class="btn btn-outline">
            <i class="fa-solid fa-xmark"></i> Batal
          </a>
          <button type="submit" class="btn btn-solid-primary" style="padding: 12px 28px; font-size: 1rem;">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>

      </form>

    </div>

    <!-- Sidebar Informasi Bantuan -->
    <aside class="post-ad-sidebar">
      <div class="tips-card">
        <div class="tips-card-header">
          <i class="fa-solid fa-lightbulb" style="color: var(--warning);"></i>
          <h3>Tips Mengubah Iklan</h3>
        </div>
        <ul class="tips-list">
          <li>
            <span class="tips-num">1</span>
            <span><strong>Perbarui Harga:</strong> Menyesuaikan harga dengan kondisi pasar dapat meningkatkan minat calon pembeli.</span>
          </li>
          <li>
            <span class="tips-num">2</span>
            <span><strong>Foto Berkualitas:</strong> Anda dapat menghapus foto yang kurang jelas dan menggantinya dengan foto sudut lain.</span>
          </li>
          <li>
            <span class="tips-num">3</span>
            <span><strong>Kejujuran Deskripsi:</strong> Sebutkan jika ada pembaruan kondisi atau kelengkapan barang.</span>
          </li>
        </ul>
      </div>
    </aside>

  </main>


  <!-- ================================================================
       FOOTER
       ================================================================ -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">
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
