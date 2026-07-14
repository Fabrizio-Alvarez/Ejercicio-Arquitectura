<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gondolas', function (Blueprint $table): void {
            $table->integer('reservado')->default(0)->after('umbral_bajo');
        });
    }

    public function down(): void
    {
        Schema::table('gondolas', function (Blueprint $table): void {
            $table->dropColumn('reservado');
        });
    }
};
