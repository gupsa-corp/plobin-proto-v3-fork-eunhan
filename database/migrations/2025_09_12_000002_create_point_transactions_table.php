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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type', 20)->comment('earn, spend, refund, admin_adjust');
            $table->decimal('amount', 15, 2)->comment('거래 금액 (+/-)');
            $table->decimal('balance_before', 15, 2)->comment('거래 전 잔액');
            $table->decimal('balance_after', 15, 2)->comment('거래 후 잔액');
            $table->string('reason', 50)->comment('payment, bonus, refund, admin_adjustment 등');
            $table->text('description')->nullable()->comment('상세 설명');
            $table->string('reference_type')->nullable()->comment('관련 모델 타입');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('관련 모델 ID');
            $table->foreignId('processed_by')->nullable()->constrained('users')->comment('처리한 관리자');
            $table->json('metadata')->nullable()->comment('추가 메타데이터');
            $table->timestamps();
            
            $table->index(['organization_id', 'transaction_type']);
            $table->index(['organization_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['processed_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};