<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('nilai_project_dpp', 18, 2)->nullable()->after('status');
            $table->decimal('nilai_project_inc_ppn', 18, 2)->nullable()->after('nilai_project_dpp');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['nilai_project_dpp', 'nilai_project_inc_ppn']);
        });
    }
};
