# Manprosi

Proyek ini adalah sistem pelacakan aset rumah sakit skala tugas/latihan, dibangun dengan Laravel 13. Backend menyediakan API untuk manajemen aset, pembuatan label QR, pencatatan riwayat pemindahan, dan pelaporan kerusakan.

## Panduan Penyiapan

Ikuti langkah-langkah berikut untuk menyiapkan lingkungan pengembangan lokal:

1. Clone repositori dan masuk ke folder proyek:

```bash
git clone https://github.com/farrelisboring/manprosi
cd manprosi
```

2. Install dependensi PHP:

```bash
composer install
```

3. Generate application key:

```bash
php artisan key:generate
```

4. Masukan credentials db ke .env (atur variabel DB_* sesuai lingkungan Anda).
5. Jalankan NPM

```bash
npm run build
```

5. Jalankan migrasi dan seeder:

```bash
php artisan migrate
php artisan db:seed
```

## Catatan

- Project ini menggunakan PHP ^8.3 dan Laravel 13.
- Untuk menjalankan server lokal, gunakan `php artisan serve` setelah langkah di atas.