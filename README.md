# Jualin - Modern C2C Classifieds & Marketplace Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Frontend](https://img.shields.io/badge/Frontend-HTML5%20%7C%20CSS3%20%7C%20Vanilla%20JS-E34F26?style=flat-square)](assets/css/style.css)
[![Security](https://img.shields.io/badge/Security-PDO%20Prepared%20Statements-success?style=flat-square)](koneksi.php)
[![License: Educational Portfolio](https://img.shields.io/badge/License-Educational%20Portfolio-orange?style=flat-square)](#lisensi--hak-cipta)

**Jualin** adalah aplikasi platform marketplace jual-beli barang bekas dan baru lokal (Consumer-to-Consumer / C2C) yang dibangun dengan arsitektur **PHP Native** dan basis data relasional **MySQL**. Dirancang dengan fokus pada keamanan tingkat tinggi (*zero SQL-injection* via PDO Prepared Statements murni), performa kilat tanpa framework berat, sanitasi output anti-XSS, serta antarmuka responsif *mobile-first* bernuansa *Slate & Teal* yang elegan.

Aplikasi ini dipublikasikan secara terbuka sebagai proyek **portofolio edukasi dan studi kasus rekayasa perangkat lunak mandiri**.

---

## Daftar Isi
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Prasyarat Sistem](#prasyarat-sistem)
- [Panduan Instalasi Lokal](#panduan-instalasi-lokal)
- [Arsitektur Sistem & Alur Kerja](#arsitektur-sistem--alur-kerja)
  - [Struktur Direktori Lengkap](#struktur-direktori-lengkap)
  - [Request Lifecycle](#request-lifecycle)
  - [Alur Data (Data Flow)](#alur-data-data-flow)
  - [Komponen Inti](#komponen-inti)
  - [Skema Basis Data (Database Schema)](#skema-basis-data-database-schema)
- [Konfigurasi Sistem](#konfigurasi-sistem)
- [Daftar File & Routing Halaman](#daftar-file--routing-halaman)
- [Pengujian & Verifikasi (Testing)](#pengujian--verifikasi-testing)
- [Panduan Deployment ke Hostinger](#panduan-deployment-ke-hostinger)
- [Pemecahan Masalah (Troubleshooting)](#pemecahan-masalah-troubleshooting)
- [Aspek Keamanan](#aspek-keamanan)
- [Lisensi & Hak Cipta](#lisensi--hak-cipta)

---

## Fitur Utama

- **Pencarian Cerdas & Filter Multi-Parameter:** Filter instan berbasis kata kunci pencarian pada judul dan deskripsi iklan, pemilihan wilayah dinamis, serta pengurutan data (Terbaru, Termurah, Termahal).
- **Kategori Dinamis Terkoneksi Database:** Navigasi kategori bertingkat dengan integrasi ikon Font Awesome 6 dan fitur *expand/collapse* navigasi.
- **Katalog Berbasis Pagination:** Pembatasan presisi 20 iklan per halaman pada katalog utama dengan navigasi halaman bernomor untuk memastikan latensi muat halaman tetap rendah.
- **Etalase Toko Penjual (`penjual.php`):** Halaman profil publik penjual yang menampilkan statistik inventaris barang aktif, riwayat bergabung, dan tombol pintas WhatsApp instan.
- **Rekomendasi Terkait Relevan:** Penampilan otomatis iklan sejenis berdasarkan relasi `category_id` produk yang sedang ditinjau.
- **Manajemen Inventaris Mandiri (`iklan-saya.php`):** Dashboard penjual dengan kontrol *Show More* dinamis (kelipatan 10 iklan), fitur pengeditan lengkap, dan penghapusan berkas fisik otomatis.
- **Multi-Image Upload & Storage Management:** Unggah multi-foto produk dengan sanitasi nama berkas acak (SHA/MD5 hash) dan pembersihan *garbage file* saat iklan dihapus dari server.
- **Sistem Otentikasi Terenkripsi:** Registrasi dan login dengan hashing `password_hash()` (Bcrypt/Argon2id), sesi aman, serta persistensi *Remember Me* via cookie terenkripsi dengan atribut `HttpOnly`.
- **Desain Mobile-First & Floating Action Button:** Pengalaman belanja seluler optimal dengan grid 2-kolom seimbang dan tombol pasang iklan mengambang (*FAB*) untuk akses jempol satu tangan.
- **10 Halaman Legal & Edukasi Terstruktur:** Tentang Kami, Karir, Kontak, Blog, FAQ, Keamanan, Panduan, Pelaporan, Syarat Ketentuan, dan Kebijakan Privasi.

---

## Tech Stack

- **Bahasa Pemrograman:** PHP 8.0+ (Native Procedural & Object-Oriented Mix)
- **Database Engine:** MySQL 8.0 / MariaDB 10.4+ (Engine InnoDB, Charset `utf8mb4_0900_ai_ci`)
- **Database Abstraction:** PHP Data Objects (PDO) dengan emulasi `ATTR_EMULATE_PREPARES => false`
- **Frontend / View:** Semantic HTML5, Vanilla CSS3 modern (CSS Custom Properties / Variables, Flexbox, CSS Grid)
- **Tipografi:** Inter font family via CDN Google Fonts & Native Fallback Stack
- **Ikonografi:** Font Awesome 6 Free (CDN) murni tanpa ketergantungan emoji
- **Web Server:** Apache 2.4+ (dengan modul `mod_rewrite`) atau Nginx 1.20+
- **Platform Deployment:** Hostinger Cloud / Shared Hosting (hPanel & Git Deployment Engine)

---

## Prasyarat Sistem

Sebelum menjalankan proyek di lingkungan pengembangan lokal, pastikan perangkat telah memenuhi prasyarat berikut:

- **PHP:** Versi 8.0 atau lebih tinggi (ekstensi yang wajib aktif: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `openssl`).
- **MySQL / MariaDB:** Versi 8.0+ (MySQL) atau 10.4+ (MariaDB).
- **Lingkungan Server Lokal:** Laragon (Sangat direkomendasikan untuk Windows), XAMPP, MAMP, atau Docker.
- **Git:** Versi 2.30+ untuk manajemen version control.
- **Web Browser:** Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi modern.

---

## Panduan Instalasi Lokal

### 1. Kloning Repositori

Buka terminal atau command prompt, arahkan ke direktori root web server lokal Anda (`www` untuk Laragon, `htdocs` untuk XAMPP):

```bash
git clone https://github.com/4nndxrzky/jualin-marketplace.git
cd jualin-marketplace
```

### 2. Konfigurasi Basis Data MySQL

1. Jalankan server MySQL melalui panel kontrol Laragon atau XAMPP.
2. Buka antarmuka manajemen database (phpMyAdmin, HeidiSQL, DBeaver, atau MySQL CLI).
3. Buat database baru:
   ```sql
   CREATE DATABASE olx_clone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Impor file skema dan data awal yang disertakan:
   ```bash
   # Jalankan via terminal:
   mysql -u root -p olx_clone < olx_clone.sql
   ```
   *Atau impor berkas `olx_clone.sql` secara langsung melalui menu Import di phpMyAdmin.*

### 3. Penyesuaian Kredensial Koneksi

Buka berkas `koneksi.php` pada teks editor Anda, sesuaikan variabel berikut jika menggunakan kredensial non-default:

```php
// koneksi.php
$host     = 'localhost';
$port     = 3306;
$dbname   = 'olx_clone';
$username = 'root';
$password = ''; // Isi jika MySQL lokal Anda menggunakan password
$charset  = 'utf8mb4';
```

### 4. Konfigurasi Izin Direktori Berkas (Permissions)

Pastikan direktori penyimpanan berkas gambar iklan memiliki hak akses tulis:

```bash
# Untuk Linux / macOS:
chmod -R 775 uploads/ads/

# Untuk Windows (PowerShell):
icacls uploads/ads /grant "Everyone:(OI)(CI)F"
```

### 5. Menjalankan Server Pengembangan

Pilih salah satu metode berikut:

- **Metode A (Laragon / XAMPP):**
  Akses proyek melalui browser pada tautan:
  `http://localhost/jualin-marketplace` atau `http://jualin-marketplace.test` (jika fitur Pretty URLs Laragon aktif).

- **Metode B (PHP Built-in Server):**
  Jalankan perintah ini langsung dari folder proyek:
  ```bash
  php -S localhost:8000
  ```
  Buka browser dan kunjungi `http://localhost:8000`.

---

## Arsitektur Sistem & Alur Kerja

### Struktur Direktori Lengkap

```text
jualin-marketplace/
├── assets/
│   ├── css/
│   │   └── style.css            # Desain antarmuka, variabel Slate & Teal, typography, media queries
│   └── js/
│       └── main.js              # Handler dropdown, konfirmasi modal, preview upload foto
├── uploads/
│   └── ads/                     # Direktori penyimpanan fisik gambar iklan terunggah
│       ├── .gitkeep
│       └── [hashed-images].jpg  # Berkas foto iklan aktif
├── connection.php               # Jembatan alias kompatibilitas PDO ($pdo & $db)
├── detail.php                   # Halaman rincian iklan, profil penjual, dan iklan terkait
├── edit-iklan.php               # Antarmuka pembaruan informasi dan mutasi foto iklan
├── edit-profil.php              # Pengaturan profil, nomor WhatsApp, dan ganti password
├── faq.php                      # Halaman Frequently Asked Questions
├── hapus-iklan.php              # Controller penghapusan iklan + unlink fisik gambar
├── iklan-saya.php               # Dashboard inventaris iklan aktif user + Show More
├── index.php                    # Controller & View katalog utama, pagination, & filter
├── karir.php                    # Halaman informasi karir
├── kategori.php                 # Handler pencarian spesifik per kategori
├── keamanan.php                 # Tips & panduan transaksi aman anti-penipuan
├── kebijakan-privasi.php        # Dokumen legal privasi data pengguna
├── koneksi.php                  # Inisialisasi PDO, error handler, & konfigurasi brand
├── kontak.php                   # Layanan bantuan & formulir kontak
├── laporkan.php                 # Formulir pelaporan iklan bermasalah
├── login.php                    # Otentikasi masuk pengguna & Remember Me cookie
├── logout.php                   # Penghancuran sesi dan pembersihan cookie otentikasi
├── olx_clone.sql                # Skema basis data DDL dan data seed awal
├── panduan.php                  # Panduan transaksi dan tips jual cepat
├── pasang-iklan.php             # Form publikasi iklan baru & multi-upload file handler
├── penjual.php                  # Halaman etalase profil penjual publik
├── register.php                 # Pendaftaran akun baru & enkripsi password
├── search.php                   # Mesin pencari katalog & query redirection
├── sitemap.xml                  # Peta situs terindeks untuk mesin pencari (SEO)
├── syarat-ketentuan.php         # Syarat & ketentuan layanan hukum
├── tentang.php                  # Profil platform, visi, misi, dan arsitektur
├── .gitignore                   # Pencegahan pelacakan file sampah lokal & cache OS
└── README.md                    # Dokumentasi teknis komprehensif repositori
```

### Request Lifecycle

```text
Browser Client Request (HTTP GET/POST)
                  │
                  ▼
          Web Server (Apache/Nginx)
                  │
                  ▼
        Routing File Target (misal: index.php, detail.php)
                  │
                  ├─► session_start() & Read Cookies ('olx_remember_email')
                  │
                  ├─► require_once 'koneksi.php'
                  │        │
                  │        ▼
                  │   Inisialisasi PDO (dsn, credentials, options)
                  │
                  ├─► Validasi & Sanitasi Parameter Input ($_GET / $_POST)
                  │
                  ├─► Prepared Statements SQL Execution
                  │        │
                  │        ▼
                  │   MySQL Engine (Execution with bound parameters)
                  │
                  ├─► Data Fetching (PDO::FETCH_ASSOC)
                  │
                  └─► Output Sanitization (htmlspecialchars) -> Render HTML/CSS/JS Response
```

### Alur Data (Data Flow)

#### 1. Alur Pasang Iklan Baru (`pasang-iklan.php`)
```text
Pengguna (Input Form + File Foto) 
    ──► Validasi Sesi Login
    ──► Validasi Format & MIME Type Gambar (jpg, jpeg, png, webp)
    ──► Sanitasi & Generate Nama Berkas Acak (ad_{hash}_{timestamp}.ext)
    ──► Simpan File Fisik ke /uploads/ads/
    ──► Mulai Transaksi Database (PDO Transaction)
    ──► INSERT INTO ads (user_id, category_id, title, description, price, location)
    ──► Dapatkan Last Insert ID (ad_id)
    ──► INSERT INTO ad_images (ad_id, image_path)
    ──► Commit Transaksi
    ──► Set Flash Message Sukses -> Redirect ke detail.php?id={ad_id}
```

#### 2. Alur Hapus Iklan (`hapus-iklan.php`)
```text
Pengguna (Trigger Tombol Hapus) 
    ──► Validasi Kepemilikan (Pastikan user_id pada iklan = $_SESSION['user_id'])
    ──► Query Seluruh image_path dari ad_images untuk ad_id terkait
    ──► Iterasi File: Jalankan unlink() pada direktori /uploads/ads/
    ──► DELETE FROM ad_images WHERE ad_id = :id
    ──► DELETE FROM ads WHERE id = :id
    ──► Redirect ke iklan-saya.php dengan notifikasi berhasil
```

### Komponen Inti

1. **Security & Connection Hub (`koneksi.php`):**
   Pusat kendali koneksi PDO. Menerapkan `PDO::ATTR_EMULATE_PREPARES => false` guna menjamin kueri diproses murni secara native oleh MySQL server, memutus potensi SQL Injection. Mengisolasi konstanta identitas platform: `APP_NAME`, `APP_TAGLINE`, dan `APP_COMPANY`.
2. **State & Flash Messages:**
   Manajemen umpan balik aksi pengguna memanfaatkan `$_SESSION['flash_success']` dan `$_SESSION['flash_error']` yang langsung di-*unset* setelah dirender, mencegah pesan berulang saat halaman disegarkan.
3. **Responsive UI Engine (`style.css`):**
   Menggunakan arsitektur CSS modern dengan variabel palet warna netral *Slate 900* (`#0f172a`), warna aksen *Teal 600* (`#0d9488`), sistem elevasi bayangan *soft-diffused*, serta layout adaptif pada breakpoint `992px`, `768px`, `576px`, dan `480px`.

### Skema Basis Data (Database Schema)

```text
  +------------------+         +-------------------------+
  |      users       |         |       categories        |
  +------------------+         +-------------------------+
  | PK  id           |         | PK  id                  |
  |     name         |         |     name                |
  |     email        |         |     icon                |
  |     whatsapp     |         +-------------------------+
  |     password     |                      ▲
  |     created_at   |                      │
  +------------------+                      │
           ▲                                │
           │ 1                              │ 1
           │                                │
           │ N                              │ N
  +------------------------------------------------------+
  |                         ads                          |
  +------------------------------------------------------+
  | PK  id                                               |
  | FK  user_id ─────────────────────────────────────────┘
  | FK  category_id ─────────────────────────────────────┘
  |     title                                            |
  |     description                                      |
  |     price                                            |
  |     location                                         |
  |     created_at                                       |
  +------------------------------------------------------+
           ▲
           │ 1
           │
           │ N
  +------------------+
  |    ad_images     |
  +------------------+
  | PK  id           |
  | FK  ad_id        |
  |     image_path   |
  +------------------+
```

#### Struktur Kolom & Tipe Data

| Tabel | Kolom | Tipe Data | Keterangan |
|---|---|---|---|
| **`users`** | `id` | INT (PK, Auto Increment) | Pengenal unik pengguna |
| | `name` | VARCHAR(100) NOT NULL | Nama lengkap pengguna |
| | `email` | VARCHAR(100) NOT NULL, UNIQUE | Alamat email untuk otentikasi login |
| | `whatsapp` | VARCHAR(20) DEFAULT NULL | Nomor telepon / WhatsApp penjual |
| | `password` | VARCHAR(255) NOT NULL | Hash kata sandi (`PASSWORD_DEFAULT`) |
| | `created_at` | DATETIME DEFAULT CURRENT_TIMESTAMP | Waktu pendaftaran akun |
| **`categories`**| `id` | INT (PK, Auto Increment) | Pengenal unik kategori |
| | `name` | VARCHAR(50) NOT NULL | Label kategori (Mobil, Gadget, dll) |
| | `icon` | VARCHAR(100) DEFAULT NULL | Kelas icon Font Awesome |
| **`ads`** | `id` | INT (PK, Auto Increment) | Pengenal unik iklan produk |
| | `user_id` | INT NOT NULL (FK -> users.id) | ID pengguna pemilik iklan |
| | `category_id` | INT NOT NULL (FK -> categories.id) | ID kategori produk |
| | `title` | VARCHAR(50) NOT NULL | Judul ringkas iklan |
| | `description` | TEXT | Rincian spesifikasi barang |
| | `price` | DECIMAL(15, 2) NOT NULL | Nominal harga jual produk |
| | `location` | VARCHAR(100) DEFAULT NULL | Nama kota / wilayah penjualan |
| | `created_at` | DATETIME DEFAULT CURRENT_TIMESTAMP | Waktu posting iklan |
| **`ad_images`** | `id` | INT (PK, Auto Increment) | Pengenal unik foto |
| | `ad_id` | INT NOT NULL (FK -> ads.id) | Relasi iklan induk |
| | `image_path` | VARCHAR(255) NOT NULL | Jalur berkas (`uploads/ads/...`) |

---

## Konfigurasi Sistem

Seluruh konfigurasi utama terpusat pada berkas `koneksi.php`:

| Parameter | Tipe | Nilai Default | Keterangan |
|---|---|---|---|
| `$host` | String | `'localhost'` | Host server database MySQL |
| `$port` | Integer | `3306` | Port koneksi MySQL |
| `$dbname` | String | `'olx_clone'` | Nama basis data aktif |
| `$username` | String | `'root'` | User basis data MySQL |
| `$password` | String | `''` | Kata sandi basis data MySQL |
| `$charset` | String | `'utf8mb4'` | Karakter encoding standar multibyte |
| `APP_NAME` | Konstanta | `'Jualin'` | Nama aplikasi pada seluruh UI |
| `APP_TAGLINE` | Konstanta | `'Marketplace Terpercaya'` | Slogan platform |
| `APP_COMPANY` | Konstanta | `'Jualin Indonesia'` | Entitas identitas copyright |

---

## Daftar File & Routing Halaman

| File Sumber | Metode | Fungsi & Aksesibilitas |
|---|---|---|
| `index.php` | GET | Halaman utama katalog, pagination 20 iklan, filter lokasi & kata kunci. Publik. |
| `detail.php` | GET | Detail rincian barang, kontak WhatsApp penjual, dan iklan rekomendasi serupa. Publik. |
| `penjual.php` | GET | Profil etalase toko penjual beserta seluruh katalog yang diunggahnya. Publik. |
| `pasang-iklan.php` | GET, POST | Formulir pasang iklan dan multi-upload foto. Membutuhkan Login. |
| `iklan-saya.php` | GET | Dashboard iklan aktif pengguna, kontrol Show More (limit 10). Membutuhkan Login. |
| `edit-iklan.php` | GET, POST | Formulir update informasi dan manipulasi foto iklan. Membutuhkan Login (Pemilik Iklan). |
| `hapus-iklan.php` | POST | Controller penghapusan iklan dan pembersihan file fisik. Membutuhkan Login (Pemilik Iklan). |
| `login.php` | GET, POST | Autentikasi akun pengguna dan pembuatan cookie Remember Me. Publik (Tamu). |
| `register.php` | GET, POST | Pendaftaran akun pengguna baru dan enkripsi kata sandi. Publik (Tamu). |
| `logout.php` | GET | Pemusnahan sesi aktif dan pembatalan cookie Remember Me. Membutuhkan Login. |
| `edit-profil.php` | GET, POST | Pembaruan profil, nomor WhatsApp, dan ganti kata sandi. Membutuhkan Login. |
| `tentang.php` | GET | Informasi latar belakang, arsitektur, dan visi platform Jualin. Publik. |
| `karir.php` | GET | Informasi peluang bergabung dan budaya kerja tim. Publik. |
| `kontak.php` | GET, POST | Layanan saluran komunikasi pengguna dan formulir bantuan. Publik. |
| `blog.php` | GET | Tips jual beli cerdas, tren gadget/otomotif, dan artikel edukasi. Publik. |
| `faq.php` | GET | Rangkuman jawaban atas pertanyaan umum seputar jual beli. Publik. |
| `keamanan.php` | GET | Panduan keamanan bertransaksi dan tips menghindari penipuan. Publik. |
| `panduan.php` | GET | Tata cara memasang iklan efektif dan cara belanja aman (COD). Publik. |
| `laporkan.php` | GET, POST | Saluran pelaporan iklan terindikasi melanggar hukum/penipuan. Publik. |
| `syarat-ketentuan.php`| GET | Perjanjian hukum dan ketentuan hak serta kewajiban pengguna. Publik. |
| `kebijakan-privasi.php`| GET | Kebijakan pengelolaan dan perlindungan privasi data pribadi. Publik. |
| `sitemap.xml` | XML | Dokumen XML terstruktur sitemap untuk mesin perayap (Googlebot). Publik. |

---

## Pengujian & Verifikasi (Testing)

Lakukan pengujian fungsionalitas sebelum melakukan *deployment*:

### 1. Uji Registrasi & Keamanan Kata Sandi
- Buka `register.php`, daftarkan akun baru dengan nomor WhatsApp valid (`08xxxxxxxxxx`).
- Cek tabel `users` di database: pastikan password tersimpan dalam format hash acak (contoh diawali `$2y$10$...`), bukan teks asli (*plaintext*).

### 2. Uji Login & Persistensi Cookie
- Masuk melalui `login.php` dengan mencentang opsi **Ingat Saya**.
- Tutup peramban, buka kembali, dan pastikan email pengguna terisi otomatis pada form login.

### 3. Uji Unggah Berkas & Batasan Gambar
- Pasang iklan baru melalui `pasang-iklan.php`.
- Unggah file gambar berformat `.jpg`, `.png`, atau `.webp`.
- Pastikan berkas tersimpan rapi pada direktori `uploads/ads/` dengan nama yang di-*hash* acak.
- Coba unggah berkas bukan gambar (misal `.pdf` atau `.exe`): sistem harus menolak dan memunculkan notifikasi kesalahan.

### 4. Uji Pagination & Filter
- Pastikan katalog `index.php` membatasi tampilan tepat 20 iklan per halaman.
- Lakukan pengujian filter kategori, filter kota lokasi, dan pengurutan harga termurah/termahal untuk memvalidasi query builder di `index.php`.

### 5. Uji Penghapusan Iklan & Pembersihan Berkas
- Masuk ke `iklan-saya.php`, pilih salah satu iklan untuk dihapus.
- Pastikan data terhapus dari tabel `ads` dan `ad_images`.
- Periksa folder fisik `uploads/ads/`: pastikan file gambar produk terkait sudah terhapus secara fisik dari server (*zero garbage files*).

---

## Panduan Deployment ke Hostinger

Aplikasi ini telah siap 100% untuk dideploy ke shared hosting maupun cloud hosting Hostinger menggunakan integrasi Git:

### Langkah 1: Buat Database MySQL di Hostinger
1. Masuk ke panel kontrol **hPanel Hostinger**.
2. Buka menu **Databases** -> **MySQL Databases**.
3. Buat database baru:
   - Nama Database: (misal: `u123456_jualin`)
   - Username: (misal: `u123456_admin`)
   - Password: Buat password yang kuat dan catat hasilnya.
4. Buka **phpMyAdmin** untuk database tersebut melalui tombol di hPanel.
5. Klik tab **Import**, pilih file `olx_clone.sql` dari komputer Anda, lalu klik **Go** / **Import**.

### Langkah 2: Deploy Repositori via Git di hPanel
1. Pada hPanel Hostinger, buka menu **Advanced** -> **Git**.
2. Buat deployment baru:
   - **Repository URL:** Masukkan tautan repositori GitHub Anda:
     `https://github.com/4nndxrzky/jualin-marketplace.git`
   - **Branch:** Pilih `main`
   - **Install in Directory:** Kosongkan atau isi `public_html` (jika ini website utama pada domain).
3. Klik tombol **Deploy**. Hostinger akan mengkloning seluruh source code proyek langsung ke `public_html`.

### Langkah 3: Konfigurasi Kredensial Database Produksi
1. Buka menu **Files** -> **File Manager** di hPanel Hostinger.
2. Masuk ke direktori `public_html/`.
3. Buka dan edit file `koneksi.php`.
4. Sesuaikan konfigurasi database dengan data database Hostinger yang telah dibuat pada Langkah 1:
   ```php
   $host     = 'localhost'; // Tetap 'localhost' untuk Hostinger
   $port     = 3306;
   $dbname   = 'u123456_jualin';
   $username = 'u123456_admin';
   $password = 'PasswordDatabaseKuatAnda';
   ```
5. Simpan perubahan (*Save & Close*).

### Langkah 4: Verifikasi Hak Akses Direktori Uploads
1. Di File Manager, navigasikan ke folder `public_html/uploads/ads/`.
2. Klik kanan pada folder `ads`, pilih **Permissions**.
3. Pastikan izin akses disetel ke `755` (atau centang Read, Write, Execute untuk Owner). Ini krusial agar pengguna website dapat mengunggah gambar produk secara lancar.

---

## Pemecahan Masalah (Troubleshooting)

### 1. Masalah: "Koneksi Database Gagal! SQLSTATE[HY000] [2002] Connection refused"
- **Penyebab:** Layanan MySQL belum berjalan, port tidak sesuai, atau hostname keliru.
- **Solusi:**
  - Di lokal: Pastikan service MySQL di Laragon/XAMPP sudah dalam status *Running*.
  - Di Hostinger: Pastikan nilai `$host` pada `koneksi.php` adalah `'localhost'` (bukan IP eksternal) dan pastikan nama database beserta user sudah sesuai persis dengan yang ada di hPanel.

### 2. Masalah: "Gambar Iklan Tidak Muncul Setelah Diunggah di Server Hosting"
- **Penyebab:** Izin direktori `uploads/ads/` tidak mengizinkan penulisan berkas (*write access*) atau batasan ukuran file `php.ini`.
- **Solusi:**
  - Ubah izin folder `uploads/ads/` menjadi `755` melalui File Manager.
  - Periksa konfigurasi PHP di hPanel (**PHP Configurations**): pastikan `upload_max_filesize` minimal `8M` dan `post_max_size` minimal `16M`.

### 3. Masalah: "Session Login Tiba-tiba Hilang / Sering Logout Sendiri"
- **Penyebab:** Konfigurasi session path hosting penuh atau pengaturan cookie path bermasalah.
- **Solusi:**
  - Pastikan disk space hosting belum mencapai kuota 100%.
  - Cek apakah browser memblokir cookie pihak ketiga jika mengakses via domain tanpa HTTPS/SSL. Aktifkan fitur *Free SSL* di Hostinger.

### 4. Masalah: "Fatal Error: Call to undefined function imagecreatefromjpeg()"
- **Penyebab:** Ekstensi PHP GD library belum aktif di server.
- **Solusi:**
  - Di lokal (Laragon/XAMPP): Buka file `php.ini`, hapus tanda titik koma `;` pada baris `extension=gd`, lalu restart server.
  - Di Hostinger: Masuk menu **PHP Extensions**, pastikan ekstensi `gd` dan `fileinfo` dalam status tercentang (*Enabled*).

---

## Aspek Keamanan

Proyek Jualin dibangun dengan mematuhi standar dasar keamanan web OWASP:

- **100% Prepared Statements (Anti SQL Injection):** Seluruh manipulasi kueri yang melibatkan masukan pengguna dieksekusi menggunakan `$pdo->prepare()` dan parameter binding binding eksplisit. Fitur emulasi dimatikan (`PDO::ATTR_EMULATE_PREPARES => false`) agar kueri tidak dapat disusupi injeksi kode SQL.
- **Sanitasi Kontekstual Anti-XSS (Cross-Site Scripting):** Seluruh data dinamis yang ditampilkan ke antarmuka browser dienkapsulasi menggunakan fungsi `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` guna mencegah eksekusi skrip berbahaya.
- **Proteksi Kata Sandi Kriptografis:** Tidak ada penyimpanan kata sandi dalam bentuk teks terbuka (*plaintext*). Seluruh kata sandi dienkripsi dengan algoritma adaptif kuat satu arah `PASSWORD_DEFAULT`.
- **Pengamanan Cookie Otentikasi:** Cookie `olx_remember_email` dilengkapi bendera keamanan `HttpOnly` untuk menangkal pencurian identitas sesi melalui skrip JavaScript di sisi klien.

---

## Lisensi & Hak Cipta

- **Tujuan Proyek:** Repositori ini dipublikasikan secara terbuka murni untuk keperluan **studi kasus, pembelajaran, dan portofolio edukasi pribadi**.
- **Ketentuan Penggunaan:** Seluruh kode sumber disediakan sebagaimana adanya (*as-is*) sebagai referensi akademis dan contoh implementasi teknis PHP Native & MySQL. Penggunaan atau pengembangan kembali untuk keperluan pembelajaran dipersilakan dengan tetap mencantumkan atribusi kepada pembuat asli.
- **Kredit Pembelajaran:** Proyek ini berakar dari studi kasus praktikum pada **Kelas Fullstack Web Development - Codepolitan**, dengan berbagai rekayasa ulang mandiri (peningkatan keamanan PDO, peremajaan antarmuka modern *Slate & Teal*, optimalisasi *mobile-first*, sistem pagination presisi, dan fitur etalase toko mandiri).

