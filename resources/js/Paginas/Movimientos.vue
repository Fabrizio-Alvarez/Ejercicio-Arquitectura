<script setup>
import { reactive } from 'vue';
import { apiFetch } from '../api.js';
import { router } from '@inertiajs/vue3';
import Card from '../components/Card.vue';
import Badge from '../components/Badge.vue';
import DataTable from '../components/DataTable.vue';
import { etiquetaTipoMovimiento } from '../constants/etiquetas.js';

defineProps({
    movimientos: { type: Array, default: () => [] },
    stockDeposito: { type: Array, default: () => [] },
});

const colorTipo = {
    venta: 'amber',
    reposicion: 'emerald',
    ajuste: 'slate',
    reabastecimiento: 'sky',
};

const formAjuste = reactive({ productId: '', ubicacion: 'deposito', delta: '', motivo: '' });

async function registrarAjuste() {
    if (!formAjuste.productId || !formAjuste.delta) return;
    const res = await apiFetch(`/api/adjust/${formAjuste.productId}`, {
        method: 'POST',
        body: JSON.stringify({ ubicacion: formAjuste.ubicacion, delta: parseInt(formAjuste.delta), motivo: formAjuste.motivo || null }),
    });
    if (res.ok) {
        formAjuste.productId = ''; formAjuste.ubicacion = 'deposito'; formAjuste.delta = ''; formAjuste.motivo = '';
        router.reload({ only: ['movimientos', 'stockDeposito'] });
    }
}
</script>

<template>
    <section class="space-y-8">
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Stock del depósito</h2>
                <span class="text-sm text-slate-500">{{ stockDeposito.length }} productos</span>
            </div>

            <DataTable
                :headers="['Producto', { label: 'Depósito', class: 'text-right' }, { label: 'Estado', class: 'text-center' }]"
                :items="stockDeposito"
                empty="Sin stock registrado."
            >
                <template #row="{ item }">
                    <td class="px-4 py-3 font-mono">{{ item.productId }}</td>
                    <td class="px-4 py-3 text-right">{{ item.warehouseQuantity }}</td>
                    <td class="px-4 py-3 text-center">
                        <Badge :color="item.warehouseLow ? 'red' : 'emerald'">
                            {{ item.warehouseLow ? 'depósito bajo' : 'ok' }}
                        </Badge>
                    </td>
                </template>
            </DataTable>
        </div>

        <Card>
            <h2 class="mb-4 text-lg font-bold">Nuevo ajuste de stock</h2>
            <form @submit.prevent="registrarAjuste" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Producto</label>
                    <select v-model="formAjuste.productId" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="" disabled>Producto…</option>
                        <option v-for="item in stockDeposito" :key="item.productId" :value="item.productId">{{ item.productId }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Ubicación</label>
                    <select v-model="formAjuste.ubicacion" class="rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="deposito">Depósito</option>
                        <option value="gondola">Góndola</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Delta (+/−)</label>
                    <input v-model="formAjuste.delta" type="number" class="w-24 rounded border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Motivo</label>
                    <input v-model="formAjuste.motivo" placeholder="Conteo, merma, rotura…" class="w-full rounded border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <button type="submit" class="rounded bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Ajustar</button>
            </form>
        </Card>

        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Movimientos del depósito</h2>
                <span class="text-sm text-slate-500">{{ movimientos.length }} registros</span>
            </div>

            <DataTable
                :headers="['Fecha', 'Tipo', 'Producto', 'Ubicación', { label: 'Cantidad', class: 'text-right' }, 'Referencia']"
                :items="movimientos"
                empty="Sin movimientos todavía. Registra una venta en Cobrar para ver la auditoría del depósito en acción."
            >
                <template #row="{ item }">
                    <td class="px-4 py-3">{{ item.fecha }}</td>
                    <td class="px-4 py-3">
                        <Badge :color="colorTipo[item.tipo] ?? 'slate'">
                            {{ etiquetaTipoMovimiento[item.tipo]?.texto ?? item.tipo }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3 font-mono">{{ item.productoId }}</td>
                    <td class="px-4 py-3 capitalize">{{ item.ubicacion }}</td>
                    <td class="px-4 py-3 text-right">{{ item.cantidad }}</td>
                    <td class="px-4 py-3 font-mono text-slate-400">{{ item.referencia ?? '—' }}</td>
                </template>
            </DataTable>
        </div>
    </section>
</template>
