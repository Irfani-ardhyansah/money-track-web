# Money Tracker

Aplikasi pencatatan keuangan pribadi berbasis web yang dibangun dengan Laravel. Kelola dompet, catat transaksi pemasukan & pengeluaran, lacak tabungan, dan pantau aktivitas keuanganmu dari satu dashboard.

---

## Fitur Utama

- **Dashboard** — Ringkasan saldo, grafik tren, dan rincian transaksi per kategori
- **Manajemen Dompet** — Dukung berbagai jenis dompet: tunai, bank, e-wallet, tabungan, dll.
- **Kategori** — Kategori pemasukan & pengeluaran yang bisa dikustomisasi
- **Transaksi** — Catat pemasukan, pengeluaran, dan transfer antar dompet
- **Tabungan** — Pantau saldo tabungan dengan riwayat penyesuaian
- **Log Aktivitas** — Rekam jejak setiap perubahan data secara otomatis

---

## Prasyarat

Pastikan sistem kamu sudah memiliki:

| Tools | Versi Minimum |
|-------|---------------|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |

---

## Instalasi

### 1. Clone Repositori

```bash
git clone <url-repositori> money-tracker
cd money-tracker
```

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Salin File Environment

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Konfigurasi Database

Secara default aplikasi ini menggunakan **SQLite**. Cukup buat file database-nya:

```bash
touch database/database.sqlite
```

> Jika ingin menggunakan MySQL/MariaDB, ubah konfigurasi `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env`.

### 6. Jalankan Migrasi & Seeder

```bash
php artisan migrate --seed
```

Perintah ini akan membuat seluruh tabel dan mengisi data dummy berikut:

| Data | Jumlah |
|------|--------|
| Dompet | 7 (Tunai, BCA, Mandiri, GoPay, OVO, Tabungan Liburan, Tabungan Darurat) |
| Kategori | 14 (5 pemasukan, 9 pengeluaran) |
| Transaksi | 35+ (mencakup Maret–Mei 2026) |
| Penyesuaian Tabungan | 6 entri |

### 7. Install Dependensi Frontend & Build Asset

```bash
npm install
npm run build
```

### 8. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser dan akses: **http://localhost:8000**

---

## Menjalankan Mode Development

Untuk development dengan hot-reload, jalankan semua proses sekaligus:

```bash
composer run dev
```

Perintah ini menjalankan secara bersamaan:
- `php artisan serve` — web server
- `npm run dev` — Vite dev server dengan hot-reload
- `php artisan queue:listen` — queue worker
- `php artisan pail` — log viewer

---

## Reset Data & Ulang Seeder

Jika ingin mereset seluruh data dan mengisi ulang dari awal:

```bash
php artisan migrate:fresh --seed
```

> **Perhatian:** Perintah ini akan menghapus **semua data** di database.

---

## Alur Penggunaan Aplikasi

### Dashboard

Halaman utama yang menampilkan:
- **Ringkasan saldo** semua dompet
- **Grafik tren** pemasukan & pengeluaran per bulan
- **Breakdown kategori** pengeluaran terbesar
- Filter rentang tanggal untuk analisis periode tertentu

---

### 1. Kelola Dompet

Akses melalui menu **Dompet** (`/wallets`).

**Tipe dompet yang didukung:**
- `cash` — Uang tunai
- `bank` — Rekening bank
- `e-wallet` — Dompet digital (GoPay, OVO, dll.)
- `savings` — Rekening tabungan khusus
- `general` — Umum

**Cara menambah dompet:**
1. Klik tombol **Tambah Dompet**
2. Isi nama, pilih tipe
3. Opsional: pilih dompet induk jika merupakan sub-dompet
4. Klik **Simpan**

---

### 2. Kelola Kategori

Akses melalui menu **Kategori** (`/categories`).

Kategori digunakan untuk mengelompokkan transaksi pemasukan dan pengeluaran.

**Cara menambah kategori:**
1. Klik tombol **Tambah Kategori**
2. Isi nama kategori
3. Pilih tipe: **Pemasukan** atau **Pengeluaran**
4. Klik **Simpan**

---

### 3. Catat Transaksi

Akses melalui menu **Transaksi** (`/transactions`).

**Tipe transaksi:**

| Tipe | Keterangan |
|------|------------|
| `income` | Pemasukan — uang masuk ke dompet |
| `expense` | Pengeluaran — uang keluar dari dompet |
| `transfer` | Transfer — pindah saldo antar dompet |

**Cara mencatat transaksi:**
1. Klik tombol **Tambah Transaksi**
2. Pilih tipe transaksi
3. Isi tanggal, jumlah, dan dompet sumber
4. Untuk transfer: pilih dompet tujuan
5. Untuk pemasukan/pengeluaran: pilih kategori
6. Tambahkan catatan (opsional)
7. Klik **Simpan**

**Filter & Pencarian:**
- Filter berdasarkan rentang tanggal
- Filter berdasarkan tipe transaksi
- Filter berdasarkan dompet atau kategori

---

### 4. Manajemen Tabungan

Akses melalui menu **Tabungan** (`/savings`).

Fitur ini khusus untuk dompet bertipe `savings`. Kamu bisa mencatat penyesuaian saldo tabungan — baik penambahan maupun pengurangan — beserta tanggal dan catatan alasannya.

**Cara mencatat penyesuaian tabungan:**
1. Klik tombol **Tambah Penyesuaian**
2. Pilih dompet tabungan
3. Isi jumlah (nilai negatif untuk pengurangan)
4. Isi tanggal dan catatan
5. Klik **Simpan**

---

### 5. Log Aktivitas

Akses melalui menu **Log Aktivitas** (`/activity-logs`).

Menampilkan riwayat seluruh perubahan data (tambah, ubah, hapus) secara otomatis untuk keperluan audit dan pelacakan.

---

## Struktur Proyek

```
app/
├── Http/Controllers/     # Controller untuk setiap fitur
├── Models/               # Eloquent models
├── Observers/            # Auto-log aktivitas
└── Services/             # Business logic (wallet balance, savings, dll.)

database/
├── migrations/           # Skema database
├── seeders/              # Data dummy untuk development
└── factories/            # Factory untuk testing

resources/views/          # Template Blade (UI)
routes/web.php            # Definisi route aplikasi
```

---

## Lisensi

Proyek ini bersifat pribadi dan tidak memiliki lisensi publik.
