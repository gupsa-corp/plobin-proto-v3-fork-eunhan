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
        Schema::create('ai_summary_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id'); // ai_summary_requests 테이블의 ID
            $table->string('version', 50); // 버전 (v년월일시분초 형식)
            $table->text('summary_content'); // AI 요약 내용
            $table->enum('status', ['success', 'failed', 'unavailable'])->default('success'); // 요약 결과 상태
            $table->text('error_message')->nullable(); // 실패 시 오류 메시지
            $table->timestamp('created_at'); // 버전 생성 시간
            $table->unsignedBigInteger('user_id')->nullable(); // 생성한 사용자 ID
            $table->timestamps(); // created_at, updated_at

            // 인덱스
            $table->index('request_id');
            $table->index('version');
            $table->index('status');
            $table->index('created_at');
            $table->index('user_id');

            // 외래 키 제약조건
            $table->foreign('request_id')->references('id')->on('ai_summary_requests')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_summary_results');
    }
};
