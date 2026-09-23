<?php
    /**
 * ============================================================================
 * HALAMAN PASANG IKLAN (POST AD) — DINAMIS PDO & MYSQL
 * Terkoneksi dengan Database: olx_clone
 * Tabel Terkait:
 *   - ads (user_id, category_id, title, description, price, location, created_at)
 *   - ad_images (ad_id, image_path)
 *   - categories (id, name, icon)
 *   - users (id, name, email)
 * ============================================================================
 */

    session_start();
    require_once __DIR__ . '/koneksi.php';

    // Proteksi Autentikasi: Wajib login untuk memasang iklan
    if (! isset($_SESSION['user_id'])) {
    $_SESSION['flash_success'] = "Silakan masuk ke akun Anda terlebih dahulu untuk memasang iklan.";
    header("Location: login.php");
    exit;
    }

    // Ambil data user aktif lengkap dengan nomor whatsapp
    $stmtUser = $pdo->prepare("SELECT id, name, email, whatsapp FROM users WHERE id = ? LIMIT 1");
    $stmtUser->execute([$_SESSION['user_id']]);
    $currentUser = $stmtUser->fetch() ?: [
        'id'       => (int) $_SESSION['user_id'],
        'name'     => $_SESSION['user_name'] ?? 'Pengguna',
        'email'    => $_SESSION['user_email'] ?? '',
        'whatsapp' => null,
    ];

    // Ambil data kategori secara dinamis dari tabel categories
    $stmtCategories = $pdo->query("SELECT id, name, icon FROM categories ORDER BY id ASC");
    $categories     = $stmtCategories->fetchAll();

    $errors        = [];
    $title         = '';
    $category_id   = '';
    $price         = '';
    $location      = '';
    $description   = '';
    $is_negotiable = 1;

    // Proses Form Submission (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title'] ?? '');
    $category_id   = (int) ($_POST['category_id'] ?? 0);
    $price         = trim($_POST['price'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $agree_rules   = isset($_POST['agree_rules']);
    $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;

    // 1. Validasi Judul (ads.title - VARCHAR 50)
    if (empty($title)) {
        $errors[] = "Judul iklan wajib diisi.";
    } elseif (mb_strlen($title) > 50) {
        $errors[] = "Judul iklan maksimal 50 karakter (sesuai spesifikasi sistem).";
    }

    // 2. Validasi Kategori (ads.category_id)
    if ($category_id <= 0) {
        $errors[] = "Silakan pilih kategori iklan dari dropdown.";
    } else {
        $catCheck = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $catCheck->execute([$category_id]);
        if (! $catCheck->fetch()) {
            $errors[] = "Kategori yang dipilih tidak terdaftar di sistem.";
        }
    }

    // 3. Validasi Harga (ads.price - DECIMAL 15,2)
    if ($price === '' || ! is_numeric($price) || (float) $price < 0) {
        $errors[] = "Harga barang wajib diisi dengan nominal angka yang valid (contoh: 185000000).";
    }

    // 4. Validasi Lokasi (ads.location - VARCHAR 100)
    if (empty($location)) {
        $errors[] = "Lokasi barang (kota/wilayah) wajib diisi.";
    } elseif (mb_strlen($location) > 100) {
        $errors[] = "Lokasi barang maksimal 100 karakter.";
    }

    // 5. Validasi Deskripsi (ads.description - TEXT)
    if (empty($description)) {
        $errors[] = "Deskripsi barang wajib diisi secara jelas dan jujur.";
    }

    // 6. Validasi Syarat & Ketentuan
    if (! $agree_rules) {
        $errors[] = "Anda wajib menyetujui Syarat & Ketentuan Pasang Iklan.";
    }

    // 7. Penanganan Unggah Foto (ad_images)
    $uploadedImages = [];
    $uploadDir      = __DIR__ . '/uploads/ads/';

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
        $totalFiles  = count($_FILES['images']['name']);

        if ($totalFiles > 5) {
            $errors[] = "Maksimal hanya dapat mengunggah 5 foto barang.";
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < min($totalFiles, 5); $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = "Gagal mengunggah foto ke-" . ($i + 1) . ".";
                continue;
            }

            $fileTmp  = $_FILES['images']['tmp_name'][$i];
            $fileSize = $_FILES['images']['size'][$i];
            $mimeType = $finfo->file($fileTmp);

            if (! array_key_exists($mimeType, $allowedMimes)) {
                $errors[] = "Format foto ke-" . ($i + 1) . " tidak didukung. Harap gunakan format JPG, PNG, atau WebP.";
                continue;
            }

            if ($fileSize > $maxFileSize) {
                $errors[] = "Ukuran foto ke-" . ($i + 1) . " melebihi batas 5MB.";
                continue;
            }

            $extension   = $allowedMimes[$mimeType];
            $newFileName = 'ad_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destination)) {
                $uploadedImages[] = 'uploads/ads/' . $newFileName;
            } else {
                $errors[] = "Terjadi kegagalan saat menyimpan file foto ke-" . ($i + 1) . ".";
            }
        }
    }

    // Eksekusi Database dengan Transaksi PDO
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Simpan data iklan ke tabel ads
            $stmt = $pdo->prepare("INSERT INTO ads (user_id, category_id, title, description, price, location, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $currentUser['id'],
                $category_id,
                $title,
                $description,
                (float) $price,
                $location,
            ]);

            $newAdId = (int) $pdo->lastInsertId();

            // Simpan referensi foto ke tabel ad_images
            if (! empty($uploadedImages)) {
                $imgStmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)");
                foreach ($uploadedImages as $imgPath) {
                    $imgStmt->execute([$newAdId, $imgPath]);
                }
            }

            $pdo->commit();

            $_SESSION['flash_success'] = "Selamat! Iklan \"" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\" berhasil dipasang dan sudah tayang.";
            header("Location: detail.php?id=" . $newAdId);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            // Bersihkan file yang sudah sempat terunggah jika DB rollback
            foreach ($uploadedImages as $relativePath) {
                $fullPath = __DIR__ . '/' . $relativePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            error_log("Gagal memasang iklan: " . $e->getMessage());
            $errors[] = "Terjadi kesalahan internal saat menyimpan iklan ke database: " . $e->getMessage();
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
  <title>Pasang Iklan Gratis — Jual Cepat Barang Bekas & Baru | OLX Clone</title>
  <meta name="description" content="Pasang iklan gratis di OLX Clone sekarang! Jual mobil, motor, properti, HP, laptop, perabotan, dan jasa dengan mudah ke jutaan calon pembeli di seluruh Indonesia.">
  <meta name="keywords" content="pasang iklan gratis, jual barang bekas, pasang iklan OLX, jual mobil, jual motor, jual properti, pasang iklan online">
  <meta name="author" content="OLX Clone">
  <!-- Best Practice SEO: noindex, follow untuk halaman formulir pasang iklan agar bot crawler tidak mengindeks form kosong -->
  <meta name="robots" content="noindex, follow">
  <link rel="canonical" href="https://olxclone.local/pasang-iklan.php">

  <!-- ==================== OPEN GRAPH (Social Media Preview) ==================== -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="Pasang Iklan Gratis — OLX Clone">
  <meta property="og:description" content="Punya barang nganggur? Jual cepat sekarang di OLX Clone. Gratis dan mudah!">
  <meta property="og:url" content="https://olxclone.local/pasang-iklan.php">
  <meta property="og:image" content="https://olxclone.local/assets/images/og-preview.jpg">
  <meta property="og:site_name" content="OLX Clone">
  <meta property="og:locale" content="id_ID">

  <!-- ==================== TWITTER CARD ==================== -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="Pasang Iklan Gratis — OLX Clone">
  <meta name="twitter:description" content="Pasang iklan jual beli online gratis dan jangkau jutaan pembeli terdekat.">
  <meta name="twitter:image" content="https://olxclone.local/assets/images/og-preview.jpg">

  <!-- ==================== FAVICON ==================== -->
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">

  <!-- ==================== JSON-LD STRUCTURED DATA ==================== -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Pasang Iklan Gratis — OLX Clone",
      "url": "https://olxclone.local/pasang-iklan.php",
      "description": "Formulir pemasangan iklan jual beli barang baru dan bekas di OLX Clone.",
      "breadcrumb": {
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
            "name": "Pasang Iklan",
            "item": "https://olxclone.local/pasang-iklan.php"
          }
        ]
      }
    }
  </script>

  <!-- ==================== FONT AWESOME ICONS ==================== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- ==================== CSS EXTERNAL ==================== -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- ================================================================
       HEADER (Konsisten dengan index.php, detail.php, login.php & register.php)
       ================================================================ -->
  <header class="site-header" role="banner">
    <div class="container">
      <div class="header-top">

        <!-- Logo -->
        <a href="index.php" class="logo" aria-label="OLX Clone - Halaman Utama">
          OLX<span>Clone</span>
        </a>
        
        <!-- Auth Action Navigasi -->
        <div class="header-actions">
          <a href="index.php" class="btn btn-outline" aria-label="Kembali ke beranda">
            <i class="fa-solid fa-arrow-left"></i> Beranda
          </a>
          <div class="user-menu-wrapper">
            <button type="button" class="user-menu-btn" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar-sm"><?php echo strtoupper(substr($currentUser['name'], 0, 1)) ?></span>
              <span class="user-menu-name"><?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?></span>
              <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <div class="user-dropdown" role="menu">
              <div style="padding: 10px 16px; border-bottom: 1px solid var(--gray-200);">
                <strong style="display: block; font-size: 0.88rem; color: var(--text-primary);"><?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <small style="color: var(--text-muted); font-size: 0.75rem;"><?php echo htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></small>
              </div>
              <a href="edit-profil.php" class="dropdown-item" role="menuitem">
                <i class="fa-solid fa-user-pen"></i> Edit Profil
              </a>
              <a href="iklan-saya.php" class="dropdown-item" role="menuitem">
                <i class="fa-solid fa-box-open"></i> Iklan Saya
              </a>
              <a href="penjual.php?id=<?php echo (int) $currentUser['id']; ?>" class="dropdown-item" role="menuitem">
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
        <li aria-current="page">Pasang Iklan</li>
      </ol>
    </nav>
  </div>


  <!-- ================================================================
       KONTEN UTAMA: HALAMAN PASANG IKLAN
       ================================================================ -->
  <main class="container" id="main-content" role="main">
    <div class="post-ad-layout">

      <!-- ==================== KOLOM KIRI: FORM PASANG IKLAN ==================== -->
      <div class="post-ad-main">

        <!-- Banner Header Form -->
        <div class="post-ad-header">
          <h1>Pasang Iklan Anda</h1>
          <p>Isi rincian barang yang ingin Anda jual di bawah ini untuk menarik lebih banyak pembeli potensial.</p>
        </div>

        <!-- NOTIFIKASI ERROR JIKA ADA -->
        <?php if (! empty($errors)): ?>
          <div class="alert alert-danger" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert-content">
              <strong>Gagal Memasang Iklan:</strong>
              <ul style="margin: 6px 0 0 16px; list-style: disc;">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button type="button" class="alert-close" aria-label="Tutup notifikasi">&times;</button>
          </div>
        <?php endif; ?>

        <!-- FORM UTAMA DENGAN ENCTYPE MULTIPART -->
        <form class="post-ad-form" action="pasang-iklan.php" method="POST" enctype="multipart/form-data">

          <!-- ==================== CARD 1: PILIH KATEGORI (categories table dinamis via dropdown) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-cat">
            <h2 id="heading-cat" class="post-ad-card-title">
              <i class="fa-solid fa-tags" aria-hidden="true"></i> 1. Pilih Kategori Iklan
            </h2>
            <p class="form-hint" style="margin-bottom: 14px;">
              Pilih kategori yang paling sesuai dengan barang atau jasa yang Anda iklankan. Data diambil secara dinamis dari database.
            </p>

            <div class="form-group">
              <label for="category_id" class="form-label">Kategori Barang *</label>
              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-list"></i></span>
                <select name="category_id" id="category_id" class="form-control" required aria-required="true">
                  <option value="" disabled <?php echo empty($category_id) ? 'selected' : '' ?>>-- Pilih Kategori Barang --</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int) $cat['id'] ?>" <?php echo((string) $category_id === (string) $cat['id']) ? 'selected' : '' ?>>
                      <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <span class="form-hint">Klik dropdown di atas untuk memilih kategori yang sesuai dengan barang Anda.</span>
            </div>
          </section>


          <!-- ==================== CARD 2: UNGGAH FOTO (ad_images table) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-photos">
            <h2 id="heading-photos" class="post-ad-card-title">
              <i class="fa-solid fa-camera" aria-hidden="true"></i> 2. Unggah Foto Barang (Maks. 5 Foto)
            </h2>

            <!-- Dropzone Upload -->
            <label class="photo-upload-zone" for="ad-images-input">
              <span class="photo-upload-icon" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></span>
              <span class="photo-upload-text">Klik di sini untuk memilih foto atau seret foto ke sini</span>
              <span class="photo-upload-subtext">Format: JPG, JPEG, PNG, atau WebP (Maksimal 5MB per foto)</span>
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

            <!-- Slot Pratinjau Foto (Preview Grid) -->
            <div class="photo-slots-grid" aria-label="Slot Foto">
              <div class="photo-slot primary-slot" title="Foto Utama">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 1</span>
              </div>
              <div class="photo-slot" title="Foto Tambahan 2">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 2</span>
              </div>
              <div class="photo-slot" title="Foto Tambahan 3">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 3</span>
              </div>
              <div class="photo-slot" title="Foto Tambahan 4">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 4</span>
              </div>
              <div class="photo-slot" title="Foto Tambahan 5">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <span>Foto 5</span>
              </div>
            </div>
          </section>


          <!-- ==================== CARD 3: DETAIL IKLAN (ads.title, ads.description) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-info">
            <h2 id="heading-info" class="post-ad-card-title">
              <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> 3. Detail & Informasi Iklan
            </h2>

            <!-- Judul Iklan (ads.title - VARCHAR 50) -->
            <div class="form-group" style="margin-bottom: 20px;">
              <div class="char-counter-row">
                <label for="title" class="form-label">Judul Iklan *</label>
                <span id="title-counter" class="char-count"><?php echo mb_strlen($title) ?> / 50 karakter</span>
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
                value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
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
                aria-required="true"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
              <span class="form-hint">Iklan dengan deskripsi detail mendapatkan respons pembeli 3x lebih banyak.</span>
            </div>
          </section>


          <!-- ==================== CARD 4: TENTUKAN HARGA (ads.price) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-price">
            <h2 id="heading-price" class="post-ad-card-title">
              <i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i> 4. Tentukan Harga
            </h2>

            <!-- Input Harga dengan Prefix Rp (ads.price - DECIMAL(15,2)) -->
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
                  value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>"
                  aria-required="true">
              </div>
              <span class="form-hint">Tuliskan nominal angka saja tanpa titik atau koma (contoh: 185000000).</span>
            </div>

            <!-- Checkbox Nego -->
            <label class="checkbox-label" for="is_negotiable">
              <input type="checkbox" id="is_negotiable" name="is_negotiable" value="1" <?php echo $is_negotiable ? 'checked' : '' ?>>
              <span>Bisa Nego (Buka penawaran harga santai)</span>
            </label>
          </section>


          <!-- ==================== CARD 5: LOKASI PENJUAL (ads.location) ==================== -->
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
                  value="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>"
                  aria-required="true">
              </div>
              <span class="form-hint">Cantumkan nama kota dan kecamatan agar calon pembeli terdekat mudah menemukan iklan Anda.</span>
            </div>
          </section>


          <!-- ==================== CARD 6: KONFIRMASI PENJUAL (users table) ==================== -->
          <section class="post-ad-card" aria-labelledby="heading-seller">
            <h2 id="heading-seller" class="post-ad-card-title">
              <i class="fa-solid fa-user" aria-hidden="true"></i> 6. Profil Penjual
            </h2>

            <div style="display: flex; align-items: center; gap: 14px; background-color: var(--gray-50); padding: 14px 16px; border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
              <div class="seller-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;" aria-hidden="true">
                <?php echo strtoupper(substr($currentUser['name'], 0, 1)) ?>
              </div>
              <div>
                <p style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2px;"><?php echo htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;"><?php echo htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?> &bull; Akun Terverifikasi</p>
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
              Iklan ini akan dipasang dan dikelola menggunakan akun Anda yang sedang aktif.
            </span>
          </section>


          <!-- ==================== SUBMIT SECTION ==================== -->
          <div class="post-ad-card">
            <label class="checkbox-label" for="agree_rules" style="margin-bottom: 18px;">
              <input type="checkbox" id="agree_rules" name="agree_rules" value="1" required aria-required="true">
              <span>
                Saya menyatakan bahwa barang ini adalah milik sah saya dan menyetujui
                <a href="syarat-ketentuan.php" target="_blank" rel="noopener">Syarat & Ketentuan Pasang Iklan</a> di OLX Clone.
              </span>
            </label>

            <button type="submit" class="btn btn-solid-primary btn-block" style="font-size: 1.05rem; padding: 14px;">
              <i class="fa-solid fa-paper-plane"></i> Pasang Iklan Sekarang
            </button>
          </div>

        </form>

      </div>


      <!-- ==================== KOLOM KANAN: SIDEBAR TIPS & ATURAN ==================== -->
      <aside class="post-ad-sidebar" aria-label="Panduan Pasang Iklan">

        <!-- Card Tips Cepat Laku -->
        <div class="tips-card">
          <div class="tips-card-header">
            <i class="fa-solid fa-lightbulb" style="color: var(--accent); font-size: 1.2rem;"></i>
            <h3>Tips Iklan Cepat Laku</h3>
          </div>
          <ul class="tips-list">
            <li>
              <span class="tips-num">1</span>
              <div>
                <strong>Foto Asli & Jernih:</strong>
                <p>Gunakan foto barang asli dengan pencahayaan terang dari berbagai sudut pandang.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">2</span>
              <div>
                <strong>Judul Spesifik:</strong>
                <p>Sertakan merek, seri model, dan tahun pembuatan pada judul iklan.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">3</span>
              <div>
                <strong>Harga Kompetitif:</strong>
                <p>Cek harga pasaran barang serupa di OLX agar harga yang Anda pasang menarik pembeli.</p>
              </div>
            </li>
            <li>
              <span class="tips-num">4</span>
              <div>
                <strong>Deskripsi Transparan:</strong>
                <p>Jelaskan minus dan kelebihan barang secara jujur untuk meminimalisir komplain.</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Card Aturan Pasang Iklan -->
        <div class="rules-card">
          <h3><i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Aturan Pasang Iklan</h3>
          <ul>
            <li>Dilarang menjual barang ilegal, palsu/replika, senjata tajam, obat terlarang, atau hewan dilindungi.</li>
            <li>Dilarang membuat iklan ganda (<em>spam duplikasi</em>) untuk satu produk yang sama.</li>
            <li>Pastikan nomor WhatsApp dan chat aktif untuk merespons calon pembeli dengan cepat.</li>
            <li>OLX Clone berhak menghapus iklan yang melanggar kebijakan marketplace tanpa pemberitahuan.</li>
          </ul>
        </div>

      </aside>

    </div>
  </main>


  <!-- ================================================================
       FOOTER (Konsisten dengan seluruh halaman)
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
