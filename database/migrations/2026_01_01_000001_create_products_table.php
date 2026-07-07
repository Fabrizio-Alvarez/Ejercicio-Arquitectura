<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Domain uses string ids (UUIDs), so the PK is a non-incrementing string.
            $table->string('id')->primary();
            $table->string('name');
            $table->bigInteger('price_amount'); // integer cents, no floats
            $table->char('price_currency', 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
