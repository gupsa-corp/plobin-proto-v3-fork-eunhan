<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, in-progress, completed, on-hold, cancelled
            $table->string('priority')->default('medium'); // low, medium, high
            $table->integer('progress')->default(0); // 0-100
            $table->json('pms_metadata')->nullable(); // 확장 가능한 PMS 메타데이터
            $table->string('sandbox_folder')->nullable(); // 샌드박스 타입
            $table->boolean('allow_individual_sandbox_per_page')->default(false)->comment('페이지별로 개별 샌드박스 선택 허용 여부');
            $table->string('default_access_level')->default('member'); // 기본 접근 레벨
            $table->json('project_roles')->nullable(); // 프로젝트 역할
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->softDeletes(); // Soft delete 추가
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
