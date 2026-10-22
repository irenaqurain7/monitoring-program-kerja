<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['unit_id', 'name']);
        });

        Schema::table('work_programs', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('unit_id')->constrained()->nullOnDelete();
            $table->index(['team_id', 'status']);
        });

        Schema::create('work_program_progress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_program_id')->constrained()->restrictOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('actual', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['work_program_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_program_progress_updates');

        Schema::table('work_programs', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'status']);
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::dropIfExists('teams');

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['description', 'is_active']);
        });
    }
};
