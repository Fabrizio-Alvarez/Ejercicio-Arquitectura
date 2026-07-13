<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de log de auditoría (event sourcing ligero): cada evento de dominio
 * que se despacha se persiste aquí con su tipo y payload serializado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_de_dominio', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_de_dominio');
    }
};
