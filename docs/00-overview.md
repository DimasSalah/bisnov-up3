# Overview — Sistem Monitoring E-Solution (Modernisasi dari Spreadsheet)

## Tujuan project

Mengganti proses pencatatan manual di Google Sheets (`MONITORING SIPRIMA - ELECTRIC SOLUTION UP3`) menjadi aplikasi web. Sumber data lama tersebar di 3 tab utama (`Recap Penawaran Esol`, `Realisasi E. Solution`, `Recap E-Solution 2026`) yang isinya project yang sama dicatat berulang di tahap berbeda. Prinsip aplikasi baru: **satu project = satu record, status berjalan maju, field bertambah tiap naik tahap — tidak ada input ulang data yang sudah ada.**

## Tech stack

**Full Laravel** — satu aplikasi, backend + frontend jadi satu repo, pakai starter kit Vue (Breeze/Jetstream) dengan **Inertia.js**. Bukan Laravel API terpisah + Nuxt. Database: MySQL/MariaDB (skema siap pakai di `05-database-schema.sql`).

## Daftar dokumen acuan

Baca semua file berikut sebagai satu kesatuan konteks sebelum mulai coding:

1. **`01-flow-bisnis.md`** — alur bisnis end-to-end, tahap status, dan aturan percabangan (gagal/lanjut).
2. **`02-data-model.md`** — daftar lengkap field per tahap, termasuk rumus perhitungan, tipe data yang disarankan, dan relasi antar entitas.
3. **`03-user-flow.md`** — alur interaksi user di UI: layar apa saja, urutan aksi, dan prinsip desain yang dipakai (termasuk kanban drag-and-drop).
4. **`04-mvp-plan.md`** — scope MVP, urutan milestone, dan breakdown teknis Laravel + Inertia/Vue.
5. **`05-database-schema.sql`** — DDL siap generate untuk semua tabel.

## Ringkasan alur (detail di `01-flow-bisnis.md`)

```
Probing → Penawaran → SPK (kontrak) → Pengerjaan → BAST (+ tagihan pelanggan) → Tagihan Mitra → Selesai
```

Cabang gagal (`Gagal WO`) bisa terjadi di tahap Probing atau Penawaran, sebelum SPK terbit.

## Status pengembangan saat ini

- Role/permission: **belum jadi prioritas**, seluruh aksi sementara dianggap dilakukan oleh satu role (`admin`). Role granular (marketing, admin, finance) adalah fitur tambahan kalau waktu pengerjaan masih longgar — jangan desain skema yang keras mengunci ke satu role, tapi jangan juga bangun sistem permission kompleks di awal.
- Fokus awal: struktur data & alur status dulu, baru UI mendetail.

## Catatan penting untuk AI assistant

- Semua istilah bahasa Indonesia di dokumen ini (Probing, Penawaran, SPK, BAST, Termin, Mitra, DPP, PPN, PPH) adalah istilah bisnis asli dari perusahaan — **jangan diterjemahkan atau diganti istilah generik** (misal jangan ganti "Mitra" jadi "Vendor" begitu saja) di UI atau nama field yang terlihat user, kecuali diminta eksplisit.
- Rumus finansial di `02-data-model.md` harus diimplementasikan persis seperti yang tertulis — ini rumus akuntansi resmi yang dipakai, bukan asumsi.
- BAST dan Tagihan Mitra berpotensi punya banyak baris per satu SPK (lihat catatan relasi di `02-data-model.md`) — jangan modelkan sebagai kolom tunggal di tabel project/SPK.
