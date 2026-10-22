<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_program_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->decimal('target', 12, 2)->default(0);
            $table->decimal('actual', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('not_started');
            $table->timestamps();
            $table->unique(['work_program_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_program_monthly_targets');
    }
};
