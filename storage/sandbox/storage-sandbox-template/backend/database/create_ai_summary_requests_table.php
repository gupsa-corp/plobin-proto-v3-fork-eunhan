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
        Schema::create('ai_summary_requests', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 255); // 업로드된 파일명
            $table->text('description')->nullable(); // 파일에 대한 설명 (선택사항)
            $table->string('request_id', 100)->unique(); // AI 서버에서 반환받은 요청 ID
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending'); // 요약 처리 상태
            $table->text('error_message')->nullable(); // 실패 시 오류 메시지
            $table->timestamp('requested_at'); // 요약 요청 시간
            $table->timestamp('completed_at')->nullable(); // 요약 완료 시간
            $table->unsignedBigInteger('user_id')->nullable(); // 요청한 사용자 ID
            $table->timestamps(); // created_at, updated_at

            // 인덱스
            $table->index('file_name');
            $table->index('request_id');
            $table->index('status');
            $table->index('requested_at');
            $table->index('user_id');

            // 외래 키 제약조건 (선택적)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_summary_requests');
    }
};
