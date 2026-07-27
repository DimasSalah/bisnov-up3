-- =====================================================================
-- Skema database — Monitoring E-Solution
-- Sesuai dengan 02-data-model.md. MySQL 8 / MariaDB syntax.
-- Urutan CREATE TABLE mengikuti urutan dependency foreign key.
-- =====================================================================

-- 1. PROJECTS — data inti + Probing/Inspeksi + status project berjalan
CREATE TABLE projects (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Probing
    tanggal_probing         DATE NOT NULL,
    unit_layanan            VARCHAR(150) NOT NULL,
    nama_pic_beyond_kwh     VARCHAR(150) NULL,
    nama_perusahaan         VARCHAR(200) NOT NULL,
    alamat_perusahaan       TEXT NOT NULL,
    latitude                DECIMAL(10,7) NULL,
    longitude               DECIMAL(10,7) NULL,
    scope_pekerjaan         ENUM('electric_services','ev_ecosystem') NOT NULL,
    potensi_pekerjaan       VARCHAR(150) NULL,
    nama_pic_pelanggan      VARCHAR(150) NOT NULL,
    nomor_pic_pelanggan     VARCHAR(30) NOT NULL,

    -- Inspeksi
    tanggal_inspeksi        DATE NULL,

    -- Percabangan keputusan
    opsi_selanjutnya        ENUM(
                                'permintaan_penawaran',
                                'butuh_survey_ulang',
                                'masih_mempertimbangkan',
                                'sudah_punya_vendor_lain'
                            ) NULL,

    -- Status berjalan (dipakai buat kolom kanban)
    status                  ENUM(
                                'probing',
                                'penawaran',
                                'spk',
                                'bast',
                                'tagihan_mitra',
                                'selesai',
                                'gagal_wo'
                            ) NOT NULL DEFAULT 'probing',

    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. PROBING_ATTACHMENTS — file diupload ke server sendiri, bisa banyak per project
CREATE TABLE probing_attachments (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id              BIGINT UNSIGNED NOT NULL,
    tipe                    ENUM('dokumentasi_probing','evidence_inspeksi') NOT NULL,
    file_path               VARCHAR(255) NOT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. PENAWARAN — relasi 1-ke-1 dengan projects
CREATE TABLE penawaran (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id                  BIGINT UNSIGNED NOT NULL UNIQUE,

    no_penawaran                VARCHAR(50) NOT NULL,
    pic_penawaran                VARCHAR(150) NOT NULL,

    -- Sisi Pelanggan
    nilai_jasa_pelanggan        DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_material_pelanggan    DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_dpp_pelanggan         DECIMAL(15,2) NOT NULL DEFAULT 0, -- jasa + material
    nilai_ppn_pelanggan         DECIMAL(15,2) NOT NULL DEFAULT 0, -- (dpp*11/12)*12%
    nilai_pph_pelanggan         DECIMAL(15,2) NOT NULL DEFAULT 0, -- jasa*0.02
    nilai_dpp_ppn_pelanggan     DECIMAL(15,2) NOT NULL DEFAULT 0, -- dpp + ppn
    nilai_terbayar_pelanggan    DECIMAL(15,2) NOT NULL DEFAULT 0, -- dpp + ppn - pph

    -- Sisi Mitra
    nilai_jasa_mitra            DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_material_mitra        DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_dpp_mitra             DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_ppn_mitra             DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_pph_mitra             DECIMAL(15,2) NOT NULL DEFAULT 0, -- jasa*0.02
    nilai_dpp_ppn_mitra         DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_terbayar_mitra        DECIMAL(15,2) NOT NULL DEFAULT 0,

    -- Margin
    margin_dpp                  DECIMAL(15,2) NOT NULL DEFAULT 0, -- dpp pelanggan - dpp mitra
    margin_ppn                  DECIMAL(15,2) NOT NULL DEFAULT 0, -- (dpp+ppn pelanggan) - (dpp+ppn mitra)
    margin_persen                DECIMAL(6,2) NOT NULL DEFAULT 0, -- (margin_dpp / dpp_mitra) * 100

    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. SPK — relasi 1-ke-1 dengan projects
CREATE TABLE spk (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id              BIGINT UNSIGNED NOT NULL UNIQUE,

    no_spk_pelanggan        VARCHAR(50) NOT NULL,
    tanggal_awal_kontrak     DATE NOT NULL,
    tanggal_akhir_kontrak    DATE NOT NULL,
    tanggal_pekerjaan        DATE NULL,
    nama_pic_bast            VARCHAR(150) NULL,
    nama_pic_bapp            VARCHAR(150) NULL,
    nama_mitra               VARCHAR(200) NOT NULL,
    berkas_spk               VARCHAR(500) NULL, -- link Google Drive

    created_at               TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. BAST — relasi 1-ke-banyak dengan spk (per termin)
CREATE TABLE bast (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    spk_id                      BIGINT UNSIGNED NOT NULL,

    termin                      VARCHAR(30) NOT NULL,       -- mis. "Termin I"
    persentase                  DECIMAL(5,2) NOT NULL,      -- persen nilai termin ini

    no_bast                     VARCHAR(50) NOT NULL,
    tanggal_bast                DATE NOT NULL,

    no_invoice                  VARCHAR(50) NULL,
    tanggal_invoice              DATE NULL,
    no_faktur_pajak              VARCHAR(50) NULL,
    tanggal_faktur_pajak         DATE NULL,

    nominal_bayar_pelanggan      DECIMAL(15,2) NOT NULL DEFAULT 0,
    tanggal_bayar_pelanggan      DATE NULL,
    status_bayar_pelanggan       ENUM('belum_ditagih','sudah_invoice','lunas') NOT NULL DEFAULT 'belum_ditagih',

    keterangan                   TEXT NULL,

    berkas_penawaran              VARCHAR(500) NULL, -- link Google Drive
    berkas_bast_bapp              VARCHAR(500) NULL, -- link Google Drive
    berkas_tagihan                 VARCHAR(500) NULL, -- link Google Drive

    created_at                    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (spk_id) REFERENCES spk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. TAGIHAN_MITRA — relasi 1-ke-banyak dengan spk (per batch/No Parking)
CREATE TABLE tagihan_mitra (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    spk_id                      BIGINT UNSIGNED NOT NULL,

    nama_mitra                   VARCHAR(200) NOT NULL,
    no_spk                       VARCHAR(50) NOT NULL,
    tanggal_spk                  DATE NOT NULL,
    no_parking                   VARCHAR(50) NULL,

    material                     DECIMAL(15,2) NOT NULL DEFAULT 0,
    jasa                         DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_spk_exc_ppn             DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai_spk_inc_ppn             DECIMAL(15,2) NOT NULL DEFAULT 0,

    status_smartpayment           VARCHAR(50) NULL,
    tgl_verifikator                DATE NULL,
    tgl_approval                  DATE NULL,
    tgl_man_perbendaharaan         DATE NULL,

    nilai_terbayar                 DECIMAL(15,2) NOT NULL DEFAULT 0,
    tanggal_bayar                  DATE NULL,

    status_nodin                   VARCHAR(50) NULL,
    tanggal_nodin                   DATE NULL,
    no_nodin                        VARCHAR(50) NULL,
    scan_nodin                      VARCHAR(500) NULL, -- link Google Drive
    scan_tagihan_mitra               VARCHAR(500) NULL, -- link Google Drive

    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (spk_id) REFERENCES spk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. STATUS_HISTORY — log perubahan status, dasar tampilan timeline & kanban
CREATE TABLE status_history (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id              BIGINT UNSIGNED NOT NULL,

    status_dari             VARCHAR(30) NULL,
    status_ke               VARCHAR(30) NOT NULL,
    keterangan              TEXT NULL,

    changed_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- Catatan:
-- - Field turunan (Bulan/Tahun BAST, Bulan Bayar Pelanggan, Durasi Lama Hari)
--   sengaja TIDAK disimpan sebagai kolom — dihitung via query
--   (MONTH(tanggal_bast), DATEDIFF(tanggal_bayar, tgl_verifikator), dst)
--   supaya tidak ada risiko data turunan tidak sinkron dengan tanggal aslinya.
-- - Kolom `status` di tabel projects adalah kolom yang dipakai kanban board
--   (satu kolom kanban = satu nilai enum status).
-- =====================================================================
