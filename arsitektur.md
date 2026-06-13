# Blueprint Arsitektur Sistem: Anti-Gravity Comic Hub (Scalable Multi-Source Aggregator)
**Arsitek:** KDV  
**Versi:** 1.0.0  
**Target Ekosistem:** Laravel 11 + Inertia.js + React + Redis + PostgreSQL/MySQL (VPS Hosted)

---

## 1. Visi Skalabilitas: Multi-Source Aggregator

Untuk membangun sebuah platform pusat komik yang super lengkap, sistem tidak boleh dikunci hanya untuk satu website sumber (seperti Shinigami Scans). Kita akan menerapkan **Multi-Source Aggregator Architecture** menggunakan **Adapter Pattern**.

### Alur Kerja Universal Data
1. Sistem memiliki satu kontrak standar (`ComicDriverInterface`).
2. Setiap website target baru (Shinigami, Asura, Kiryuu, MangaDex, dll.) akan dibuatkan satu Class khusus bernama **Driver/Adapter** (contoh: `ShinigamiDriver`, `AsuraDriver`).
3. Driver bertugas mengubah struktur API/HTML unik milik target menjadi format data universal yang dimengerti oleh internal Laravel kita.
4. Database kita hanya menyimpan satu struktur data universal tersebut. Dengan demikian, *frontend* (React) tidak pernah peduli dari mana komik tersebut berasal.

---

## 2. Struktur Layer Aplikasi (Clean Architecture Blueprint)

Aplikasi akan dibagi menjadi 4 layer utama yang terisolasi dengan baik:

```
[ Presentation Layer: React + Inertia ]
                 │
                 ▼
[ Application Layer: Services & Caching ]
                 │
                 ▼
[ Domain Layer: Eloquent Models & Business Logic ]
                 │
                 ▼
[ Infrastructure Layer: Drivers / Scrapers / Adapters ]
```

### A. Presentation Layer (Inertia + React)
* Hanya menerima data bersih dari Controller.
* Bertanggung jawab penuh terhadap UI/UX, *lazy loading* gambar, dan retensi posisi membaca (*reading history*) menggunakan `LocalStorage`.

### B. Application Layer (Laravel Services)
* **ComicAggregatorService:** Mengkoordinasikan pencarian komik di lintas *database* lokal dan panggilan ke pihak ketiga jika data belum ada di lokal.
* **ImageProxyService:** Mengalirkan (*streaming*) gambar dari CDN luar ke pembaca jika terjadi kendala CORS/Hotlink Protection, sekaligus menyembunyikan jejak *referrer*.

### C. Domain Layer (Database & Models)
Menyimpan entitas bisnis inti aplikasi. Skema tabel dirancang agar mendukung multi-sumber dengan menambahkan kolom `source_code`.

### D. Infrastructure Layer (Drivers)
Berisi implementasi konkret dari `ComicDriverInterface`. Jika besok website target mengubah skema API-nya, **Bos hanya perlu memperbaiki file Driver terkait di layer ini.** Sisa sistem lainnya (Database, Service, UI React) akan tetap berjalan 100% tanpa perubahan.

---

## 3. Rancangan Skema Database (Universal Schema)

### Tabel `mangas`
Menampung metadata utama komik dari seluruh sumber.
* `id` (UUID, Primary Key)
* `title` (String)
* `slug` (String, Unique)
* `synopsis` (Text, Nullable)
* `cover_url` (String)
* `status` (Enum: 'ongoing', 'completed')
* `type` (Enum: 'manga', 'manhwa', 'manhua')
* `source_code` (String) -> Penanda asal, contoh: 'shinigami', 'asura', 'mangadex'
* `source_manga_id` (String) -> ID asli di website target (contoh UUID `c0f1d049...` milik Shinigami)
* `created_at` & `updated_at`

### Tabel `chapters`
Menampung daftar chapter. Relasi One-to-Many dengan `mangas`.
* `id` (UUID, Primary Key)
* `manga_id` (UUID, Foreign Key, Cascade on Delete)
* `chapter_number` (Decimal/Float) -> Mendukung chapter seperti 10.5
* `chapter_title` (String, Nullable)
* `source_chapter_id` (String) -> ID asli chapter di website target
* `pages_data` (JSONB / JSON) -> Menyimpan array path file gambar (e.g., `["01.jpg", "02.jpg"]`)
* `base_url_override` (String, Nullable) -> Jika CDN target berubah sewaktu-waktu
* `path_override` (String, Nullable)
* `created_at` & `updated_at`

### Tabel `genres` & `manga_genre`
Untuk sistem filter yang agresif.
* `id` (BigInteger, Primary Key)
* `name` (String)
* `slug` (String, Unique)

---

## 4. Strategi Performa Tinggi & Pertahanan Server

Karena dijalankan di VPS 4GB RAM milik Bos, kita bisa mengimplementasikan performa ekstrem berikut:

1. **Aggressive Redis Caching:**
   Setiap kali Driver berhasil menarik data chapter dari sumber luar, data tersebut langsung disimpan ke **Redis RAM** dengan masa kedaluwarsa 24 jam. Request berikutnya dari user lain untuk chapter yang sama akan langsung dilayani dari RAM dalam waktu <2ms. Server target tidak akan mendeteksi *spamming* dari server kita.
   
2. **On-Demand Background Scraping:**
   Sistem tidak perlu melakukan kloning seluruh isi website target sekaligus (yang akan menghabiskan memori dan disk). Kloning dilakukan secara *On-Demand*. Ketika ada user yang mencari atau membuka komik tertentu yang belum ada di database kita, Laravel akan memicu Driver secara *real-time* di *background*, menyimpannya, lalu menyuapkannya ke user.

3. **Garbage Collection Task Scheduling:**
   Mengingat kapasitas disk VPS kita adalah 30GB, sebuah *Cron Job* di Laravel akan berjalan setiap minggu untuk menghapus isi kolom `pages_data` pada chapter-chapter lama yang tidak pernah dibuka oleh satu user pun dalam 30 hari terakhir. Ini menjamin penggunaan disk VPS kita akan selalu stabil di bawah 50%.