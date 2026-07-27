<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spk', function (Blueprint $table) {
            $table->string('no_spk_pelanggan', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('spk', function (Blueprint $table) {
            $table->string('no_spk_pelanggan', 50)->nullable()->change();
        });
    }
};
