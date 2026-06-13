# Design System: Obsidian Noir
**Arsitek Visual:** KDV  
**Konsep Utama:** Distraction-Free, Eye Comfort, Mobile-First Comic Reader  
**Implementasi:** Tailwind CSS v3/v4

---

## 1. Color Tokens (Palet Warna)

Membaca komik membutuhkan waktu lama. Palet warna ini dirancang khusus untuk mengurangi kelelahan mata (*eye strain*) secara maksimal, menghindari warna hitam pekat (`#000000`) dan putih terang (`#FFFFFF`) yang kontrasnya terlalu menusuk mata.

| Token | Hex Code | Penggunaan |
| :--- | :--- | :--- |
| `bg-base-dark` | `#0F172A` | Background utama aplikasi (Deep Slate) |
| `bg-base-card` | `#1E293B` | Background untuk card, navbar, sidebar (Slate 800) |
| `border-muted` | `#334155` | Border halus untuk pemisah konten |
| `brand-primary`| `#8B5CF6` | Aksen tombol utama, status membaca, hover effect (Vivid Violet) |
| `brand-success`| `#10B981` | Label chapter baru, status ongoing (Emerald) |
| `text-primary` | `#F1F5F9` | Teks judul, konten utama, keterbacaan tinggi |
| `text-muted`   | `#94A3B8` | Teks sinopsis, tanggal update, informasi sekunder |

---

## 2. Typography (Tipografi)

Sistem menggunakan font sans-serif modern yang bersih untuk memastikan antarmuka terasa seperti aplikasi native.

* **Font Family Utama:** `Inter`, `Geist Sans`, atau system-ui sans-serif.
* **Ukuran & Berat Teks:**
  * Juul Manga (Detail Page): `text-2xl md:text-4xl font-bold text-slate-100 tracking-tight`
  * Nomor Chapter (List): `text-sm font-semibold text-slate-200`
  * UI Navigasi Reader: `text-xs md:text-sm font-medium tracking-wide`
  * Sinopsis: `text-sm leading-relaxed text-slate-400`

---

## 3. Spesifikasi Komponen Inti

### A. The Reader Viewport (Wadah Baca Komik)
Komponen paling krusial dalam sistem. Harus bersih dari elemen UI kosmetik saat membaca berjalan.
* **Lebar Maksimal:** Dibatasi maksimal `max-w-3xl` (sekitar 768px) atau `max-w-2xl` di desktop agar mata pembaca tidak perlu bergerak terlalu jauh dari kiri ke kanan. Di mobile, wajib *full-bleed* (`w-full` tanpa padding kiri-kanan).
* **Image Container Rule:** Setiap tag `<img>` wajib dibungkus di dalam container dengan warna dasar `#1E293B` dan memiliki efek transisi halus saat gambar selesai dimuat untuk menghindari fenomena *Layout Shift*.
* **Lazy Loading:** Atribut `loading="lazy"` wajib disematkan pada semua halaman komik di bawah halaman ke-3.

### B. Mobile Navigation Overlay (Bottom Bar)
Karena 80% user menggunakan smartphone, kontrol navigasi diletakkan di bawah (mudah dijangkau jempol).
* **Posisi:** `fixed bottom-0 left-0 right-0 z-50`
* **Desain:** Background `#1E293B` dengan tingkat opasitas 95% (`bg-opacity-95`) ditambah efek *backdrop-blur*.
* **Kontrol:** Tombol "Prev Chapter", "Dropdown List Chapter", dan "Next Chapter" dengan ukuran minimal klik area `48px x 48px` sesuai standar aksesibilitas internasional.

### C. Skeleton Loader Template
Untuk memberikan impresi performa yang instan saat data ditarik asinkron.
* Menggunakan animasi `animate-pulse` bawaan Tailwind.
* Warna dasar skeleton wajib menggunakan kelas `bg-slate-800` dengan radius sudut `rounded-lg` atau `rounded-md` yang konsisten di seluruh aplikasi.