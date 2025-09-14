<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_pages', function (Blueprint $table) {
            $table->string('sandbox_domain')->nullable()->after('sandbox_custom_screen_folder')->comment('샌드박스 도메인명');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_pages', function (Blueprint $table) {
            $table->dropColumn('sandbox_domain');
        });
    }
};
