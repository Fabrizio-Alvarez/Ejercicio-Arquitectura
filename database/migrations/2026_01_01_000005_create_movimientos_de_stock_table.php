<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_de_stock', function (Blueprint $table) {
            // Auditoría del depósito: cada movimiento de stock (venta, reposición, ajuste).
            $table->string('id')->primary();
            $table->string('producto_id');
            $table->string('tipo');        // TipoDeMovimiento (venta|reposicion|ajuste)
            $table->integer('cantidad');
            $table->string('ubicacion');   // UbicacionDeStock (gondola|deposito)
            $table->string('referencia')->nullable(); // p.ej. el id de la venta que originó el movimiento
            $table->dateTime('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_de_stock');
    }
};
