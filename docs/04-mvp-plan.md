# Rencana MVP — Monitoring E-Solution (Laravel + Starter Kit Vue/Inertia)

Dokumen ini melengkapi `00-overview.md`, `01-flow-bisnis.md`, `02-data-model.md`, `03-user-flow.md`, `05-database-schema.sql`. Isinya: apa yang masuk MVP, urutan pengerjaan, dan breakdown teknis per stack.

## 1. Definisi "selesai" untuk MVP

MVP dianggap selesai kalau satu project bisa dijalankan penuh dari Probing sampai Selesai/Lunas lewat aplikasi, tanpa perlu buka spreadsheet sama sekali — walau tampilannya masih sederhana.

## 2. Scope

### Masuk MVP
- Login sederhana, satu role (`admin`), tanpa permission granular
- CRUD project dengan alur status berurutan (tombol "naikkan status", bukan dropdown bebas)
- Form Probing + Inspeksi, termasuk upload foto ke server
- Form Penawaran dengan kalkulasi otomatis (DPP, PPN, PPH, margin) sesuai rumus di `02-data-model.md`
- Form SPK, dengan field link Google Drive (teks URL)
- Form BAST — mendukung banyak baris per SPK (termin)
- Form Tagihan Mitra — mendukung banyak baris per SPK (batch/No Parking)
- Halaman daftar project — **tampilan kanban** (kolom = status, kartu = project), **drag-and-drop antar kolom untuk naikkan/pindah status**, plus filter & search
- Halaman detail project (timeline status + semua data tahap)
- Dashboard ringkas: jumlah project per status (angka saja, belum perlu grafik funnel visual)

### Belum masuk MVP (susulan kalau waktu masih ada)
- Role/permission granular (marketing/admin/finance terpisah)
- Notifikasi/reminder otomatis (project macet di satu status)
- Export ke Excel format kompatibel spreadsheet lama
- Dashboard funnel visual & SLA (`Durasi Lama Hari`)

## 3. Urutan pengerjaan (milestone)

**Milestone 1 — Fondasi & database**
1. Setup project Laravel dengan starter kit Vue (Laravel Breeze/Jetstream + Inertia.js) — satu repo full stack, tidak perlu setup backend & frontend terpisah.
2. Migration database sesuai urutan dependency:
   - `projects` (data inti + field probing + status)
   - `penawaran` (relasi 1-ke-1 ke `projects`)
   - `spk` (relasi 1-ke-1 ke `projects`)
   - `bast` (relasi 1-ke-banyak ke `spk`)
   - `tagihan_mitra` (relasi 1-ke-banyak ke `spk`)
   - `status_history` (log perubahan status)
3. Model Eloquent + relasi antar tabel.
4. Login sederhana (Laravel Breeze/Sanctum, satu role admin).

**Milestone 2 — Alur Probing sampai Penawaran**
1. API endpoint create/update project + upload foto (Probing & Inspeksi).
2. API endpoint create Penawaran dengan kalkulasi rumus di backend (jangan hitung di frontend saja, biar konsisten).
3. Halaman Vue (lewat Inertia): form Probing (dengan upload gambar), form Penawaran (angka rumus tampil read-only hasil hitung backend).
4. Endpoint aksi "naikkan status" + validasi (tidak bisa lompat tahap).

**Milestone 3 — Alur SPK sampai Tagihan Mitra**
1. API endpoint create SPK, BAST (banyak baris), Tagihan Mitra (banyak baris).
2. Halaman Vue (lewat Inertia): form SPK, form BAST (dengan tombol "tambah termin"), form Tagihan Mitra (dengan tombol "tambah batch").
3. Logika penutupan status ke "Selesai" begitu tagihan pelanggan (di BAST) dan tagihan mitra sama-sama lunas.

**Milestone 4 — Kanban, detail, dashboard**
1. Halaman daftar project: kanban (kolom = status) + filter/search. Endpoint drag-and-drop: `PATCH /projects/{id}/move` (terima `status_tujuan` + `position`).
2. Validasi drag: kartu cuma boleh di-drop ke kolom status yang valid (next status berurutan, atau mundur untuk koreksi) — drop ke kolom yang melompat tahap ditolak dengan pesan error.
3. Kalau drop ke kolom yang butuh data tambahan (SPK, BAST, Tagihan Mitra), otomatis buka modal form tahap itu setelah drop — status baru resmi tersimpan hanya setelah form disubmit, bukan langsung pindah kolom tanpa data.
4. Halaman detail project: timeline semua tahap dalam satu halaman.
5. Dashboard ringkas: hitung jumlah project per status.

**Milestone 5 — Rapikan & uji coba**
1. Validasi input di seluruh form (field wajib, format tanggal, format URL untuk link GDrive).
2. Uji alur penuh satu project dari Probing sampai Selesai.
3. Cek ulang rumus finansial dengan beberapa data asli dari spreadsheet sebagai pembanding.

## 4. Breakdown teknis

Stack: **satu aplikasi Laravel full stack**, pakai starter kit Vue (Breeze/Jetstream) dengan **Inertia.js** — bukan Laravel API + Nuxt terpisah. Controller Laravel langsung render komponen Vue lewat `Inertia::render()`, data dikirim sebagai props, tidak perlu bikin lapisan REST API sendiri maupun Blade view manual.

### Backend (Laravel)
- Controller per resource (`ProjectController`, `PenawaranController`, `SpkController`, `BastController`, `TagihanMitraController`) — pakai method resource standar (index/store/update), render ke Inertia.
- Validasi: Form Request class per controller, terutama untuk rumus finansial dan field wajib per tahap.
- Perhitungan rumus (DPP, PPN, PPH, margin): taruh di Model (accessor) atau Service class, bukan di controller — supaya bisa dipakai ulang dan gampang ditest.
- File upload Probing/Inspeksi: pakai Laravel filesystem (local disk dulu untuk MVP, gampang pindah ke S3 nanti).
- Field link Google Drive: cukup kolom string biasa + validasi format URL.
- Endpoint drag-and-drop kanban (`PATCH /projects/{id}/move`) tetap lewat route Laravel biasa, dipanggil dari Vue pakai Inertia router atau `axios`/`fetch` kalau butuh response tanpa reload halaman penuh.

### Frontend (Vue via Inertia)
- Halaman (page components) ditaruh di `resources/js/Pages`, routing tetap didefinisikan di `routes/web.php` seperti Laravel biasa — bukan file-based routing ala Nuxt.
- State/form: manfaatkan `useForm` bawaan Inertia untuk form probing/penawaran/SPK/BAST/tagihan mitra (sudah handle validation error & loading state otomatis, tidak perlu Pinia untuk kasus ini).
- Form bertahap (probing → penawaran → SPK → BAST → tagihan mitra) dibuat sebagai komponen Vue terpisah, dipanggil sesuai status project saat ini — bukan satu form raksasa.
- Komponen "Naikkan status" sebagai modal/dialog, bukan halaman terpisah, biar konteks project tetap kelihatan. Modal ini juga yang muncul otomatis setelah drag-and-drop ke kolom yang butuh data tambahan.
- Drag-and-drop kanban: pakai library ringan seperti `vuedraggable` (wrapper Vue untuk SortableJS) — jalan normal di atas Inertia, cukup untuk drag antar kolom + urutan dalam kolom, tidak perlu build sendiri.

## 5. Prioritas kalau waktu terbatas

Kalau harus dipotong, urutan yang paling aman dikorbankan duluan (dari yang paling boleh ditunda ke paling penting dipertahankan):
1. Dashboard ringkas (bisa sementara diganti hitung manual dari list)
2. Search & filter lanjutan (cukup filter status dulu)
3. Upload foto Probing (bisa sementara pakai field link biasa dulu, upload ke server nyusul)
4. Alur status & rumus finansial — **ini inti MVP, jangan dipotong.**
