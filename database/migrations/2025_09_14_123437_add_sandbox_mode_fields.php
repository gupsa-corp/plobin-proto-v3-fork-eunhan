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
        // 프로젝트 테이블에 페이지별 개별 샌드박스 허용 필드 추가
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('allow_individual_sandbox_per_page')
                  ->default(false)
                  ->after('sandbox_folder')
                  ->comment('페이지별로 개별 샌드박스 선택 허용 여부');
        });

        // 프로젝트 페이지 테이블에 샌드박스 모드 필드 추가
        Schema::table('project_pages', function (Blueprint $table) {
            $table->enum('sandbox_mode', ['project', 'individual'])
                  ->default('project')
                  ->after('sandbox_folder')
                  ->comment('샌드박스 선택 모드: project(프로젝트 따름), individual(개별 선택)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('allow_individual_sandbox_per_page');
        });

        Schema::table('project_pages', function (Blueprint $table) {
            $table->dropColumn('sandbox_mode');
        });
    }
};
