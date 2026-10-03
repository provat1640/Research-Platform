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
            $table->text('research_question')->nullable();
            $table->string('methodology')->nullable();
            $table->text('expected_outcome')->nullable();
            $table->string('ethics_status')->default('not_assessed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropColumn(['research_question', 'methodology', 'expected_outcome', 'ethics_status']);
        });
    }
};
