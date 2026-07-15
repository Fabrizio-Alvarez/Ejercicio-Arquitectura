<script setup>
import { ref } from 'vue';
import { apiFetch } from '../api.js';
import Card from '../components/Card.vue';
import { useFormato } from '../composables/useFormato.js';

const { dineroVO } = useFormato();

const cierre = ref({
    cajeroId: 'cajero-1',
    fecha: new Date().toISOString().slice(0, 10),
});
const resultadoCierre = ref(null);
const cierreError = ref(null);
const consultando = ref(false);

async function consultarCierre() {
    cierreError.value = null;
    resultadoCierre.value = null;
    consultando.value = true;

    try {
        const params = new URLSearchParams({
            cashierId: cierre.value.cajeroId,
            date: cierre.value.fecha,
        });
        const res = await apiFetch(`/api/cash-close?${params}`);
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'No se pudo obtener el cierre');
        }
        resultadoCierre.value = data;
    } catch (e) {
        cierreError.value = e.message;
    } finally {
        consultando.value = false;
    }
}
</script>

<template>
    <section class="space-y-8">
        <div>
            <h2 class="text-2xl font-bold mb-6">Cierre de caja</h2>
            <Card padding="p-6" class="max-w-xl">
                <form @submit.prevent="consultarCierre" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="block">
                            <span class="text-sm text-slate-600">Cajero</span>
                            <input v-model="cierre.cajeroId" class="mt-1 w-full rounded border-slate-300" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-600">Día</span>
                            <input v-model="cierre.fecha" type="date" class="mt-1 w-full rounded border-slate-300" />
                        </label>
                    </div>
                    <button :disabled="consultando" class="rounded bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900 disabled:opacity-50">
                        {{ consultando ? 'Consultando…' : 'Consultar cierre' }}
                    </button>
                    <p v-if="cierreError" class="text-sm text-red-600">{{ cierreError }}</p>
                </form>
            </Card>

            <Card v-if="resultadoCierre" padding="p-6" class="mt-6 max-w-xl space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Ventas del día</span>
                    <span>{{ resultadoCierre.count }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Total</span>
                    <span class="font-bold">{{ dineroVO(resultadoCierre.total) }}</span>
                </div>
                <hr v-if="resultadoCierre.rows.length" />
                <ul v-if="resultadoCierre.rows.length" class="text-sm space-y-1">
                    <li v-for="(row, i) in resultadoCierre.rows" :key="i" class="flex justify-between">
                        <span>{{ row.customerName }}</span>
                        <span>{{ dineroVO(row.amount) }}</span>
                    </li>
                </ul>
            </Card>
        </div>
    </section>
</template>
