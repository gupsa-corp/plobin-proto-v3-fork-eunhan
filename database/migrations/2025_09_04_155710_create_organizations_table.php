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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 25); // 조직명 최대 25자
            $table->string('url', 50)->nullable(); // 조직 URL
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->integer('members_count')->default(1);
            $table->decimal('points_balance', 15, 2)->default(0)->comment('포인트 잔액 (성능 최적화용)');
            $table->unsignedBigInteger('user_id'); // 조직 생성자 ID
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
