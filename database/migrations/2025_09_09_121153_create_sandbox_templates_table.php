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
        Schema::create('sandbox_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // 템플릿 이름 (샌드박스 컨테이너 폴더명)
            $table->text('description')->nullable(); // 템플릿 설명
            $table->enum('type', ['system', 'custom'])->default('custom'); // 시스템 템플릿 vs 사용자 생성 템플릿
            $table->enum('status', ['active', 'inactive'])->default('active'); // 템플릿 상태
            $table->json('settings')->nullable(); // 템플릿 추가 설정 (JSON 형태)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // 생성자 (시스템 템플릿의 경우 null)
            $table->integer('usage_count')->default(0); // 사용 횟수
            $table->timestamps();
            
            // 템플릿 이름은 유니크해야 함
            $table->unique('name');
            
            // 인덱스 추가
            $table->index(['type', 'status']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandbox_templates');
    }
};
