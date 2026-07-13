<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_de_stock', function (Blueprint $table) {
            // Registro persistente de las alertas de stock bajo emitidas por
            // el dominio (góndola < 30 al vender, depósito < 150 al reponer).
            // El spec exige persistir "incluidas las alertas de stock".
            $table->string('id')->primary();
            $table->string('producto_id');
            $table->string('ubicacion');   // UbicacionDeStock (gondola|deposito)
            $table->integer('cantidad');
            $table->dateTime('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_de_stock');
    }
};
