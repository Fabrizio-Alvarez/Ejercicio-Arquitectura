<script setup>
import Card from './Card.vue';

/**
 * Tarjeta de estadística con etiqueta + valor grande.
 * Reemplaza el patrón repetido en Tablero y Reportes.
 *
 * El slot default permite HTML arbitrario (colores, sufijos de moneda);
 * si se omite, se usa la prop `value`.
 */
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], default: null },
    valueClass: { type: String, default: 'text-slate-800' },
});
</script>

<template>
    <Card padding="p-5">
        <p class="text-sm text-slate-500">{{ label }}</p>
        <p class="mt-1 text-2xl font-bold" :class="valueClass">
            <slot>{{ value }}</slot>
        </p>
    </Card>
</template>
