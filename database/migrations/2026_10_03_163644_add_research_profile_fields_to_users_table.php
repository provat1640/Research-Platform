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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'orcid_id')) {
                $table->string('orcid_id')->nullable()->unique();
            }

            if (! Schema::hasColumn('users', 'is_teacher')) {
                $table->boolean('is_teacher')->default(false);
            }

            if (! Schema::hasColumn('users', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'orcid_id')) {
                $table->dropUnique(['orcid_id']);
                $table->dropColumn('orcid_id');
            }

            if (Schema::hasColumn('users', 'is_teacher')) {
                $table->dropColumn('is_teacher');
            }

            if (Schema::hasColumn('users', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
        });
    }
};
