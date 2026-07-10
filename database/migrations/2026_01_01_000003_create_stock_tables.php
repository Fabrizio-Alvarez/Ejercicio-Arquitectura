<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gondolas', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->integer('quantity');
        });

        Schema::create('depositos', function (Blueprint $table) {
            $table->string('product_id')->primary();
            $table->integer('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gondolas');
        Schema::dropIfExists('depositos');
    }
};
