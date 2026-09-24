<?php
    /**
 * Halaman Edit Iklan (edit-iklan.php)
 * Jualin - Codepolitan
 *
 * Fitur:
 * 1. Proteksi Autentikasi ketat (wajib login).
 * 2. Otorisasi Kepemilikan (hanya pemilik listing yang berhak mengedit).
 * 3. Selaras 100% dengan struktur form dan kartu pada pasang-iklan.php.
 * 4. Pengelolaan foto tersimpan (opsi hapus berkas fisik uploads/ads/ secara bersih).
 * 5. Unggah foto baru tambahan (maksimal total akumulasi 5 foto).
 * 6. Transaksi database PDO untuk menjamin integritas ads & ad_images.
 */

    session_start();
    require_once __DIR__ . '/koneksi.php';

    // Proteksi Autentikasi: Wajib login
    if (! isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan masuk ke akun Anda terlebih dahulu.";
    header("Location: login.php");
    exit;
    }

    $userId    = (int) $_SESSION['user_id'];
    $userName  = $_SESSION['user_name'] ?? 'Pengguna';
    $userEmail = $_SESSION['user_email'] ?? '';
    $adId      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;

    // Verifikasi Kepemilikan Iklan (Keamanan Ketat: Hanya pemilik sah yang bisa mengedit)
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$adId, $userId]);
    $ad = $stmt->fetch();

    if (! $ad) {
    $_SESSION['flash_error'] = "Iklan tidak ditemukan atau Anda tidak memiliki hak akses untuk mengedit iklan tersebut.";
    header("Location: iklan-saya.php");
    exit;
    }

    // Ambil data user aktif untuk kartu profil penjual
    $stmtUser = $pdo->prepare("SELECT id, name, email, whatsapp FROM users WHERE id = ? LIMIT 1");
    $stmtUser->execute([$userId]);
    $currentUser = $stmtUser->fetch();

    // Ambil data kategori
    $stmtCategories = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
    $categories     = $stmtCategories->fetchAll();

    // Ambil foto-foto yang sudah ada
    $stmtImgs = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ? ORDER BY id ASC");
    $stmtImgs->execute([$adId]);
    $existingImages = $stmtImgs->fetchAll();

    // Ambil daftar lokasi untuk header
    $stmtLoc   = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND TRIM(location) != '' ORDER BY location ASC");
    $locations = $stmtLoc->fetchAll(PDO::FETCH_COLUMN);

    $errors      = [];
    $title       = $ad['title'];
    $category_id = (int) $ad['category_id'];
    $price       = (float) $ad['price'];
    $location    = $ad['location'];
    $description = $ad['description'];

    // Proses Pembaruan Iklan (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $category_id  = (int) ($_POST['category_id'] ?? 0);
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
        if (! $catCheck->fetch()) {
            $errors[] = "Kategori yang dipilih tidak valid.";
        }
    }

    // 3. Validasi Harga
    if ($priceInput === '' || ! is_numeric($priceInput) || (float) $priceInput < 0) {
        $errors[] = "Harga barang wajib berupa angka nominal positif yang valid.";
    } else {
        $price = (float) $priceInput;
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
    $uploadDir         = __DIR__ . '/uploads/ads/';

    if (isset($_FILES['images']) && ! empty($_FILES['images']['name'][0])) {
        if (! is_dir($uploadDir)) {
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
            $errors[] = "Total akumulasi foto setelah disimpan tidak boleh melebihi 5 foto.";
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

            if (! array_key_exists($mimeType, $allowedMimes)) {
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
                $userId,
            ]);

            // B. Hapus foto lama yang dicentang pengguna
            if (! empty($deleteImages)) {
                $delSelectStmt = $pdo->prepare("SELECT id, image_path FROM ad_images WHERE id = ? AND ad_id = ?");
                $delStmt       = $pdo->prepare("DELETE FROM ad_images WHERE id = ? AND ad_id = ?");

                foreach ($deleteImages as $delImgId) {
                    $delImgId = (int) $delImgId;
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
            if (! empty($newUploadedImages)) {
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
  <title>Edit Iklan: <?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?> — Jualin</title>
  <meta name="description" content="Perbarui rincian, foto, harga, dan lokasi iklan Anda di Jualin.">
  <meta name="robots" content="noindex, follow">

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
       HEADER — Logo, Lokasi, Search, Auth
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="Jualin - Halaman Utama">
          Jual<span>in</span>
        </a>

        <!-- Auth Action Navigasi -->
        <div class="header-actions">
          <a href="iklan-saya.php" class="btn btn-outline" aria-label="Kembali ke Iklan Saya">
            <i class="fa-solid fa-arrow-left"></i> Iklan Saya
          </a>
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
              <a href="penjual.php?id=<?php echo (int) $userId; ?>" class="dropdown-item" role="menuitem">
                <i class="fa-solid fa-store"></i> Toko Saya
              </a>
              <div class="dropdown-divider"></div>
              <a href="logout.php" class="dropdown-item danger-item" role="menuitem">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
              </a>
            </div>
          </div>
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
        <li aria-current="page">Edit Iklan #<?php echo str_pad((string) $adId, 5, '0', STR_PAD_LEFT); ?></li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN UTAMA: FORM EDIT IKLAN (SELARAS DENGAN PASANG-IKLAN.PHP)
       ================================================================ -->
  <main class="container" id="main-content" role="main">
    <div class="post-ad-layout">

      <!-- ==================== KOLOM KIRI: FORM EDIT IKLAN ==================== -->
      <div class="post-ad-main">

        <!-- Banner Header Form -->
        <div class="post-ad-header">
          <h1>Edit Iklan Listing</h1>
          <p>Perbarui rincian, foto, harga, atau lokasi untuk iklan #<?php echo str_pad((string) $adId, 5, '0', STR_PAD_LEFT); ?> di bawah ini.</p>
        </div>

        <!-- NOTIFIKASI ERROR JIKA ADA -->
        <?php if (! empty($errors)): ?>
          <div class="alert alert-danger" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert-content">
              <strong>Gagal Memperbarui Iklan:</strong>
              <ul style="margin: 6px 0 0 16px; list-style: disc;">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
          </div>
        <?php endif; ?>

        <!-- FORM UTAMA DENGAN ENCTYPE MULTIPART -->
        <form class="post-ad-form" action="edit-iklan.php?id=<?php echo (int) $adId; ?>" method="POST" enctype="multipart/form-data">

          <!-- ==================== CARD 1: PILIH KATEGORI (Selaras dengan pasang-iklan.php) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-cat">
            <h2 id="heading-cat" class="post-ad-card-title">
              <i class="fa-solid fa-tags" aria-hidden="true"></i> 1. Pilih Kategori Iklan
            </h2>
            <p class="form-hint" style="margin-bottom: 14px;">
              Pilih kategori yang paling sesuai dengan barang atau jasa yang Anda iklankan.
            </p>

            <div class="form-group">
              <label for="category_id" class="form-label">Kategori Barang *</label>
              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-list"></i></span>
                <select name="category_id" id="category_id" class="form-control" required aria-required="true">
                  <option value="" disabled>-- Pilih Kategori Barang --</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int) $cat['id']; ?>" <?php echo((int) $category_id === (int) $cat['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <span class="form-hint">Klik dropdown di atas untuk memilih kategori yang sesuai dengan barang Anda.</span>
            </div>
          </section>


          <!-- ==================== CARD 2: PENGELOLAAN FOTO (Selaras dengan pasang-iklan.php) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-photos">
            <h2 id="heading-photos" class="post-ad-card-title">
              <i class="fa-solid fa-camera" aria-hidden="true"></i> 2. Unggah Foto Barang (Maks. 5 Foto)
            </h2>

            <!-- Foto yang Tersimpan di Database -->
            <?php if (! empty($existingImages)): ?>
              <div style="margin-bottom: 22px;">
                <label class="form-label" style="display: flex; align-items: center; justify-content: space-between;">
                  <span>Foto yang Tersimpan (<?php echo count($existingImages); ?> Foto)</span>
                  <span style="font-size: 0.8rem; color: var(--danger); font-weight: normal;">
                    <i class="fa-solid fa-trash-can"></i> Centang foto yang ingin dihapus
                  </span>
                </label>
                <div class="existing-photos-grid">
                  <?php foreach ($existingImages as $img): ?>
                    <div class="existing-photo-item">
                      <img src="<?php echo htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto Listing">
                      <label class="existing-photo-delete-label">
                        <input type="checkbox" name="delete_images[]" value="<?php echo (int) $img['id']; ?>">
                        <span><i class="fa-solid fa-trash-can"></i> Hapus Foto</span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Dropzone Upload Foto Baru Tambahan -->
            <label class="photo-upload-zone" for="ad-images-input">
              <span class="photo-upload-icon" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></span>
              <span class="photo-upload-text">Klik di sini untuk memilih foto baru atau seret foto ke sini</span>
              <span class="photo-upload-subtext">Format: JPG, JPEG, PNG, atau WebP (Maksimal 5MB per foto, akumulasi maksimal 5 foto)</span>
              <input
                type="file"
                id="ad-images-input"
                name="images[]"
                multiple
                accept="image/jpeg, image/png, image/webp"
                aria-describedby="photo-rules-text">
            </label>

            <span id="photo-rules-text" class="form-hint" style="margin-top: 10px; display: block;">
              <i class="fa-solid fa-lightbulb" style="color: var(--accent);"></i> <strong>Tips:</strong> Foto pertama akan otomatis menjadi foto sampul utama pada halaman pencarian.
            </span>

            <!-- Slot Pratinjau Foto Baru -->
            <div class="photo-slots-grid" aria-label="Slot Foto Baru">
              <div class="photo-slot primary-slot" title="Foto Baru 1">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 1</span>
              </div>
              <div class="photo-slot" title="Foto Baru 2">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 2</span>
              </div>
              <div class="photo-slot" title="Foto Baru 3">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 3</span>
              </div>
              <div class="photo-slot" title="Foto Baru 4">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 4</span>
              </div>
              <div class="photo-slot" title="Foto Baru 5">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 5</span>
              </div>
            </div>
          </section>


          <!-- ==================== CARD 3: DETAIL IKLAN (Selaras dengan pasang-iklan.php) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-info">
            <h2 id="heading-info" class="post-ad-card-title">
              <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> 3. Detail & Informasi Iklan
            </h2>

            <!-- Judul Iklan (ads.title - VARCHAR 50) -->
            <div class="form-group" style="margin-bottom: 20px;">
              <div class="char-counter-row">
                <label for="title" class="form-label">Judul Iklan *</label>
                <span id="title-counter" class="char-count"><?php echo mb_strlen($title); ?> / 50 karakter</span>
              </div>
              <input
                type="text"
                id="title"
                name="title"
                class="form-control"
                style="padding-left: 14px;"
                placeholder="Contoh: Toyota Avanza 1.3 G MT 2020 Putih Mulus"
                required
                maxlength="50"
                value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                aria-required="true">
              <span class="form-hint">Maksimal 50 karakter. Tulis merek, tipe, atau fitur unggulan barang Anda.</span>
            </div>

            <!-- Deskripsi Iklan (ads.description - TEXT) -->
            <div class="form-group">
              <label for="description" class="form-label">Deskripsi Lengkap *</label>
              <textarea
                id="description"
                name="description"
                class="form-control"
                style="padding-left: 14px; min-height: 160px; resize: vertical;"
                placeholder="Jelaskan kondisi barang secara jujur, kelengkapan aksesori, riwayat servis/pemakaian, alasan dijual, dan informasi penting lainnya..."
                required
                rows="6"
                aria-required="true"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
              <span class="form-hint">Iklan dengan deskripsi detail mendapatkan respons pembeli 3x lebih banyak.</span>
            </div>
          </section>


          <!-- ==================== CARD 4: TENTUKAN HARGA (Selaras dengan pasang-iklan.php) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-price">
            <h2 id="heading-price" class="post-ad-card-title">
              <i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i> 4. Tentukan Harga
            </h2>

            <div class="form-group" style="margin-bottom: 12px;">
              <label for="price" class="form-label">Harga Barang (Rp) *</label>
              <div class="price-input-wrapper">
                <span class="price-prefix" aria-hidden="true">Rp</span>
                <input
                  type="number"
                  id="price"
                  name="price"
                  placeholder="0"
                  min="0"
                  step="1000"
                  required
                  value="<?php echo htmlspecialchars((string) (int) $price, ENT_QUOTES, 'UTF-8'); ?>"
                  aria-required="true">
              </div>
              <span class="form-hint">Tuliskan nominal angka saja tanpa titik atau koma (contoh: 185000000).</span>
            </div>
          </section>


          <!-- ==================== CARD 5: LOKASI BARANG (Selaras dengan pasang-iklan.php) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-location">
            <h2 id="heading-location" class="post-ad-card-title">
              <i class="fa-solid fa-location-dot" aria-hidden="true"></i> 5. Lokasi Barang
            </h2>

            <div class="form-group">
              <label for="location" class="form-label">Kota / Wilayah *</label>
              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                <input
                  type="text"
                  id="location"
                  name="location"
                  class="form-control"
                  placeholder="Contoh: Jakarta Selatan, Cilandak"
                  required
                  maxlength="100"
                  value="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>"
                  aria-required="true">
              </div>
              <span class="form-hint">Cantumkan nama kota dan kecamatan agar calon pembeli terdekat mudah menemukan iklan Anda.</span>
            </div>
          </section>


          <!-- ==================== CARD 6: PROFIL PENJUAL (Selaras dengan pasang-iklan.php) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-seller">
            <h2 id="heading-seller" class="post-ad-card-title">
              <i class="fa-solid fa-user" aria-hidden="true"></i> 6. Profil Penjual
            </h2>

            <div style="display: flex; align-items: center; gap: 14px; background-color: var(--gray-50); padding: 14px 16px; border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
              <div class="seller-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;" aria-hidden="true">
                <?php echo strtoupper(substr($currentUser['name'] ?? $userName, 0, 1)); ?>
              </div>
              <div>
                <p style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2px;"><?php echo htmlspecialchars($currentUser['name'] ?? $userName, ENT_QUOTES, 'UTF-8'); ?></p>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;"><?php echo htmlspecialchars($currentUser['email'] ?? $userEmail, ENT_QUOTES, 'UTF-8'); ?> &bull; Akun Terverifikasi</p>
                <?php if (! empty($currentUser['whatsapp'])): ?>
                  <p style="font-size: 0.82rem; color: #128c7e; font-weight: 600; margin: 0;">
                    <i class="fa-brands fa-whatsapp"></i> <?php echo htmlspecialchars($currentUser['whatsapp'], ENT_QUOTES, 'UTF-8'); ?> (Aktif untuk chat pembeli)
                  </p>
                <?php else: ?>
                  <p style="font-size: 0.8rem; color: var(--warning); margin: 0;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Nomor WhatsApp belum diatur &bull; <a href="edit-profil.php" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Atur di Profil</a> agar pembeli dapat langsung chat via WhatsApp.
                  </p>
                <?php endif; ?>
              </div>
            </div>
            <span class="form-hint" style="margin-top: 8px; display: block;">
              Iklan ini dikelola menggunakan akun Anda yang sedang aktif.
            </span>
          </section>


          <!-- ==================== SUBMIT SECTION ==================== -->
          <div class="post-ad-card" style="display: flex; align-items: center; justify-content: flex-end; gap: 14px;">
            <a href="iklan-saya.php" class="btn btn-outline" style="padding: 12px 24px;">
              <i class="fa-solid fa-xmark"></i> Batal
            </a>
            <button type="submit" class="btn btn-solid-primary" style="padding: 12px 28px; font-size: 1rem;">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
          </div>

        </form>

      </div>


      <!-- ==================== KOLOM KANAN: SIDEBAR PANDUAN ==================== -->
      <aside class="post-ad-sidebar" aria-label="Panduan Edit Iklan">

        <!-- Card Tips Edit Iklan -->
        <div class="tips-card">
          <div class="tips-card-header">
            <i class="fa-solid fa-lightbulb" style="color: var(--accent); font-size: 1.2rem;"></i>
            <h3>Tips Mengubah Iklan</h3>
          </div>
          <ul class="tips-list">
            <li>
              <span class="tips-num">1</span>
              <div>
                <strong>Perbarui Harga Pasar:</strong>
                <p>Menyesuaikan harga yang bersaing dapat mempercepat transaksi jual beli.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">2</span>
              <div>
                <strong>Ganti Foto Berkualitas:</strong>
                <p>Hapus foto yang buram dan gantikan dengan foto beresolusi tinggi dari sudut terbaik.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">3</span>
              <div>
                <strong>Deskripsi Lengkap & Jujur:</strong>
                <p>Sertakan riwayat pemakaian terbaru, minus, atau kelengkapan tambahan barang.</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Card Bantuan -->
        <div class="rules-card">
          <div class="rules-card-header">
            <i class="fa-solid fa-circle-question" style="color: var(--primary); font-size: 1.2rem;"></i>
            <h3>Butuh Bantuan?</h3>
          </div>
          <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 12px;">
            Perubahan informasi iklan akan langsung terbarui di katalog pencarian Jualin segera setelah Anda menekan tombol simpan.
          </p>
          <a href="iklan-saya.php" class="btn btn-outline btn-block" style="font-size: 0.82rem; justify-content: center;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Iklan Saya
          </a>
        </div>

      </aside>

    </div>
  </main>


  <!-- ================================================================
       FOOTER
       ================================================================ -->
  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Jualin. Hak Cipta Dilindungi Undang-Undang.</p>
        <div class="footer-badges">
          <span class="badge-tag"><i class="fa-solid fa-shield-halved"></i> Transaksi Aman</span>
          <span class="badge-tag"><i class="fa-solid fa-check-double"></i> Bebas Biaya</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- SCRIPT UTAMA -->
  <script src="assets/js/main.js"></script>

</body>

</html>
