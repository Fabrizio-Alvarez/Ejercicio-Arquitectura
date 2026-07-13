<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    alertas: { type: Array, default: () => [] },
});

const etiquetaUbicacion = {
    gondola: { texto: 'Góndola', clase: 'bg-amber-100 text-amber-700' },
    deposito: { texto: 'Depósito', clase: 'bg-red-100 text-red-700' },
};

const cantidades = reactive({});
const mensaje = ref(null);
const cargando = ref(null);

async function reabastecer(productoId) {
    cargando.value = productoId;
    mensaje.value = null;
    try {
        const res = await fetch(`/api/restock/${productoId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ quantity: cantidades[productoId] ?? 100 }),
        });
        if (!res.ok) throw new Error('Respuesta ' + res.status);
        const data = await res.json();
        mensaje.value = `${data.productId}: +${data.recibido} → depósito en ${data.nivelDelDeposito} unidades.`;
        cantidades[productoId] = null;
        router.reload({ only: ['alertas'] });
    } catch (e) {
        mensaje.value = 'No se pudo reabastecer: ' + e.message;
    } finally {
        cargando.value = null;
    }
}
</script>

<template>
    <section class="space-y-8">
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Alertas de stock bajo</h2>
                <span class="text-sm text-slate-500">{{ alertas.length }} registros</span>
            </div>

            <p
                v-if="mensaje"
                class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700"
            >
                {{ mensaje }}
            </p>

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
                        <th class="px-4 py-3">Reabastecer</th>
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
                        <td class="px-4 py-3">
                            <div v-if="a.ubicacion === 'deposito'" class="flex items-center gap-2">
                                <input
                                    type="number"
                                    min="1"
                                    v-model.number="cantidades[a.productoId]"
                                    placeholder="100"
                                    class="w-20 rounded border border-slate-300 px-2 py-1 text-xs"
                                />
                                <button
                                    @click="reabastecer(a.productoId)"
                                    :disabled="cargando === a.productoId"
                                    class="rounded bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    {{ cargando === a.productoId ? '…' : 'Reabastecer' }}
                                </button>
                            </div>
                            <span v-else class="text-xs text-slate-400">auto-reposición</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
