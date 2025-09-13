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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // 로그 유형 (hourly_check 등)
            $table->string('message'); // 로그 메시지
            $table->json('data')->nullable(); // JSON 형태의 추가 데이터
            $table->timestamp('created_at')->index(); // 생성 시간에 인덱스
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
