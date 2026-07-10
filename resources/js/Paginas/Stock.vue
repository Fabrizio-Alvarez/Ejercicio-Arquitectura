<script setup>
defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Stock por producto</h2>
            <span class="text-sm text-slate-500">{{ items.length }} productos</span>
        </div>

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
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.productId" class="border-t border-slate-100">
                    <td class="px-4 py-3 font-mono">{{ item.productId }}</td>
                    <td class="px-4 py-3 text-right">{{ item.shelfQuantity }}</td>
                    <td class="px-4 py-3 text-right">{{ item.warehouseQuantity }}</td>
                    <td class="px-4 py-3 text-center space-x-1">
                        <span v-if="item.shelfLow" class="inline-block rounded bg-amber-100 text-amber-700 px-2 py-0.5 text-xs">góndola baja</span>
                        <span v-if="item.warehouseLow" class="inline-block rounded bg-red-100 text-red-700 px-2 py-0.5 text-xs">depósito bajo</span>
                        <span v-if="!item.shelfLow && !item.warehouseLow" class="inline-block rounded bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">ok</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
