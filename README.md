# Dokumentasi & Rekapitulasi Proyek SLBB Kejaksaan Negeri Banda Aceh

## 1. Overview Aplikasi & Tech Stack

**Sistem Rekapitulasi Barang Bukti (SLBB)** Kejaksaan Negeri Banda Aceh adalah aplikasi manajemen dan rekapitulasi data benda sitaan serta barang bukti perkara pidana umum (Pidum) dan pidana khusus (Pidsus). Sistem ini dirancang untuk mempermudah pencatatan registrasi sitaan, pemantauan status perkara, kalkulasi kuantitas fisik (seperti barang bukti narkotika), serta otomatisasi pembuatan laporan bulanan resmi (Form 3A, 3B, dan 3C) yang dapat diekspor ke format **DOCX** dan **PDF**.

### Daftar Teknologi Terdeteksi

* **Backend Framework**: Laravel 13 (PHP 8.4)


* **Frontend Integration**: Inertia.js v3 (Monolithic SPA architecture)


* **Frontend Library**: Vue 3 (Composition API dengan `<script setup lang="ts">`), TypeScript, Tailwind CSS v4, Chart.js (Donut Chart), Lucide Vue Icons


* **Database**: SQLite


* **Document Engine**: PhpOffice/PhpWord v1.4 & Symfony Process (CLI LibreOffice Headless untuk konversi PDF)


* **Infrastruktur & Server**: Docker, Docker Compose, Nginx (Alpine), Autoheal Container


* **Automation & Tools**: Python Telegram Bot (`python-telegram-bot` v20.0) untuk pemantauan server & registrasi akun



---

## 2. Arsitektur & Struktur Folder

Proyek ini mengadopsi pola arsitektur **Laravel-Inertia SPA**, di mana backend Laravel mengelola logika bisnis, routing, dan database, sementara frontend Vue 3 dirender secara interaktif tanpa memerlukan API REST terpisah.

```text
├── .github/workflows/       # CI/CD pipeline (Pest & PHPStan checks)
├── app/
│   ├── Http/
│   │   ├── Controllers/    # AuthenticatedSessionController, DashboardController, 
│   │   │                   # FormTemplateController, LaporanController, SettingController
│   │   └── Middleware/     # HandleInertiaRequests (Shared props: auth, flash)
│   ├── Models/             # User, FormTemplate, DropdownOption, Setting
│   ├── Providers/          # AppServiceProvider (CarbonImmutable, Security defaults)
│   └── Services/           # LaporanDocxService (PhpWord engine)
├── bootstrap/               # Application bootstrapper & Inertia middleware binding
├── config/                  # Konfigurasi app, auth, database, inertia, logging, session
├── database/
│   ├── factories/          # UserFactory
│   ├── migrations/         # Skema tabel users, form_templates, dropdown_options, settings
│   └── seeders/            # DatabaseSeeder, DropdownOptionSeeder, Form3BJuli2026Seeder
├── docker/                  # Custom php.ini, opcache.ini, Nginx app.conf
├── resources/
│   ├── css/                # app.css (Tailwind CSS v4 import & theme config)
│   ├── js/
│   │   ├── Components/     # Sidebar.vue, Topbar.vue
│   │   ├── Layouts/        # Layout.vue (Authenticated Wrapper)
│   │   ├── pages/
│   │   │   ├── Auth/       # Login.vue
│   │   │   ├── Dashboard/  # Dashboard.vue
│   │   │   └── Tabs/       # Form3A.vue, Form3AInput.vue, Form3B.vue, Form3C.vue, 
│   │   │                   # Form3CInput.vue, Laporan.vue, PengaturanForm.vue
│   │   └── types/          # Auth, Global TypeScript definitions
│   └── views/              # app.blade.php (Root HTML Entry)
├── routes/                  # web.php (Inertia routes) & console.php
├── telegram_bot/            # bot.py & Dockerfile bot kendali server
├── Dockerfile               # Multi-stage Docker build (Node.js -> PHP 8.4 Alpine + LibreOffice)
└── docker-compose.yaml      # Orchestration slbb-app, webserver, autoheal, slbb-bot

```

### Hubungan Alur Data

1. **Request HTTP** diterima oleh Nginx dan diteruskan ke PHP-FPM (`app:9000`).


2. **Laravel Route** mencocokkan URI dan memanggil Controller terkait.


3. **Middleware** (`HandleInertiaRequests`) menyuntikkan data sesi global (pengguna terautentikasi dan pesan *flash*).


4. **Controller** melakukan query melalui Eloquent Model (`FormTemplate`, `DropdownOption`, dll.).


5. **Inertia Response** mengirimkan props data JSON ke komponen Vue di folder `resources/js/pages/`.


6. **Frontend Render**: Vue melakukan rendering halaman secara dinamis tanpa *full page reload*.



---

## 3. Inventarisasi Seluruh Modul & Fitur

### Modul Autentikasi & Keamanan User

* **Fungsi**: Mengelola akses masuk pegawai menggunakan NIP dan Password.


* **Workflow**: `AuthenticatedSessionController` memvalidasi input NIP, menerapkan *Rate Limiting* (maksimal 5 kali percobaan gagal per 5 menit), memverifikasi password, dan meregenerasi sesi pengguna.



### Modul Dashboard Utama (`Dashboard.vue`)

* **Fungsi**: Pusat visualisasi statistik dan rekapitulasi data.


* **Workflow**: Menyediakan filter dinamis per bulan/tahun; menampilkan statistik perkara masuk (Form 3A) dan perkara selesai/inkracht (Form 3C); kalkulasi otomatis total kuantitas barang bukti narkotika (Sabu, Ganja, Ekstasi); grafik lingkaran (Doughnut Chart) kategori pidana; serta matriks rekapitulasi perkara dan unit barang bukti.



### Modul Input & Pencatatan Form 3A (`Form3AInput.vue`)

* **Fungsi**: Pencatatan benda sitaan dan barang bukti perkara tahap penyidikan/persidangan.


* **Workflow**: Wizard 2 tahap untuk menentukan nama form dan periode laporan, dilanjutkan pengisian registrasi sitaan, penyidikan, identitas tersangka, pasal, dan repeater barang bukti (jenis, jumlah, uraian, lokasi penyimpanan).



### Modul Input & Pencatatan Form 3C (`Form3CInput.vue`)

* **Fungsi**: Pencatatan barang bukti perkara yang telah berkekuatan hukum tetap (Inkracht).


* **Workflow**: Wizard 2 tahap untuk menginput nomor/tanggal putusan pengadilan (PN/PT/MA), tanggal eksekusi, serta daftar barang bukti beserta spesifikasi kadar dan amar putusan (dirampas, dimusnahkan, dikembalikan, lelang).



### Modul Rekapitulasi Form 3B (`Form3B.vue`)

* **Fungsi**: Laporan rekapitulasi penanganan barang bukti bulanan.


* **Workflow**: Secara otomatis menghitung sisa bulan lalu, jumlah perkara masuk (akumulasi Form 3A), perkara selesai (Form 3C), dan sisa akhir bulan laporan.



### Modul Manajemen Laporan & Ekspor (`Laporan.vue`)

* **Fungsi**: Pratinjau tabel laporan resmi dan ekspor berkas.


* **Workflow**: Pengguna memilih jenis form, periode, dan kategori tindak pidana. Ekspor **DOCX** diproses oleh `LaporanDocxService` menggunakan PhpWord. Ekspor **PDF** mengonversi berkas DOCX sementara secara *headless* menggunakan CLI LibreOffice via `Symfony\Component\Process`.



### Modul Pengaturan System & Master Data (`PengaturanForm.vue`)

* **Fungsi**: Pengelolaan opsi dropdown dinamis dan data pejabat penandatangan.


* **Workflow**: Admin melakukan operasi CRUD pada opsi kategori pidana, jenis narkotika, satuan, dan tempat penyimpanan, serta memperbarui profil penandatangan laporan (Kepala Seksi).



### Modul Telegram Server Control (`telegram_bot/bot.py`)

* **Fungsi**: Pemantauan dan manajemen infrastruktur server via Telegram.


* **Workflow**: Memverifikasi Chat ID admin. Perintah `/newacc` mengeksekusi Artisan Tinker via Docker CLI untuk pendaftaran akun baru; `/listuser` menampilkan daftar akun; `/start` menampilkan menu tombol restart container dan cek status Docker.



---

## 4. Struktur Database & Relasi

| Nama Tabel | Eloquent Model | Fungsi & Peran Data |
| --- | --- | --- |
| `users` | `User` | Menyimpan identitas akun pengguna (nama, NIP unik, password).
| `form_templates` | `FormTemplate` | Menyimpan entitas form laporan (3A, 3B, 3C), periode bulan/tahun, dan data perkara (`cases`) dalam format JSON.
| `dropdown_options` | `DropdownOption` | Menyimpan master data pilihan dropdown dinamis (`category`, `label`, `form_target`).
| `settings` | `Setting` | Menyimpan konfigurasi sistem berbasis *key-value* (seperti profil `pejabat_kasi`).

---

## 5. Daftar Rute & Endpoint

### Autentikasi

* `GET /` : Menampilkan halaman Login (`Auth/Login.vue`)


* `POST /login` : Memproses autentikasi NIP & password


* `POST /logout` : Mengakhiri sesi pengguna



### Dashboard

* `GET /dashboard` : Menampilkan statistik & matriks rekapitulasi (`Dashboard/Dashboard.vue`)



### Form 3A

* `GET /form3a` : Daftar form 3A


* `GET /form3a/create` : Wizard buat Form 3A baru


* `POST /forms/3a/wizard` : Menyimpan Form 3A & case pertama


* `GET /form3a/{id}/cases/create` : Tambah case pada Form 3A existing


* `POST /form3a/{id}/cases` : Menyimpan case baru pada Form 3A


* `GET /form3a/{id}/edit` : Edit data perkara Form 3A


* `PUT /form3a/{id}` : Memperbarui data perkara Form 3A


* `DELETE /form3a/{id}` : Menghapus data perkara / form 3A



### Form 3B & 3C

* `GET /form3b` : Rekapitulasi Form 3B


* `GET /form3c` : Daftar form 3C


* `GET /form3c/create` : Wizard buat Form 3C baru


* `POST /forms/3c/wizard` : Menyimpan Form 3C & case pertama


* `GET /form3c/{id}/edit` : Edit data perkara Form 3C


* `PUT /form3c/{id}` : Memperbarui data perkara Form 3C


* `DELETE /form3c/{id}` : Menghapus data perkara / form 3C



### Laporan & Ekspor

* `GET /laporan` : Halaman pratinjau laporan


* `GET /laporan/export-docx` : Unduh dokumen laporan `.docx`

* `GET /laporan/export-pdf` : Unduh dokumen laporan `.pdf`


### Pengaturan Master Data

* `GET /settings` : Halaman pengaturan master data


* `POST /settings` : Menambah opsi dropdown


* `PUT /settings/{id}` : Memperbarui opsi dropdown


* `DELETE /settings/{id}` : Menghapus opsi dropdown


* `POST /settings/officer` : Menyimpan data pejabat penandatangan



---

## 6. Panduan Instalasi Lokal (Development)

### Persyaratan Sistem

* PHP >= 8.4 dengan ekstensi `pdo_sqlite`, `mbstring`, `zip`, `bcmath`

* Composer v2


* Node.js >= 20.x & NPM



### Langkah Instalasi

1. **Clone Repositori & Masuk Direktori**:
```bash
git clone <repository-url>
cd <repository-directory>

```


2. **Pengaturan Environment**:
```bash
cp .env.example .env

```


3. **Instalasi Dependensi**:
```bash
composer install
npm install

```


4. **Generate Key**:
```bash
php artisan key:generate

```


5. **Inisialisasi Database SQLite**:
```bash
touch database/database.sqlite
php artisan migrate --seed

```


6. **Jalankan Aplikasi**:
```bash
npm run dev
# Pada terminal terpisah:
php artisan serve

```


Akses di browser melalui `http://localhost:8000`.



---

## 7. Panduan Deployment ke Server (Production & Docker)

### Persyaratan Server Production

* OS: Linux (Ubuntu 22.04 LTS / 24.04 LTS disarankan)
* Docker Engine (v24+) & Docker Compose (v2.x+)
* Paket Non-GUI Headless: LibreOffice (`libreoffice-writer`), `font-dejavu`, `ttf-liberation`, dan `fontconfig`.



### Konfigurasi Dockerfile

```dockerfile
FROM php:8.4-fpm-alpine

# Install LibreOffice Writer Headless & Font Pendukung
RUN apk add --no-cache \
    libreoffice-writer \
    font-dejavu \
    ttf-liberation \
    fontconfig \
    sqlite \
    libzip \
    oniguruma \
    && apk add --no-cache --virtual .build-deps \
    libzip-dev \
    sqlite-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo_sqlite mbstring zip bcmath opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

```

### Konfigurasi `.env` Production

```ini
APP_NAME="SLBB Kejari Banda Aceh"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.50.92:8800

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/database/database.sqlite

TELEGRAM_BOT_TOKEN="your-bot-token"
TELEGRAM_CHAT_ID="your-allowed-chat-id"

```

### Langkah Deployment

1. **Jalankan Container Docker**:
```bash
docker-compose up -d --build

```


2. **Inisialisasi Database & Optimasi Laravel**:
```bash
docker exec -it slbb-app php artisan migrate --force --seed
docker exec -it slbb-app php artisan config:cache
docker exec -it slbb-app php artisan route:cache
docker exec -it slbb-app php artisan view:cache
docker exec -it slbb-app php artisan storage:link

```


3. **Izin Akses Directory**:
```bash
docker exec -it slbb-app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database
docker exec -it slbb-app chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database

```


4. **Alur Reverse Proxy Nginx**:
```nginx
location ~ \.php$ {
    try_files $uri =404;
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_pass app:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}

```