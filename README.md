# 📦 Website Inventory Gudang
### PT Sigma Indonesia Manufacturing

[![CodeIgniter 4](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)](https://codeigniter.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laragon](https://img.shields.io/badge/Laragon-Local%20Server-00A4E4?style=for-the-badge&logo=laragon&logoColor=white)](https://laragon.org)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![VS Code](https://img.shields.io/badge/VS%20Code-Editor-007ACC?style=for-the-badge&logo=visual-studio-code&logoColor=white)](https://code.visualstudio.com)

*Sistem Informasi Manajemen Inventaris Gudang yang cepat, aman, dan modern untuk mendukung operasional logistik perusahaan.*

</div>

---

## 📋 Tentang Aplikasi
**Website Inventory Gudang PT Sigma Indonesia Manufacturing** adalah platform berbasis web yang dikembangkan untuk mengoptimalkan proses pencatatan stok barang, pengelolaan barang masuk & keluar, serta penyusunan laporan inventaris secara akurat dan *real-time*.

---

## ✨ Fitur Utama
* 📊 **Dashboard Interaktif**: Ringkasan data statistik stok barang dan aktivitas gudang secara *real-time*.
* 📦 **Manajemen Stok Barang**: Pencatatan data barang masuk, barang keluar, serta pengecekan sisa stok secara presisi.
* 📑 **Laporan & Rekapitulasi**: Fitur cetak laporan inventaris harian/bulanan untuk kebutuhan audit dan manajemen.
* 🔐 **Sistem Autentikasi Aman**: Hak akses pengguna berbasis *role* (Admin & Petugas Gudang).
* 🛡️ **Keamanan Arsitektur CI4**: Menerapkan pemisahan folder *public* untuk melindungi file inti sistem dari akses publik yang tidak sah.

---

## 🛠️ Tech Stack & Tools yang Digunakan
* **Framework Backend**: [CodeIgniter 4](https://codeigniter.com) (PHP Full-Stack Framework)
* **Local Development Server**: [Laragon](https://laragon.org) (Fast & lightweight development environment di Windows)
* **Database Management**: [MySQL](https://www.mysql.com) via **phpMyAdmin**
* **Code Editor**: [Visual Studio Code (VS Code)](https://code.visualstudio.com)
* **Dependency Manager**: [Composer](https://getcomposer.org)

---

## 🚀 Langkah-Langkah Instalasi (Installation Guide)

Ikuti tahapan di bawah ini dengan teliti untuk menjalankan project ini di komputer lokal Anda:

### 1. Persiapan Awal
Pastikan komputer Anda sudah terinstal:
* **Laragon** (sudah termasuk PHP versi 8.2+ dan MySQL).
* **Composer** (untuk mengelola *dependencies* PHP).
* **Visual Studio Code**.

### 2. Clone Repository
Buka terminal (Git Bash atau Command Prompt), lalu arahkan direktori ke dalam folder *root* web server Laragon Anda (biasanya di `C:\laragon\www\`):
```bash
git clone [https://github.com/Dahliasihombing/Website-Inventory-Gudang-PT-Sigma-Indonesia-Manufacturing.git](https://github.com/Dahliasihombing/Website-Inventory-Gudang-PT-Sigma-Indonesia-Manufacturing.git)
```

Setelah proses clone selesai, masuk ke dalam folder project:

```bash
cd Website-Inventory-Gudang-PT-Sigma-Indonesia-Manufacturing
```

### 3. Instal Vendor Dependencies via Composer
Jalankan perintah berikut di terminal untuk mengunduh seluruh pustaka (libraries) yang dibutuhkan oleh CodeIgniter 4:
```bash
composer install
```

### 4. Konfigurasi Environment (.env)
- Cari file bernama env di dalam folder utama project Anda.
- Salin (copy) file tersebut dan ubah namanya menjadi .env.
- Buka file .env menggunakan VS Code, lalu sesuaikan konfigurasi koneksi database Anda:
```bash
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = pt_sigma_inventory
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQL
```

### 5. Konfigurasi Database (phpMyAdmin)
- Buka aplikasi database manager Anda, jalankan Laragon lalu klik tombol Start All.
- Buka phpMyAdmin (bisa melalui shortcut Laragon atau akses http://localhost/phpmyadmin).
- Buat database baru dengan nama: pt_sigma_inventory (sesuaikan dengan nama database di file .env).
- Jika tersedia file struktur database (.sql), lakukan import file tersebut ke dalam database pt_sigma_inventory.

### 6. Menjalankan Aplikasi
Jalankan perintah ini di terminal VS Code:
```bash
php spark serve
```

## 📂 Struktur Direktori Project
```bash
Website-Inventory-Gudang-PT-Sigma-Manufacturing/
├── app/              # Berisi Controllers, Models, Views, dan logika aplikasi
├── public/           # Folder publik (Aset CSS/JS, dan file utama index.php)[cite: 5]
├── writable/         # Tempat penyimpanan logs, session, dan cache sistem
├── vendor/           # Pustaka sistem inti dari Composer
├── env               # File template konfigurasi environment
└── README.md         # Dokumentasi project
```

## 👥 Pengembang & Kontribusi
Project ini dikembangkan dengan penuh dedikasi oleh:
- Dahlia Sihombing — Lead Developer & Information Technology Student

