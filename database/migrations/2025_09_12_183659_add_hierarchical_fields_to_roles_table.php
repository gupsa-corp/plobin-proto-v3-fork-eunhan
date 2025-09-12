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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('scope_level')->nullable()->after('guard_name');
            $table->unsignedBigInteger('organization_id')->nullable()->after('scope_level');
            $table->unsignedBigInteger('project_id')->nullable()->after('organization_id');
            $table->unsignedBigInteger('page_id')->nullable()->after('project_id');
            $table->unsignedBigInteger('parent_role_id')->nullable()->after('page_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('parent_role_id');
            $table->boolean('is_active')->default(true)->after('created_by');

            // Foreign key constraints
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('project_pages')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('project_pages')->onDelete('cascade');
            $table->foreign('parent_role_id')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['parent_role_id']);
            $table->dropForeign(['page_id']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['organization_id']);
            
            $table->dropColumn(['scope_level', 'organization_id', 'project_id', 'page_id', 'parent_role_id', 'created_by', 'is_active']);
        });
    }
};
