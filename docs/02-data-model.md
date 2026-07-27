# Data Model — Field per Tahap

Struktur dasar: satu record `project`, dengan entitas anak per tahap. Field bertambah sesuai status, tidak menimpa data tahap sebelumnya.

```
projects            → 1 baris per project (data probing + status saat ini)
penawaran            → 1-ke-1 dengan project
spk                  → 1-ke-1 dengan project
bast                 → 1-ke-banyak dengan spk (per termin)
tagihan_mitra        → 1-ke-banyak dengan spk (per batch/No Parking)
status_history        → log perubahan status + tanggal
```

## Cara penyimpanan attachment (penting)

Ada 2 mekanisme berbeda, jangan disamakan:

| Tahap                      | Cara simpan                                                                   | Field terkait                                                                  |
| -------------------------- | ----------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| Probing / Inspeksi         | **Upload ke server aplikasi** (file storage sendiri, bukan URL eksternal)     | Dokumentasi Probing, Evidence Inspeksi                                         |
| SPK / BAST / Tagihan Mitra | **Link Google Drive** (field disimpan sebagai URL teks, file tetap di GDrive) | Berkas SPK, Berkas BAST & BAPP, Berkas Tagihan, Scan Nodin, Scan Tagihan Mitra |

Implikasi teknis: field attachment tahap Probing butuh endpoint upload + storage bucket sendiri (misal S3/local disk) dan validasi ukuran/tipe file. Field attachment tahap SPK/BAST/Tagihan Mitra cukup input teks biasa (validasi format URL), tidak perlu handling file upload di backend.

## §1. Probing (data dasar project)

| Field                       | Tipe / catatan                                                                                                            |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| Tanggal Probing             | tanggal                                                                                                                   |
| Unit Layanan                |                                                                                                                           |
| Nama PIC Beyond KWH         |                                                                                                                           |
| Nama Perusahaan (Pelanggan) |                                                                                                                           |
| Alamat Perusahaan           | idealnya lengkap dengan Latitude/Longitude                                                                                |
| Scope Pekerjaan             | pilihan: Electric Services / EV Ecosystem                                                                                 |
| Potensi Pekerjaan           |                                                                                                                           |
| Dokumentasi Probing         | **upload gambar ke server**, bisa lebih dari satu                                                                         |
| Nama PIC Pelanggan          |                                                                                                                           |
| Nomor PIC Pelanggan         |                                                                                                                           |
| Tanggal Inspeksi            | opsional, kalau ada tahap survey lanjutan setelah probing awal                                                            |
| Evidence Inspeksi           | **upload gambar ke server**, bisa lebih dari satu                                                                         |
| Opsi Selanjutnya            | pilihan: Permintaan Penawaran / Butuh Survey Ulang / Pelanggan masih mempertimbangkan / Pelanggan sudah punya vendor lain |

## §2. Penawaran

| Field                       | Rumus / catatan                             |
| --------------------------- | ------------------------------------------- |
| No Penawaran                |                                             |
| PIC Penawaran               |                                             |
| **Sisi Pelanggan**          |                                             |
| Nilai Jasa Pelanggan        | input manual                                |
| Nilai Material Pelanggan    | input manual                                |
| Nilai DPP Pelanggan         | `Jasa + Material`                           |
| Nilai PPN Pelanggan         | `(DPP * 11/12) * 12%`                       |
| Nilai PPH 23 Pelanggan (2%) | `Jasa * 0.02`                               |
| Nilai DPP + PPN Pelanggan   | `DPP + PPN`                                 |
| Nilai Terbayar Pelanggan    | `DPP + PPN - PPH`                           |
| **Sisi Mitra**              |                                             |
| Nilai Jasa Mitra            | input manual                                |
| Nilai Material Mitra        | input manual                                |
| Nilai DPP Mitra             | `Jasa + Material`                           |
| Nilai PPN Mitra             | `(DPP * 11/12) * 12%`                       |
| Nilai PPH 23 Mitra (2%)     | `Jasa * 0.02`                               |
| Nilai DPP + PPN Mitra       | `DPP + PPN`                                 |
| Nilai Terbayar Mitra        | `DPP + PPN - PPH`                           |
| **Margin**                  |                                             |
| Margin DPP                  | `DPP Pelanggan - DPP Mitra`                 |
| Margin PPN                  | `(DPP + PPN Pelanggan) - (DPP + PPN Mitra)` |
| Margin %                    | `(Margin DPP / DPP Mitra) * 100%`           |

## §3. SPK (kontrak)

| Field                      | Tipe / catatan                   |
| -------------------------- | -------------------------------- |
| No SPK Pelanggan           |                                  |
| Tanggal Awal Kontrak (SPK) |                                  |
| Tanggal Akhir Kontrak      |                                  |
| Tanggal Pekerjaan          | tanggal eksekusi lapangan        |
| Nama PIC BAST              |                                  |
| Nama PIC BAPP              |                                  |
| Nama Mitra                 |                                  |
| Berkas SPK                 | **link Google Drive** (URL teks) |

## §4. BAST (termasuk tagihan ke pelanggan)

**Relasi: satu SPK bisa punya banyak baris BAST** (satu per termin pembayaran). Field `Termin` dan `%` yang menjadi dasar kenapa relasinya harus satu-ke-banyak, bukan kolom tunggal.

| Field                   | Tipe / catatan                             |
| ----------------------- | ------------------------------------------ |
| Termin                  | mis. Termin I, Termin II                   |
| %                       | persentase nilai termin ini dari total SPK |
| No BAST                 |                                            |
| Tanggal BAST            |                                            |
| Bulan BAST              | derivatif dari Tanggal BAST                |
| Tahun BAST              | derivatif dari Tanggal BAST                |
| No Invoice              |                                            |
| Tanggal Invoice         |                                            |
| No Faktur Pajak         |                                            |
| Tanggal Faktur Pajak    |                                            |
| Nominal Bayar Pelanggan |                                            |
| Tanggal Bayar Pelanggan |                                            |
| Bulan Bayar Pelanggan   | derivatif dari Tanggal Bayar Pelanggan     |
| Status Bayar Pelanggan  | mis. belum ditagih / sudah invoice / lunas |
| Keterangan              | catatan bebas                              |
| Berkas Penawaran        | **link Google Drive** (URL teks)           |
| Berkas BAST & BAPP      | **link Google Drive** (URL teks)           |
| Berkas Tagihan          | **link Google Drive** (URL teks)           |

## §5. Tagihan Mitra

**Relasi: satu SPK bisa punya banyak baris Tagihan Mitra** (per batch/No Parking pembayaran ke mitra). Alur ini punya proses approval berjenjang sendiri, terpisah dari tagihan pelanggan.

| Field                  | Tipe / catatan                                                      |
| ---------------------- | ------------------------------------------------------------------- |
| Nama Mitra             |                                                                     |
| No SPK                 | referensi ke SPK terkait                                            |
| Tanggal SPK            |                                                                     |
| No Parking             | nomor batch pembayaran internal                                     |
| Material               |                                                                     |
| Jasa                   |                                                                     |
| Nilai SPK (Exc PPN)    |                                                                     |
| Nilai SPK (Inc PPN)    |                                                                     |
| Status di SmartPayment | status di sistem pembayaran internal                                |
| Tgl Verifikator        | tahap approval 1                                                    |
| Tgl Approval           | tahap approval 2                                                    |
| Tgl Man Perbendaharaan | tahap approval 3 (final)                                            |
| Durasi Lama Hari       | derivatif: selisih hari dari SPK sampai lunas, untuk monitoring SLA |
| Nilai Terbayar         |                                                                     |
| Tanggal Bayar          |                                                                     |
| Status Nodin           |                                                                     |
| Tanggal Nodin          |                                                                     |
| No Nodin               |                                                                     |
| Scan Nodin             | **link Google Drive** (URL teks)                                    |
| Scan Tagihan Mitra     | **link Google Drive** (URL teks)                                    |

## Catatan implementasi

- Field turunan (Bulan, Tahun, Durasi Lama Hari) sebaiknya dihitung otomatis di backend/query, bukan diinput manual, supaya konsisten dan tidak bisa salah ketik.
- Semua rumus finansial di §2 harus dihitung otomatis saat input, ditampilkan sebagai read-only ke user (bukan field yang bisa diedit bebas) untuk mencegah kesalahan hitung manual seperti yang terjadi di spreadsheet lama.
- Attachment Probing/Inspeksi perlu endpoint upload + storage sendiri (validasi ukuran & tipe file gambar). Attachment SPK/BAST/Tagihan Mitra cukup field teks URL, cukup validasi format link, tidak perlu handling file di backend.

## Skema SQL

Struktur tabel siap pakai (MySQL/MariaDB) ada di `05-database-schema.sql` — tinggal generate langsung, sudah termasuk foreign key dan urutan `CREATE TABLE` yang benar sesuai dependency.

## Riwayat validasi

Dicek ulang terhadap sheet asli (`Recap E-Solution 2026` kolom 2–76 dan `Rincian SPK 2026`). Field yang ditambahkan pada validasi ini dibanding versi sebelumnya: `Termin`, `%` (BAST), `Bulan Bayar Pelanggan`, `Keterangan` (BAST), `Tanggal Nodin`, `No Nodin`, `Scan Nodin`, `Scan Tagihan Mitra` (Tagihan Mitra), `Tanggal Inspeksi`, `Evidence Inspeksi` (Probing).
