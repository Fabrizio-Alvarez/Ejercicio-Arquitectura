<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gondolas', function (Blueprint $table) {
            $table->integer('umbral_bajo')->default(30)->after('quantity');
        });

        Schema::table('depositos', function (Blueprint $table) {
            $table->integer('umbral_bajo')->default(150)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('gondolas', function (Blueprint $table) {
            $table->dropColumn('umbral_bajo');
        });

        Schema::table('depositos', function (Blueprint $table) {
            $table->dropColumn('umbral_bajo');
        });
    }
};
