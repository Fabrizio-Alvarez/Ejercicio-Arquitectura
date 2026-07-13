<script setup>
import { ref } from 'vue';

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
        const res = await fetch(`/api/cash-close?${params}`, {
            headers: { Accept: 'application/json' },
        });
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

function formato(dinero) {
    return dinero ? `${(dinero.amount / 100).toFixed(2)} ${dinero.currency}` : '—';
}
</script>

<template>
    <section class="space-y-8">
        <div>
            <h2 class="text-2xl font-bold mb-6">Cierre de caja</h2>
            <form @submit.prevent="consultarCierre" class="space-y-4 bg-white p-6 rounded-lg shadow-sm max-w-xl">
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

            <div v-if="resultadoCierre" class="mt-6 bg-white p-6 rounded-lg shadow-sm space-y-3 max-w-xl">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Ventas del día</span>
                    <span>{{ resultadoCierre.count }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Total</span>
                    <span class="font-bold">{{ formato(resultadoCierre.total) }}</span>
                </div>
                <hr v-if="resultadoCierre.rows.length" />
                <ul v-if="resultadoCierre.rows.length" class="text-sm space-y-1">
                    <li v-for="(row, i) in resultadoCierre.rows" :key="i" class="flex justify-between">
                        <span>{{ row.customerName }}</span>
                        <span>{{ formato(row.amount) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
