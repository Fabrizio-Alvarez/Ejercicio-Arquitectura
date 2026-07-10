<script setup>
defineProps({
    movimientos: { type: Array, default: () => [] },
});

const etiquetaTipo = {
    venta: { texto: 'Venta', clase: 'bg-amber-100 text-amber-700' },
    reposicion: { texto: 'Reposición', clase: 'bg-emerald-100 text-emerald-700' },
    ajuste: { texto: 'Ajuste', clase: 'bg-slate-200 text-slate-700' },
};
</script>

<template>
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Movimientos del depósito</h2>
            <span class="text-sm text-slate-500">{{ movimientos.length }} registros</span>
        </div>

        <div v-if="movimientos.length === 0" class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">
            Sin movimientos todavía. Registra una venta en <strong>Cobrar</strong> para ver la auditoría del depósito en acción.
        </div>

        <table v-else class="w-full text-sm bg-white rounded-lg shadow-sm overflow-hidden">
            <thead class="bg-slate-100 text-slate-600 text-left">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Producto</th>
                    <th class="px-4 py-3">Ubicación</th>
                    <th class="px-4 py-3 text-right">Cantidad</th>
                    <th class="px-4 py-3">Referencia</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="m in movimientos" :key="m.productoId + m.fecha" class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ m.fecha }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-block rounded px-2 py-0.5 text-xs" :class="etiquetaTipo[m.tipo]?.clase">
                            {{ etiquetaTipo[m.tipo]?.texto ?? m.tipo }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-mono">{{ m.productoId }}</td>
                    <td class="px-4 py-3 capitalize">{{ m.ubicacion }}</td>
                    <td class="px-4 py-3 text-right">{{ m.cantidad }}</td>
                    <td class="px-4 py-3 font-mono text-slate-400">{{ m.referencia ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
