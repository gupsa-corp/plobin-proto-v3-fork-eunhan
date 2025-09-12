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
        Schema::table('project_sandboxes', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['project_id']);
            
            // Drop unique constraint that includes project_id
            $table->dropUnique(['project_id', 'name']);
            
            // Modify project_id to be nullable
            $table->unsignedBigInteger('project_id')->nullable()->change();
            
            // Add back the foreign key constraint allowing null values
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            
            // Add template_id column
            $table->string('template_id')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_sandboxes', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['project_id']);
            
            // Remove template_id column
            $table->dropColumn('template_id');
            
            // Make project_id NOT NULL again
            $table->unsignedBigInteger('project_id')->nullable(false)->change();
            
            // Add back the original foreign key and unique constraints
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unique(['project_id', 'name']);
        });
    }
};
