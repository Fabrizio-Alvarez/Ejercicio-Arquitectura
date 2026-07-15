<script setup>
import { ref } from 'vue';
import { apiFetch } from '../api.js';
import { router } from '@inertiajs/vue3';
import Card from '../components/Card.vue';
import Badge from '../components/Badge.vue';
import DataTable from '../components/DataTable.vue';

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
        const res = await apiFetch(`/api/replenish/${item.productId}`, {
            method: 'POST',
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

        <Card v-if="ultimo" padding="p-4" class="text-sm">
            <strong>Reposición de {{ ultimo.productId }}:</strong>
            se movieron {{ ultimo.moved }} unidades desde el depósito.
            <span v-if="ultimo.alert" class="text-amber-700">
                ⚠ Depósito bajo ({{ ultimo.alert.cantidad }} en {{ ultimo.alert.ubicacion }}).
            </span>
        </Card>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <DataTable
            :items="items"
            :headers="['Producto', { label: 'Góndola', class: 'text-right' }, { label: 'Depósito', class: 'text-right' }, { label: 'Estado', class: 'text-center' }, { label: 'Acción', class: 'text-right' }]"
            empty="No hay stock registrado. Seed demo con php artisan migrate:fresh --seed."
        >
            <template #row="{ item }">
                <td class="px-4 py-3 font-mono">{{ item.productId }}</td>
                <td class="px-4 py-3 text-right">{{ item.shelfQuantity }}</td>
                <td class="px-4 py-3 text-right">{{ item.warehouseQuantity }}</td>
                <td class="px-4 py-3 text-center space-x-1 whitespace-nowrap">
                    <Badge v-if="item.shelfLow" color="amber">góndola baja</Badge>
                    <Badge v-if="item.warehouseLow" color="red">depósito bajo</Badge>
                    <Badge v-if="!item.shelfLow && !item.warehouseLow" color="emerald">ok</Badge>
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
            </template>
        </DataTable>
    </section>
</template>
