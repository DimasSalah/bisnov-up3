# User Flow & Prinsip Desain UI

Role saat ini: satu role (`admin`) melakukan semua aksi. Role granular (marketing/admin/finance terpisah) adalah fitur tambahan opsional kalau waktu pengerjaan longgar — jangan kunci skema database ke satu role, tapi jangan bangun sistem permission kompleks di fase awal.

## 1. Prinsip desain

1. **Status sebagai aksi, bukan dropdown bebas.** Naik status lewat tombol "Naikkan status" yang membuka form tahap berikutnya secara berurutan — mencegah tahap ke-skip atau data cacat.
2. **Satu halaman detail = single source of truth per project.** Semua histori (probing → penawaran → SPK → BAST → tagihan mitra) ada di satu halaman sebagai timeline, bukan tab terpisah.
3. **List utama berbentuk kanban dengan drag-and-drop** (kolom = status: Probing, Penawaran, SPK, BAST, Tagihan Mitra, Selesai, Gagal WO). Drag kartu ke kolom berikutnya untuk naikkan status — tapi drop yang melompat tahap ditolak, dan drop ke kolom yang butuh data tambahan (SPK/BAST/Tagihan Mitra) otomatis membuka modal form tahap itu; status baru baru tersimpan setelah form disubmit.
4. **Mobile-first untuk form Probing** — sering diisi langsung di lapangan: kamera untuk dokumentasi, GPS otomatis untuk lokasi.

## 2. Peta layar

- **Dashboard** — ringkasan angka & funnel konversi antar status (Probing → Penawaran → SPK → BAST), titik masuk aplikasi.
- **Daftar project (kanban/list)** — pintu masuk harian, filter status/mitra/marketing, search nomor penawaran.
- **Form probing** — entry point project baru.
- **Detail project** — hub utama: timeline status, tombol naikkan status, daftar BAST (bisa lebih dari satu baris), daftar Tagihan Mitra (bisa lebih dari satu baris).
- **Modal update status** — form khusus per tahap, hanya minta field baru yang relevan; data lama tampil read-only sebagai konteks.
- **Laporan & export** — rekap tahunan, export ke Excel format kompatibel dengan tab lama.

## 3. Urutan aksi user (skenario end-to-end)

1. **Buka dashboard** → klik tombol "+ Project baru".
2. **Isi form probing** — data pelanggan, lokasi, PIC, scope pekerjaan, foto dokumentasi. Submit → project tercatat status `Probing`, muncul di kanban.
3. **Pilih "Opsi Selanjutnya"** (bagian dari form yang sama):
   - Permintaan Penawaran → lanjut ke langkah 4.
   - Butuh Survey Ulang / Masih mempertimbangkan → tetap status `Probing`, jadi reminder follow-up.
   - Sudah punya vendor lain → project ditutup sebagai `Gagal WO`.
4. **Isi form penawaran** — data pelanggan dari probing tampil read-only, user isi rincian harga (Jasa/Material sisi Pelanggan & Mitra). Sistem hitung otomatis DPP/PPN/PPH/margin. Simpan → status naik jadi `Penawaran`.
5. **Buka detail project** (dari kanban, klik kartu) → lihat timeline, klik tombol "Naikkan status".
6. **Ulangi tombol "Naikkan status" per tahap:**
   - Ke `SPK` → modal minta No SPK, tanggal kontrak, tanggal pekerjaan, PIC BAST/BAPP, upload berkas SPK.
   - Ke `BAST` → modal minta data BAST + tagihan pelanggan untuk termin ini. Kalau ada termin lagi, klik "Tambah BAST" — bukan menimpa baris sebelumnya.
   - Ke `Tagihan Mitra` → modal minta data pembayaran ke mitra (No Parking, nilai SPK, status SmartPayment, tanggal verifikator/approval/perbendaharaan). Bisa tambah baris baru per batch pembayaran.
7. **Selesai otomatis** — begitu tagihan pelanggan (dalam BAST) dan tagihan mitra sama-sama berstatus lunas, project keluar dari kanban aktif dan masuk laporan rekap.

## 4. Catatan tambahan untuk pengembangan lanjutan (opsional, bukan prioritas awal)

- Role/permission granular per tahap (marketing hanya probing & penawaran; finance hanya BAST & tagihan mitra).
- Notifikasi/reminder untuk project yang macet di satu status terlalu lama.
- Dashboard SLA khusus untuk memantau `Durasi Lama Hari` di proses Tagihan Mitra.
