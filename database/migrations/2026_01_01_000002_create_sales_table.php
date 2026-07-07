<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('cashier_id');
            $table->string('customer_name');
            $table->string('status');
            $table->string('sold_at'); // ISO-8601 timestamp of the domain createdAt
            $table->timestamps();
        });

        Schema::create('sale_lines', function (Blueprint $table) {
            $table->id();
            $table->string('sale_id');
            $table->string('product_id');
            $table->string('product_name');
            $table->integer('quantity');
            $table->bigInteger('unit_price_amount');
            $table->char('unit_price_currency', 3);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_lines');
        Schema::dropIfExists('sales');
    }
};
