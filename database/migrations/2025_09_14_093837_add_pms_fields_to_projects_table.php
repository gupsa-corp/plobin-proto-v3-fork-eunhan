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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('description'); // pending, in-progress, completed, on-hold, cancelled
            $table->string('priority')->default('medium')->after('status'); // low, medium, high
            $table->integer('progress')->default(0)->after('priority'); // 0-100
            $table->json('pms_metadata')->nullable()->after('progress'); // 확장 가능한 PMS 메타데이터
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority', 'progress', 'pms_metadata']);
        });
    }
};
