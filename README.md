<div align="center">

# 🏛️ Absensi & Monev Non-ASN
### Kantor Wilayah Kementerian Hukum dan HAM Kepulauan Bangka Belitung

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20|%208.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-5.x-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

<p align="center">
  Sistem presensi digital berbasis <b>Geofencing GPS</b> dan <b>Biometrik Wajah</b> terintegrasi modul <b>Monitoring & Evaluasi (Monev)</b> aktivitas harian serta perizinan pegawai Non-ASN.
</p>

</div>

---

## 🌟 Fitur Utama

* **📍 Geofencing & Face Capture Presisi**  
  Perekaman kehadiran masuk dan pulang wajib menyertakan foto wajah langsung (*live camera* / *capture perangkat*) yang divalidasi dengan radius GPS kantor menggunakan kalkulasi formula Haversine. Dilengkapi *testing mode* (`radius = 0`) untuk fleksibilitas pengujian lokal.

* **📋 Monev Harian Terintegrasi**  
  Syarat wajib sebelum absen pulang: pegawai mengisi uraian aktivitas, analisis kendala lapangan (minimal 100 karakter), dan unggah bukti foto kegiatan (maksimal 3 foto).

* **📝 Pengajuan Cuti & Izin Digital**  
  Pengajuan 9 kategori izin/cuti (sakit, cuti tahunan, dinas luar, dll.) dilengkapi upload berkas pendukung dan alur persetujuan (*approval / rejection*) oleh atasan secara real-time.

* **📊 Dashboard Monitoring & Pelaporan**  
  Pemantauan status kehadiran harian (Tepat Waktu, Terlambat, Izin, Alpha) secara instan, lengkap dengan rekapitulasi presensi bulanan dan fitur cetak laporan siap pakai (*print / PDF export*).

* **🗂️ Manajemen Tugas & Proyek**  
  Papan alur tugas (*Kanban task workflow*), pencatatan estimasi durasi kerja (*time tracking*), dan pemantauan progres penyelesaian proyek.

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | [Laravel 11](https://laravel.com), PHP 8.2 / 8.3 |
| **Frontend** | [Tailwind CSS](https://tailwindcss.com), Blade Engine, [SweetAlert2](https://sweetalert2.github.io) |
| **Tooling** | [Vite 5](https://vitejs.dev), Composer, NPM |
| **Database** | MySQL 8.0+ / MariaDB |
| **APIs** | HTML5 Geolocation API, WebRTC MediaStream API |

---

## 🚀 Quick Start (Panduan Cepat)

### Prasyarat
* PHP `>= 8.2` (ekstensi: `pdo_mysql`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `zip`, `openssl`)
* Composer `>= 2.x`
* Node.js `>= 18.x` & NPM
* MySQL / MariaDB (misal: Laragon / XAMPP)

### Instalasi

```bash
# 1. Clone repository
git clone https://github.com/kenyongid/Absensi-Monev_KemenkumBabel.git
cd Absensi-Monev_KemenkumBabel

# 2. Setup environment
cp .env.example .env

# 3. Install dependencies PHP & Node
composer install
npm install && npm run build

# 4. Generate application key
php artisan key:generate

# 5. Database migration & initial data seeding
php artisan migrate:fresh --seed
php artisan storage:link

# 6. Jalankan server lokal
php artisan serve
```

> **Pengguna Windows (Laragon):** Anda juga dapat langsung menjalankan server cukup dengan klik dua kali file **`run.bat`**.

Buka browser di **`http://127.0.0.1:8000`**.

---

## 🔑 Akun Login Bawaan (Hasil Seeder)

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `admin` | Dashboard, Rekapitulasi, Approval Cuti, Pengaturan Kantor, Manajemen SDM |
| **Satpam (Non-ASN)** | `joko.satpam@nonasn.id` | `password123` | Presensi Masuk & Pulang, Monev Harian, Pengajuan Izin/Cuti |
| **Driver (Non-ASN)** | `agus.driver@nonasn.id` | `password123` | Presensi Masuk & Pulang, Monev Harian, Pengajuan Izin/Cuti |
| **Kebersihan (Non-ASN)**| `siti.kebersihan@nonasn.id` | `password123` | Presensi Masuk & Pulang, Monev Harian, Pengajuan Izin/Cuti |

---

## ⚙️ Pengaturan Geofencing & Lokasi Kantor

1. Login sebagai **Admin** (`admin@example.com`).
2. Masuk ke menu **Pengaturan Lokasi Kantor** (`/attendance/settings`).
3. Anda dapat:
   * Menyesuaikan koordinat GPS kantor dengan menekan tombol **"Ambil Titik Koordinat GPS Saat Ini"**.
   * Menentukan radius toleransi (meter).
   * Menyetel `radius = 0` jika ingin mengaktifkan **Mode Bebas / Testing** tanpa batasan jarak geofence.

---

## 📂 Struktur Direktori Utama

```text
├── app/
│   ├── Http/Controllers/     # Controller logika absensi, monev, cuti, proyek & task
│   └── Models/                  # Model Eloquent database (Attendance, OfficeSetting, dll.)
├── database/
│   ├── migrations/              # Skema tabel database
│   └── seeders/                 # Data dummy & inisialisasi sistem
├── resources/
│   ├── views/                   # Template tampilan Blade (attendance, administration, dashboard)
│   ├── css/ & js/               # Kode sumber Tailwind & JavaScript
├── routes/
│   └── web.php                  # Rute aplikasi
└── run.bat                      # Runner instan untuk Windows / Laragon
```

---

## 💡 Troubleshooting Ringkas

<details>
<summary><b>1. Mengapa Absen Masuk tertolak karena radius kantor?</b></summary>
Pastikan radius kantor diatur ke <code>0</code> (Testing Mode) di <code>/attendance/settings</code> atau perbarui titik kantor sesuai lokasi perangkat Anda saat ini.
</details>

<details>
<summary><b>2. Izin kamera atau lokasi tidak muncul di browser?</b></summary>
Pastikan Anda mengakses via <code>http://localhost:8000</code> atau <code>http://127.0.0.1:8000</code>. Browser mengizinkan akses kamera dan lokasi hanya pada domain aman (HTTPS atau localhost). Klik ikon gembok pada address bar untuk memastikan izin <b>Camera</b> dan <b>Location</b> berstatus <i>Allow</i>.
</details>

<details>
<summary><b>3. Foto presensi atau profil tidak tampil?</b></summary>
Jalankan perintah symlink penyimpanan publik:
<code>php artisan storage:link</code>
</details>

<details>
<summary><b>4. Error <code>Please provide a valid cache path</code>?</b></summary>
Pastikan direktori cache framework Laravel telah tersedia:
<code>mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions bootstrap/cache</code>
</details>

---

## 📄 Lisensi

Proyek ini berada di bawah lisensi [MIT](LICENSE). Dikembangkan untuk keperluan operasional dan monitoring evaluasi pegawai di lingkungan Kementerian Hukum dan Hak Asasi Manusia Republik Indonesia.
