<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Genera UUID para todos los usuarios que no tengan uno (solo NULL en PostgreSQL)
        DB::table('users')
            ->whereNull('uuid')
            ->orderBy('id')
            ->each(function ($user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        // No se revierten UUIDs generados
    }
};
