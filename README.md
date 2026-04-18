# MediHub

**Jembatan Menuju Layanan Kesehatan Terbaik**

MediHub adalah platform aplikasi layanan kesehatan modern (HealthTech) yang dirancang untuk menghubungkan pasien dengan dokter, rumah sakit, dan fasilitas kesehatan terpercaya. Didesain dengan antarmuka yang sangat premium, estetis, dan responsif.

## 🚀 Fitur Utama

- **Premium Landing Page:** Halaman depan yang dirancang interaktif dengan *smooth scrolling*, efek *glassmorphism*, dan animasi transisi modern.
- **Katalog Layanan Spesialis:** Akses cepat ke layanan kesehatan terpopuler seperti Kandungan, Gigi & Mulut, Pemeriksaan Umum, dan Spesialis Anak.
- **Direktori Dokter Unggulan:** Menampilkan profil dokter dengan penilaian (rating), ulasan, spesialisasi, serta riwayat pasien.
- **Sistem Autentikasi (UI):** Tampilan halaman Login dan Register yang aman, minimalis, dengan validasi yang *user-friendly*.
- **Dashboard Pasien (UI):** Tata letak *sidebar* dan navigasi yang bersih untuk memudahkan pengguna mengelola janji temu.

## 🛠 Teknologi yang Digunakan

Proyek ini dibangun di atas fondasi teknologi terbaru untuk performa dan skalabilitas maksimal:

- **Framework Backend:** [Laravel 12](https://laravel.com/) (PHP 8.2+)
- **Frontend & Styling:** [Tailwind CSS v4](https://tailwindcss.com/) & Vanilla CSS (Vite terintegrasi)
- **Aset & Ikon:** Format SVG teroptimasi, ilustrasi kustom.
- **Arsitektur Tampilan:** Blade Templating dengan struktur layout modular.

## ⚙️ Cara Instalasi & Menjalankan Secara Lokal

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di mesin lokal Anda:

1. **Clone repositori ini:**
   ```bash
   git clone <repository-url>
   cd MediHub
   ```

2. **Install dependensi PHP (Composer):**
   ```bash
   composer install
   ```

3. **Install dependensi Node.js (NPM):**
   ```bash
   npm install
   ```

4. **Siapkan konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Jalankan Aplikasi:**
   Anda dapat menggunakan *Vite* dan *PHP Artisan* secara bersamaan:
   ```bash
   composer run dev
   ```
   Atau jalankan secara terpisah:
   ```bash
   # Terminal 1
   php artisan serve

   # Terminal 2
   npm run dev
   ```

6. **Akses Aplikasi:**
   Buka browser Anda dan kunjungi `http://localhost:8000`

## 🎨 Konvensi Desain (Design System)

Proyek ini menggunakan variabel CSS kustom untuk manajemen warna dan UI:
- `--bg`: `#f5f6f8`
- `--panel`: `#ffffff`
- `--text`: `#1f2024`
- `--primary`: `#6aa4ef`
- `--danger`: `#e65b54`
- `--success`: `#66be74`

Banyak komponen dibangun dengan kaidah *BEM/semantic* seperti `.doctor-card`, `.mediq-app-shell`, dll di file `resources/css/app.css` guna memastikan sistem dapat dikelola dengan mudah walaupun menggunakan utility-class dari Tailwind CSS.

## 📂 Struktur Direktori UI Utama

- `resources/views/welcome.blade.php`: Halaman depan / Landing Page.
- `resources/views/layouts/landing.blade.php`: Layout induk untuk halam depan.
- `resources/css/app.css`: *Entry-point* CSS, berisi impor Tailwind dan kumpulan gaya UI komponen MediHub.
- `public/images/`: Direktori logo dan ilustrasi raster (PNG/SVG).

---

© 2026 MediHub. Hak Cipta Dilindungi Undang-Undang.
