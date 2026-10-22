<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employment_type', 16)->nullable()->index();
            $table->string('unit_section', 40)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['employment_type']);
            $table->dropIndex(['unit_section']);
            $table->dropColumn(['employment_type', 'unit_section']);
        });
    }
};
