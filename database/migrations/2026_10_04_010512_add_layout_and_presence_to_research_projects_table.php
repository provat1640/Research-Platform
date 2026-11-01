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
        Schema::table('research_projects', function (Blueprint $table) {
            $table->string('paper_layout')->default('single_column');
            $table->string('paper_margin')->default('normal');
            $table->string('paper_size')->default('a4');
            $table->string('font_family')->default('serif');
            $table->string('line_spacing')->default('1.5');
            $table->string('active_theme')->default('clean_white');
            $table->foreignId('active_editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('active_editor_section')->nullable();
            $table->timestamp('active_editor_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropForeign(['active_editor_id']);
            $table->dropColumn([
                'paper_layout',
                'paper_margin',
                'paper_size',
                'font_family',
                'line_spacing',
                'active_theme',
                'active_editor_id',
                'active_editor_section',
                'active_editor_at',
            ]);
        });
    }
};
