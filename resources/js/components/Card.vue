<script setup>
/**
 * Tarjeta contenedora reutilizable.
 * Reemplaza el patrón repetido `<div class="rounded-lg bg-white p-5 shadow-sm">`.
 */
defineProps({
    padding: { type: String, default: 'p-5' },
});
</script>

<template>
    <div class="rounded-lg bg-white shadow-sm" :class="padding">
        <slot />
    </div>
</template>
