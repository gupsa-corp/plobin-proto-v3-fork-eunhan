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
        Schema::create('project_sandboxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable(); // Allow null values
            $table->string('name', 100); // 샌드박스 이름 (storage-sandbox-{name} 형태로 저장)
            $table->text('description')->nullable(); // 샌드박스 설명
            $table->string('template_id')->nullable(); // Template ID
            $table->enum('status', ['active', 'inactive', 'error'])->default('active'); // 샌드박스 상태
            $table->json('settings')->nullable(); // 샌드박스 추가 설정 (JSON 형태)
            $table->timestamp('last_accessed_at')->nullable(); // 마지막 접근 시간
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // 생성자
            $table->timestamps();
            
            // Add foreign key constraint allowing null values
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // 인덱스 추가
            $table->index(['project_id', 'status']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_sandboxes');
    }
};
