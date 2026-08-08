# PRD — Dashboard Admin Toko Kaldera

| | |
|---|---|
| **Produk** | Dashboard Admin Toko Kaldera |
| **Versi** | 1.0 |
| **Tanggal** | 2026-08-09 |
| **Status** | Fase 1 — Dashboard & Dummy Data |
| **Stack** | Laravel 12, Blade, Tailwind CSS v4, Alpine.js, Chart.js, SQLite |

---

## 1. Ringkasan Eksekutif

Dashboard admin single-page untuk memantau performa penjualan **Toko Kaldera** (toko perlengkapan outdoor & camping). Menampilkan KPI, grafik penjualan, dan tabel pesanan/produk secara interaktif, didukung data dummy realistis (12 bulan) agar siap dievaluasi & dikembangkan.

## 2. Asumsi

- Toko Kaldera adalah toko **perlengkapan outdoor/camping** (dapat disesuaikan).
- Fase 1 **tanpa autentikasi**; login ditambahkan pada fase berikutnya.
- Data dummy dihasilkan dari **seeder** (Faker `id_ID`) dan di-reload kapan pun.

## 3. Ruang Lingkup

### 3.1 Termasuk (Fase 1)
- Halaman dashboard tunggal (`/`).
- KPI cards: Pendapatan, Pesanan, Pelanggan Baru, Stok Menipis.
- Grafik: penjualan per bulan, distribusi kategori, metode pembayaran, top produk.
- Tabel: pesanan terbaru (search + filter status), produk stok menipis.
- Filter rentang waktu global: 7 / 30 / 90 hari / Semua.
- Layout admin (sidebar + topbar) responsif.

### 3.2 Tidak Termasuk
- Login/auth, CRUD modul (produk/pesanan/pelanggan), halaman detail, notifikasi realtime, ekspor data.

## 4. Persyaratan Fungsional

| ID | Deskripsi |
|---|---|
| F-1 | KPI Pendapatan, Pesanan, Pelanggan baru, Produk stok menipis — berubah mengikuti filter rentang waktu |
| F-2 | Grafik penjualan per bulan (bar/line toggle), dalam rupiah |
| F-3 | Grafik donat distribusi penjualan per kategori |
| F-4 | Grafik pie metode pembayaran (Transfer, QRIS, E-Wallet, COD) |
| F-5 | Grafik bar horizontal 5 produk terlaris |
| F-6 | Tabel pesanan terbaru: search (nomor/nama pelanggan), filter status, sort kolom |
| F-7 | Tabel produk stok menipis (stok ≤ 5), dengan badge status |
| F-8 | Filter rentang waktu memicu reload data (fetch JSON `GET /api/dashboard?range=...`) tanpa reload halaman |

## 5. Struktur Data

| Tabel | Kolom penting |
|---|---|
| `categories` | name, slug, color |
| `products` | category_id, name, sku, price, stock, sold, image (emoji) |
| `customers` | name, email, phone, city |
| `orders` | order_number, customer_id, status, payment_method, shipping, discount, total, city, order_date |
| `order_items` | order_id, product_id, qty, price |
| `payments` | order_id, method, amount, status, paid_at |

**Volume dummy**: 8 kategori, 40 produk, 60 pelanggan, ~480 pesanan tersebar 12 bulan (growing trend), ~360 pembayaran. Status pesanan: delivered/shipped/paid/pending/cancelled (berbobot).

## 6. Persyaratan Non-Fungsional

- Load halaman < 2s (data dummy, agregasi via query agregate Eloquent, tanpa N+1).
- Responsif: grid 4 kolom (desktop) → 1 kolom (mobile).
- Tersedia secara offline (chart & styling di-bundle via Vite).

## 7. API

| Endpoint | Keterangan |
|---|---|
| `GET /` | Halaman dashboard |
| `GET /api/dashboard?range=7\|30\|90\|all` | JSON agregasi KPI + grafik + tabel (stok menipis) |

## 8. Roadmap Fase Lanjutan

1. **Fase 2** — Autentikasi admin + CRUD produk & kategori.
2. **Fase 3** — Manajemen pesanan (ubah status), detail pelanggan.
3. **Fase 4** — Ekspor CSV/PDF, notifikasi stok via email.
