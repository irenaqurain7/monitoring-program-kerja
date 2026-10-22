<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable();
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                $emailName = explode('@', (string) $user->email, 2)[0];
                $base = strtolower((string) preg_replace('/[^a-zA-Z0-9._-]/', '', $emailName));
                $base = trim($base, '._-');
                $base = substr($base !== '' ? $base : 'pengguna', 0, 50);
                $username = $base;
                $suffix = 1;

                while (DB::table('users')->where('username', $username)->exists()) {
                    $ending = '-'.$suffix++;
                    $username = substr($base, 0, 50 - strlen($ending)).$ending;
                }

                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
