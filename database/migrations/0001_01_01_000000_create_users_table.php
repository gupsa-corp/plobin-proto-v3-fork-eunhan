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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            
            // 전화번호 관련 필드
            $table->string('country_code', 10)->nullable();
            $table->string('phone_number', 20)->nullable();
            
            // 사용자 프로필 필드
            $table->string('name', 100)->nullable(); // Filament 호환성을 위해 추가
            $table->string('nickname', 50)->unique()->nullable();
            $table->string('display_name_preference', 20)->default('auto')->comment('표시명 설정: auto(자동), real_name(실명), nickname(닉네임), email(이메일)');
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            // 인덱스 추가
            $table->index('email');
            $table->index(['country_code', 'phone_number']);
            $table->index('nickname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};