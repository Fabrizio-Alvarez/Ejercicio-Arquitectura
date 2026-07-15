<script setup>
/**
 * Tabla de datos con encabezados tipados.
 * Reemplaza el patrón repetido en Stock, Movimientos, Catalogo y Auditoria.
 */
defineProps({
    headers: { type: Array, default: () => [] },
    items:   { type: Array, default: () => [] },
    empty:   { type: String, default: 'Sin datos.' },
});
</script>

<template>
    <div v-if="items.length === 0" class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">
        {{ empty }}
    </div>

    <table v-else class="w-full text-sm bg-white rounded-lg shadow-sm overflow-hidden">
        <thead class="bg-slate-100 text-slate-600 text-left">
            <tr>
                <th
                    v-for="(h, i) in headers"
                    :key="i"
                    class="px-4 py-3 whitespace-nowrap"
                    :class="typeof h === 'object' ? h.class : ''"
                >
                    {{ typeof h === 'object' ? h.label : h }}
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in items" :key="item.id ?? item.productId ?? index" class="border-t border-slate-100">
                <slot name="row" :item="item" />
            </tr>
        </tbody>
    </table>
</template>
