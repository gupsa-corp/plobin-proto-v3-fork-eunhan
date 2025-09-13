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
        Schema::create('sandbox_cron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('schedule'); // Cron expression (e.g., "0 2 * * *")
            $table->string('type')->default('url'); // url, command, class
            $table->text('target'); // URL, command, or class name
            $table->json('options')->nullable(); // Additional options (headers, timeout, etc.)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->text('last_output')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']);
            $table->index('schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandbox_cron_jobs');
    }
};
