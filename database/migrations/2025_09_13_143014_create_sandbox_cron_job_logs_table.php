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
        Schema::create('sandbox_cron_job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cron_job_id')->constrained('sandbox_cron_jobs')->cascadeOnDelete();
            $table->enum('status', ['running', 'success', 'failed', 'skipped']);
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable(); // Execution time in milliseconds
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['cron_job_id', 'started_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandbox_cron_job_logs');
    }
};
