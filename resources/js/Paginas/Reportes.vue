<script setup>
import { computed } from 'vue';

const props = defineProps({
  ventas: { type: Object, default: () => ({}) },
  movimientos: { type: Object, default: () => ({}) },
});

const ventasPorDia = computed(() => props.ventas.ventasPorDia ?? []);
const topProductos = computed(() => props.ventas.topProductos ?? []);
const movimientosPorTipo = computed(() => props.movimientos.movimientosPorTipo ?? []);

const maxDia = computed(() => Math.max(1, ...ventasPorDia.value.map(d => d.total)));
const maxUnidades = computed(() => Math.max(1, ...topProductos.value.map(p => p.unidades)));
const maxMovUnidades = computed(() => Math.max(1, ...movimientosPorTipo.value.map(m => m.unidades)));

const etiquetaTipo = {
  venta: 'Venta',
  reposicion: 'Reposición',
  ajuste: 'Ajuste',
  reabastecimiento: 'Reabastecimiento',
};
const colorTipo = {
  venta: 'bg-amber-400',
  reposicion: 'bg-emerald-400',
  ajuste: 'bg-slate-400',
  reabastecimiento: 'bg-sky-400',
};
</script>

<template>
  <section class="space-y-8">
    <h2 class="text-2xl font-bold text-slate-800">Reportes históricos</h2>

    <!-- KPIs -->
    <div class="grid gap-4 sm:grid-cols-3">
      <div class="rounded-lg bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Total facturado</p>
        <p class="mt-1 text-2xl font-bold text-slate-800">{{ (ventas.totalGeneral ?? 0).toFixed(2) }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Ventas confirmadas</p>
        <p class="mt-1 text-2xl font-bold text-slate-800">{{ ventas.cantidadVentas ?? 0 }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Ticket promedio</p>
        <p class="mt-1 text-2xl font-bold text-slate-800">{{ (ventas.ticketPromedio ?? 0).toFixed(2) }}</p>
      </div>
    </div>

    <!-- Ventas por día -->
    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h3 class="mb-4 text-sm font-semibold text-slate-700">Ventas por día</h3>
      <div v-if="ventasPorDia.length" class="flex items-end gap-2 h-48">
        <div v-for="d in ventasPorDia" :key="d.fecha" class="flex flex-1 flex-col items-center justify-end gap-1 min-w-0">
          <span class="text-xs text-slate-500">{{ d.total.toFixed(0) }}</span>
          <div class="w-full rounded-t bg-emerald-500 transition-all" :style="{ height: (d.total / maxDia * 100) + '%' }"></div>
          <span class="text-[10px] text-slate-400 truncate w-full text-center">{{ d.fecha.slice(5) }}</span>
        </div>
      </div>
      <p v-else class="text-center text-sm text-slate-400 py-10">Sin ventas registradas.</p>
    </div>

    <!-- Top productos -->
    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h3 class="mb-4 text-sm font-semibold text-slate-700">Top productos por unidades</h3>
      <div v-if="topProductos.length" class="space-y-2">
        <div v-for="p in topProductos" :key="p.productoId" class="flex items-center gap-3">
          <span class="w-24 font-mono text-xs text-slate-600 truncate">{{ p.productoId }}</span>
          <div class="flex-1 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-6 rounded-full bg-sky-500 flex items-center justify-end pr-2" :style="{ width: (p.unidades / maxUnidades * 100) + '%' }">
              <span class="text-xs font-medium text-white">{{ p.unidades }}</span>
            </div>
          </div>
          <span class="w-20 text-right text-xs text-slate-500">{{ p.total.toFixed(2) }}</span>
        </div>
      </div>
      <p v-else class="text-center text-sm text-slate-400 py-10">Sin datos.</p>
    </div>

    <!-- Movimientos por tipo -->
    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h3 class="mb-4 text-sm font-semibold text-slate-700">Movimientos de stock por tipo</h3>
      <div v-if="movimientosPorTipo.length" class="space-y-3">
        <div v-for="m in movimientosPorTipo" :key="m.tipo" class="flex items-center gap-3">
          <span class="w-28 text-sm text-slate-600">{{ etiquetaTipo[m.tipo] ?? m.tipo }}</span>
          <div class="flex-1 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-7 rounded-full transition-all flex items-center justify-end pr-2" :class="colorTipo[m.tipo] ?? 'bg-slate-400'" :style="{ width: Math.max(8, m.unidades / maxMovUnidades * 100) + '%' }">
              <span class="text-xs font-medium text-white">{{ m.unidades }} u.</span>
            </div>
          </div>
          <span class="w-16 text-right text-xs text-slate-500">{{ m.cantidad }} reg.</span>
        </div>
      </div>
      <p v-else class="text-center text-sm text-slate-400 py-10">Sin movimientos.</p>
    </div>
  </section>
</template>
