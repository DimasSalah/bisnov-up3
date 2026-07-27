<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('import_source_key', 191)->nullable()->unique()->after('id');
            $table->enum('scope_pekerjaan', ['electric_services', 'ev_ecosystem'])->nullable()->change();
            $table->string('nama_pic_pelanggan', 150)->nullable()->change();
            $table->string('nomor_pic_pelanggan', 30)->nullable()->change();
        });

        Schema::table('spk', function (Blueprint $table) {
            $table->date('tanggal_akhir_kontrak')->nullable()->change();
        });

        Schema::table('bast', function (Blueprint $table) {
            $table->date('tanggal_bast')->nullable()->change();
        });

        Schema::table('tagihan_mitra', function (Blueprint $table) {
            $table->date('tanggal_spk')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_mitra', function (Blueprint $table) {
            $table->date('tanggal_spk')->nullable()->change();
        });

        Schema::table('bast', function (Blueprint $table) {
            $table->date('tanggal_bast')->nullable()->change();
        });

        Schema::table('spk', function (Blueprint $table) {
            $table->date('tanggal_akhir_kontrak')->nullable()->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['import_source_key']);
            $table->dropColumn('import_source_key');
            $table->enum('scope_pekerjaan', ['electric_services', 'ev_ecosystem'])->nullable()->change();
            $table->string('nama_pic_pelanggan', 150)->nullable()->change();
            $table->string('nomor_pic_pelanggan', 30)->nullable()->change();
        });
    }
};
