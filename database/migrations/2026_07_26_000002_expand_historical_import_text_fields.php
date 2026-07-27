<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spk', function (Blueprint $table) {
            $table->string('nama_mitra', 255)->change();
        });

        Schema::table('tagihan_mitra', function (Blueprint $table) {
            $table->string('no_spk', 255)->nullable()->change();
            $table->string('status_smartpayment', 255)->nullable()->change();
            $table->string('status_nodin', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_mitra', function (Blueprint $table) {
            $table->string('no_spk', 255)->nullable()->change();
            $table->string('status_smartpayment', 255)->nullable()->change();
            $table->string('status_nodin', 255)->nullable()->change();
        });

        Schema::table('spk', function (Blueprint $table) {
            $table->string('nama_mitra', 255)->change();
        });
    }
};
