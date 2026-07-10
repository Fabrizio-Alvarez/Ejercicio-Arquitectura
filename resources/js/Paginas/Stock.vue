<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    items: { type: Array, default: () => [] },
});

const reponiendo = ref(null);
const ultimo = ref(null);
const error = ref(null);

async function reponer(item) {
    error.value = null;
    reponiendo.value = item.productId;
    try {
        const res = await fetch(`/api/replenish/${item.productId}`, {
            method: 'POST',
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'No se pudo reponer');
        }
        ultimo.value = data;
        router.reload({ only: ['items'] });
    } catch (e) {
        error.value = e.message;
    } finally {
        reponiendo.value = null;
    }
}
</script>

<template>
    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">Stock por producto</h2>
            <span class="text-sm text-slate-500">{{ items.length }} productos</span>
        </div>

        <div v-if="ultimo" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm">
            <strong>Reposición de {{ ultimo.productId }}:</strong>
            se movieron {{ ultimo.moved }} unidades desde el depósito.
            <span v-if="ultimo.alert" class="text-amber-700">
                ⚠ Depósito bajo ({{ ultimo.alert.cantidad }} en {{ ultimo.alert.ubicacion }}).
            </span>
        </div>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <div v-if="items.length === 0" class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">
            No hay stock registrado. Seed demo con <code>php artisan migrate:fresh --seed</code>.
        </div>

        <table v-else class="w-full text-sm bg-white rounded-lg shadow-sm overflow-hidden">
            <thead class="bg-slate-100 text-slate-600 text-left">
                <tr>
                    <th class="px-4 py-3">Producto</th>
                    <th class="px-4 py-3 text-right">Góndola</th>
                    <th class="px-4 py-3 text-right">Depósito</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.productId" class="border-t border-slate-100">
                    <td class="px-4 py-3 font-mono">{{ item.productId }}</td>
                    <td class="px-4 py-3 text-right">{{ item.shelfQuantity }}</td>
                    <td class="px-4 py-3 text-right">{{ item.warehouseQuantity }}</td>
                    <td class="px-4 py-3 text-center space-x-1 whitespace-nowrap">
                        <span v-if="item.shelfLow" class="inline-block rounded bg-amber-100 text-amber-700 px-2 py-0.5 text-xs">góndola baja</span>
                        <span v-if="item.warehouseLow" class="inline-block rounded bg-red-100 text-red-700 px-2 py-0.5 text-xs">depósito bajo</span>
                        <span v-if="!item.shelfLow && !item.warehouseLow" class="inline-block rounded bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">ok</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button
                            v-if="item.shelfLow"
                            type="button"
                            :disabled="reponiendo === item.productId"
                            @click="reponer(item)"
                            class="rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ reponiendo === item.productId ? '…' : 'Reponer' }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
