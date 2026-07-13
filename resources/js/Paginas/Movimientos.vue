<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    movimientos: { type: Array, default: () => [] },
    stockDeposito: { type: Array, default: () => [] },
});

const etiquetaTipo = {
    venta: { texto: 'Venta', clase: 'bg-amber-100 text-amber-700' },
    reposicion: { texto: 'Reposición', clase: 'bg-emerald-100 text-emerald-700' },
    ajuste: { texto: 'Ajuste', clase: 'bg-slate-200 text-slate-700' },
    reabastecimiento: { texto: 'Reabastecimiento', clase: 'bg-sky-100 text-sky-700' },
};

const formAjuste = reactive({ productId: '', ubicacion: 'deposito', delta: '', motivo: '' });

async function registrarAjuste() {
    if (!formAjuste.productId || !formAjuste.delta) return;
    const res = await fetch(`/api/adjust/${formAjuste.productId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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

            <div v-if="stockDeposito.length === 0" class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">
                Sin stock registrado.
            </div>

            <table v-else class="w-full text-sm bg-white rounded-lg shadow-sm overflow-hidden">
                <thead class="bg-slate-100 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3 text-right">Depósito</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in stockDeposito" :key="item.productId" class="border-t border-slate-100">
                        <td class="px-4 py-3 font-mono">{{ item.productId }}</td>
                        <td class="px-4 py-3 text-right">{{ item.warehouseQuantity }}</td>
                        <td class="px-4 py-3 text-center">
                            <span v-if="item.warehouseLow" class="inline-block rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">depósito bajo</span>
                            <span v-else class="inline-block rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">ok</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
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
        </div>

        <div>
            <div class="mb-4 flex items-center justify-between">
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
        </div>
    </section>
</template>
