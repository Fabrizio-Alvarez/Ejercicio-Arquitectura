<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            // Per the spec, offers are loaded by an external system directly
            // into the data source — the application only reads them.
            $table->id();
            $table->string('product_id');
            $table->decimal('percent', 5, 2);
            $table->dateTime('valid_from');
            $table->dateTime('valid_to');
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
