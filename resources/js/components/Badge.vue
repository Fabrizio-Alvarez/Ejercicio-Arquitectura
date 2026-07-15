<script setup>
/**
 * Badge compacto con variantes de color.
 * Reemplaza `<span class="inline-block rounded px-2 py-0.5 text-xs bg-amber-100 text-amber-700">`.
 */
defineProps({
    color: {
        type: String,
        default: 'slate',
        validator: (v) => ['amber', 'red', 'emerald', 'sky', 'slate', 'purple'].includes(v),
    },
});

const colores = {
    amber:   'bg-amber-100 text-amber-800',
    red:     'bg-red-100 text-red-800',
    emerald: 'bg-emerald-100 text-emerald-800',
    sky:     'bg-sky-100 text-sky-800',
    slate:   'bg-slate-200 text-slate-800',
    purple:  'bg-purple-100 text-purple-800',
};
</script>

<template>
    <span class="inline-block rounded px-2 py-0.5 text-xs" :class="colores[color]">
        <slot />
    </span>
</template>
