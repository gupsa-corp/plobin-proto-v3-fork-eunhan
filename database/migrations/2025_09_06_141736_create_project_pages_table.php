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
        Schema::create('project_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('content')->nullable();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('project_pages')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('access_level')->default('member');
            $table->json('allowed_roles')->nullable();

            // Sandbox related columns
            $table->string('sandbox_folder')->nullable();
            $table->enum('sandbox_mode', ['project', 'individual'])->default('project')->comment('샌드박스 선택 모드: project(프로젝트 따름), individual(개별 선택)');
            $table->string('sandbox_custom_screen_folder')->nullable();
            $table->string('sandbox_domain')->nullable()->comment('샌드박스 도메인명');
            $table->boolean('custom_screen_enabled')->default(false);
            $table->timestamp('custom_screen_applied_at')->nullable();
            $table->string('template_path')->nullable();

            $table->timestamps();

            $table->unique(['project_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_pages');
    }
};
