<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('objective')->nullable();
            $table->text('success_indicator')->nullable();
            $table->decimal('target', 12, 2);
            $table->string('target_unit', 50);
            $table->unsignedSmallInteger('year')->index();
            $table->date('start_date');
            $table->date('due_date');
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('responsible_user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['campus_id', 'unit_id', 'year']);
            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_programs');
    }
};
