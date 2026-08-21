# Dokumentasi & Rekapitulasi Proyek SLBB Kejaksaan Negeri Banda Aceh

## 1. Overview Aplikasi & Tech Stack

**Sistem Rekapitulasi Barang Bukti (SLBB)** Kejaksaan Negeri Banda Aceh adalah aplikasi manajemen dan rekapitulasi data benda sitaan serta barang bukti perkara pidana umum (Pidum) dan pidana khusus (Pidsus). Sistem ini dirancang untuk mempermudah pencatatan registrasi sitaan, pemantauan status perkara, kalkulasi kuantitas fisik (seperti barang bukti narkotika), serta otomatisasi pembuatan laporan bulanan resmi (Form 3A, 3B, dan 3C) yang dapat diekspor ke format **DOCX** dan **PDF**.

### Techstack

* **Backend Framework**: Laravel 13 (PHP 8.4)

* **Frontend Integration**: Inertia.js v3 (Monolithic SPA architecture)

* **Frontend Library**: Vue 3, TypeScript, Tailwind CSS, Chart.js (Donut Chart), Lucide Vue Icons

* **Database**: SQLite

* **Document Engine**: PhpOffice/PhpWord v1.4 & Symfony Process (CLI LibreOffice Headless untuk konversi PDF)

* **Infrastruktur & Server**: Docker, Docker Compose, Nginx (Alpine)

* **Automation & Tools**: Python Telegram Bot (`python-telegram-bot` v20.0) untuk pemantauan server & registrasi akun

---

## 2. Arsitektur & Struktur Folder

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

---

## 3. Panduan Instalasi Lokal (Development)

### Persyaratan Sistem

* PHP >= 8.4 dengan ekstensi

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
php artisan migrate:fresh --seed

```


6. **Jalankan Aplikasi**:
```bash
composer run dev

```


Akses di browser melalui `http://localhost:8000`.



---

## 4. Panduan Deployment ke Server (Production & Docker)

### Persyaratan Sistem

* PHP >= 8.4

* Composer v2

* Node.js >= 20.x & NPM

* Docker

### Langkah Intalasi

1. Lakukan clone pada repository ini
```bash
git clone https://github.com/muhammadhizqilalfi/project-bb.git
cd project-bb
```

2. **Lakukan instalasi global Composer, panduan instalasi bisa dilihat pada `https://laravel.com/framework/docs/13.x/installation`

NOTE: Sesuaikan dengan Sistem Operasi yang digunakan

3. **Copy atau konfigurasi file `.env`

4. **Jalankan Container Docker**:
```bash
docker-compose up -d --build

```

5. **Inisialisasi Database & Optimasi Laravel**:
```bash
docker exec app php artisan key:generate
docker exec app php artisan migrate:fresh --seed
docker exec app php artisan config:cache
docker exec app php artisan route:cache
docker exec app php artisan view:cache
docker exec app php artisan storage:link

```


NOTE: Jika setelah langkah ini masih gagal, maka jalankan command dibawah ini:

```bash
composer install --ignore-platform-req=ext-gd
npm install # Install nodejs terlebih dahulu
npm run build

```



