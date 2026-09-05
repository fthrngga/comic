# Dokumentasi Proyek: Anti-Gravity Comic Hub

## 1. Tujuan Proyek
Anti-Gravity Comic Hub adalah sebuah platform **Scalable Multi-Source Aggregator** untuk membaca komik (Manga, Manhwa, Manhua). Tujuannya adalah menjadi satu pusat baca komik terpadu yang dapat mengambil data dari berbagai website atau API sumber berbeda (seperti Shinigami, Asura, dll) tanpa harus terkunci atau bergantung pada satu sumber tunggal. 

## 2. Tech Stack (Teknologi yang Digunakan)
- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** React.js 18 dengan Inertia.js 2.0 (SSR ready & memberikan pengalaman SPA/Single Page Application tanpa kerumitan API routing).
- **Styling:** Tailwind CSS 3
- **Build Tool:** Vite
- **Database:** MySQL (Terkonfigurasi di `.env`) / PostgreSQL.
- **Caching & Queue:** Redis (Sangat direkomendasikan untuk performa tinggi, meskipun saat ini konfigurasi fallback lokal di set ke `file`).

## 3. Mekanisme di Balik Sistem (Arsitektur)
Proyek ini mengadopsi rancangan **Clean Architecture** yang terbagi dalam beberapa *layer*, serta **Adapter Pattern** untuk fleksibilitas pengambilan data (Scraping):

1. **Presentation Layer (React + Inertia):** Bertanggung jawab pada antarmuka (UI/UX). Menerima data terstruktur dari backend dan menampilkannya, mengatur lazy loading gambar, dan berpotensi menyimpan posisi baca user via LocalStorage.
2. **Application Layer (Services):** Mengatur alur logika bisnis. Bertugas mengecek ketersediaan komik di database internal, dan memicu penarikan data ke sumber luar jika belum ada (On-Demand).
3. **Domain Layer (Eloquent Models):** Mengatur entitas data (`Manga`, `Chapter`, `Genre`). Struktur database dibuat universal (menyimpan `source_code` dan `source_manga_id`) agar bentuk data dari sumber mana pun dapat dinormalisasi ke satu bentuk.
4. **Infrastructure Layer (Drivers/Scrapers):** Disinilah *Adapter* berada. Contohnya Command `ScrapeShinigamiCommand` yang bertugas memanggil API Shinigami (`api.shngm.io`), mengambil datanya, lalu menyesuaikan strukturnya agar bisa masuk ke database internal. **Jika API target berubah, developer hanya perlu mengubah file *Driver* ini.**

### Fitur Kunci & Pertahanan Server:
- **On-Demand Background Scraping:** Sistem tidak mengkloning seluruh isi website target di awal. Pencarian atau pembacaan oleh *user* akan memicu pengunduhan detail chapter di *background*.
- **Aggressive Caching (via Redis):** Data yang sudah ditarik dari luar akan di-cache agar request selanjutnya dari *user* lain dapat dijawab dalam hitungan milidetik tanpa melakukan pemanggilan (*spamming*) ke server target.

## 4. Cara Menjalankan Proyek (Local Setup)
Berikut adalah langkah-langkah untuk menjalankan proyek di komputer lokal:

1. **Clone & Masuk ke Direktori Proyek:**
   ```bash
   cd comic
   ```

2. **Install Dependencies (Backend & Frontend):**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment:**
   Jika `.env` belum ada, copy dari `.env.example`.
   Sesuaikan bagian `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` dengan kredensial MySQL Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database:**
   ```bash
   php artisan migrate
   ```

5. **Menjalankan Server:**
   Karena menggunakan Laravel + Vite, disarankan menjalankan perintah kombo berikut yang sudah didefinisikan di `composer.json`:
   ```bash
   composer run dev
   ```
   *(Atau secara terpisah buka 2 terminal: jalankan `php artisan serve` di terminal 1 dan `npm run dev` di terminal 2).*

6. **(Opsional) Test Fetching / Scraping Data:**
   Untuk mengambil data awal dari Shinigami sebagai uji coba:
   ```bash
   php artisan comic:scrape-shinigami --limit=10 --page=1
   ```

## 5. Pengembangan Lanjutan (Future Development)
Rencana eskalasi dan pengembangan sistem selanjutnya berdasarkan arsitektur yang sudah dicanangkan:

- **Pembuatan Driver/Adapter Tambahan:** Memperluas jangkauan sumber dengan menambahkan driver untuk Asura Scans, Kiryuu, MangaDex, dll.
- **Implementasi ImageProxyService:** Membuat proxy di sisi server kita agar gambar dari sumber asli di-*stream* melalui server kita. Ini krusial untuk mencegah pemblokiran dari mekanisme *CORS* atau *Hotlink Protection* yang diterapkan oleh website sumber.
- **Integrasi Redis Sepenuhnya:** Mengubah konfigurasi `CACHE_STORE` lokal dari `file` ke `redis` untuk memaksimalkan performa *Aggressive Caching*.
- **Task Scheduling / Garbage Collection:** Mengimplementasikan *Cron Job* bawaan Laravel (via `Schedule`) untuk rutin menghapus data `pages_data` pada chapter lama yang sudah sebulan tidak dibaca. Hal ini akan mencegah VPS kehabisan disk space (storage efficiency).
