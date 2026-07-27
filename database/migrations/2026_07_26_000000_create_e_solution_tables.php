<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_probing');
            $table->string('unit_layanan', 150);
            $table->string('nama_pic_beyond_kwh', 150)->nullable();
            $table->string('nama_perusahaan', 200);
            $table->text('alamat_perusahaan');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('scope_pekerjaan', ['electric_services', 'ev_ecosystem']);
            $table->string('potensi_pekerjaan', 150)->nullable();
            $table->string('nama_pic_pelanggan', 150);
            $table->string('nomor_pic_pelanggan', 30);
            $table->date('tanggal_inspeksi')->nullable();
            $table->enum('opsi_selanjutnya', [
                'permintaan_penawaran',
                'butuh_survey_ulang',
                'masih_mempertimbangkan',
                'sudah_punya_vendor_lain',
            ])->nullable();
            $table->enum('status', [
                'probing',
                'penawaran',
                'spk',
                'bast',
                'tagihan_mitra',
                'selesai',
                'gagal_wo',
            ])->default('probing');
            $table->timestamps();
        });

        Schema::create('probing_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('tipe', ['dokumentasi_probing', 'evidence_inspeksi']);
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('penawaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('no_penawaran', 50);
            $table->string('pic_penawaran', 150);
            $table->decimal('nilai_jasa_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_material_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_dpp_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_ppn_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_pph_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_dpp_ppn_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_terbayar_pelanggan', 15, 2)->default(0);
            $table->decimal('nilai_jasa_mitra', 15, 2)->default(0);
            $table->decimal('nilai_material_mitra', 15, 2)->default(0);
            $table->decimal('nilai_dpp_mitra', 15, 2)->default(0);
            $table->decimal('nilai_ppn_mitra', 15, 2)->default(0);
            $table->decimal('nilai_pph_mitra', 15, 2)->default(0);
            $table->decimal('nilai_dpp_ppn_mitra', 15, 2)->default(0);
            $table->decimal('nilai_terbayar_mitra', 15, 2)->default(0);
            $table->decimal('margin_dpp', 15, 2)->default(0);
            $table->decimal('margin_ppn', 15, 2)->default(0);
            $table->decimal('margin_persen', 6, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('spk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('no_spk_pelanggan', 50);
            $table->date('tanggal_awal_kontrak');
            $table->date('tanggal_akhir_kontrak');
            $table->date('tanggal_pekerjaan')->nullable();
            $table->string('nama_pic_bast', 150)->nullable();
            $table->string('nama_pic_bapp', 150)->nullable();
            $table->string('nama_mitra', 200);
            $table->string('berkas_spk', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('bast', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('spk')->cascadeOnDelete();
            $table->string('termin', 30);
            $table->decimal('persentase', 5, 2);
            $table->string('no_bast', 50);
            $table->date('tanggal_bast');
            $table->string('no_invoice', 50)->nullable();
            $table->date('tanggal_invoice')->nullable();
            $table->string('no_faktur_pajak', 50)->nullable();
            $table->date('tanggal_faktur_pajak')->nullable();
            $table->decimal('nominal_bayar_pelanggan', 15, 2)->default(0);
            $table->date('tanggal_bayar_pelanggan')->nullable();
            $table->enum('status_bayar_pelanggan', ['belum_ditagih', 'sudah_invoice', 'lunas'])->default('belum_ditagih');
            $table->text('keterangan')->nullable();
            $table->string('berkas_penawaran', 500)->nullable();
            $table->string('berkas_bast_bapp', 500)->nullable();
            $table->string('berkas_tagihan', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('tagihan_mitra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('spk')->cascadeOnDelete();
            $table->string('nama_mitra', 200);
            $table->string('no_spk', 50);
            $table->date('tanggal_spk');
            $table->string('no_parking', 50)->nullable();
            $table->decimal('material', 15, 2)->default(0);
            $table->decimal('jasa', 15, 2)->default(0);
            $table->decimal('nilai_spk_exc_ppn', 15, 2)->default(0);
            $table->decimal('nilai_spk_inc_ppn', 15, 2)->default(0);
            $table->string('status_smartpayment', 50)->nullable();
            $table->date('tgl_verifikator')->nullable();
            $table->date('tgl_approval')->nullable();
            $table->date('tgl_man_perbendaharaan')->nullable();
            $table->unsignedInteger('durasi_lama_hari')->nullable();
            $table->decimal('nilai_terbayar', 15, 2)->default(0);
            $table->date('tanggal_bayar')->nullable();
            $table->string('status_nodin', 50)->nullable();
            $table->date('tanggal_nodin')->nullable();
            $table->string('no_nodin', 50)->nullable();
            $table->string('scan_nodin', 500)->nullable();
            $table->string('scan_tagihan_mitra', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('status_dari', 30)->nullable();
            $table->string('status_ke', 30);
            $table->text('keterangan')->nullable();
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_history');
        Schema::dropIfExists('tagihan_mitra');
        Schema::dropIfExists('bast');
        Schema::dropIfExists('spk');
        Schema::dropIfExists('penawaran');
        Schema::dropIfExists('probing_attachments');
        Schema::dropIfExists('projects');
    }
};
