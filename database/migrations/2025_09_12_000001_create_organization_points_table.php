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
        Schema::create('organization_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->decimal('current_balance', 15, 2)->default(0)->comment('현재 포인트 잔액');
            $table->decimal('lifetime_earned', 15, 2)->default(0)->comment('총 획득 포인트');
            $table->decimal('lifetime_spent', 15, 2)->default(0)->comment('총 사용 포인트');
            $table->timestamps();
            
            $table->index(['organization_id']);
            $table->unique(['organization_id']); // 조직당 하나의 포인트 계정
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_points');
    }
};