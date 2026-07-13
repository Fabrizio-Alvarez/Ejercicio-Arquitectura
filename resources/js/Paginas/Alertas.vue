<script setup>
defineProps({
    alertas: { type: Array, default: () => [] },
});

const etiquetaUbicacion = {
    gondola: { texto: 'Góndola', clase: 'bg-amber-100 text-amber-700' },
    deposito: { texto: 'Depósito', clase: 'bg-red-100 text-red-700' },
};
</script>

<template>
    <section class="space-y-8">
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Alertas de stock bajo</h2>
                <span class="text-sm text-slate-500">{{ alertas.length }} registros</span>
            </div>

            <div v-if="alertas.length === 0" class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">
                Sin alertas registradas. Se emiten cuando la góndola cae bajo 30 (al vender) o el depósito bajo 150 (al reponer).
            </div>

            <table v-else class="w-full text-sm bg-white rounded-lg shadow-sm overflow-hidden">
                <thead class="bg-slate-100 text-slate-600 text-left">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Ubicación</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3 text-right">Stock restante</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in alertas" :key="a.productoId + a.fecha" class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ a.fecha }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded px-2 py-0.5 text-xs" :class="etiquetaUbicacion[a.ubicacion]?.clase">
                                {{ etiquetaUbicacion[a.ubicacion]?.texto ?? a.ubicacion }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono">{{ a.productoId }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-700">{{ a.cantidad }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
