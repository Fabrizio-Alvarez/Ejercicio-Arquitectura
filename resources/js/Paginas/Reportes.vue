<script setup>
import { computed } from 'vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Filler, Tooltip, Legend } from 'chart.js';
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Filler, Tooltip, Legend);
import { Line, Bar, Doughnut } from 'vue-chartjs';
import StatCard from '../components/StatCard.vue';
import Card from '../components/Card.vue';
import { etiquetaTipoMovimiento, colorTipoMovimiento } from '../constants/etiquetas.js';

const props = defineProps({
  ventas: { type: Object, default: () => ({}) },
  movimientos: { type: Object, default: () => ({}) },
});

const ventasPorDia = computed(() => props.ventas?.ventasPorDia ?? []);
const topProductos = computed(() => props.ventas?.topProductos ?? []);
const movimientosPorTipo = computed(() => props.movimientos?.movimientosPorTipo ?? []);

// Formatea una fecha ISO (YYYY-MM-DD o con tiempo) como dd/mm.
function fechaCorta(fecha) {
  if (!fecha) return '';
  const parte = String(fecha).split('T')[0].split('-');
  if (parte.length < 3) return fecha;
  const [_, mes, dia] = parte;
  return `${dia}/${mes}`;
}

// ---- Chart 1: Ventas por día (Line con relleno en gradiente esmeralda) ----
const ventasChartData = computed(() => ({
  labels: ventasPorDia.value.map(d => fechaCorta(d.fecha)),
  datasets: [
    {
      label: 'Total',
      data: ventasPorDia.value.map(d => d.total),
      borderColor: '#10b981',
      borderWidth: 2,
      tension: 0.35,
      fill: true,
      pointRadius: 3,
      pointBackgroundColor: '#10b981',
      backgroundColor: (context) => {
        const chart = context?.chart;
        const { ctx, chartArea } = chart ?? {};
        if (!ctx || !chartArea) return 'rgba(16,185,129,0.3)';
        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        gradient.addColorStop(0, 'rgba(16,185,129,0.3)');
        gradient.addColorStop(1, 'rgba(16,185,129,0)');
        return gradient;
      },
    },
  ],
}));

const ventasChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
  scales: {
    y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.15)' } },
    x: { grid: { display: false } },
  },
  elements: { line: { borderJoinStyle: 'round' } },
};

// ---- Chart 2: Top productos (Bar horizontal, sky blue) ----
const productosChartData = computed(() => ({
  labels: topProductos.value.map(p => p.productoId),
  datasets: [
    {
      label: 'Unidades',
      data: topProductos.value.map(p => p.unidades),
      backgroundColor: '#0ea5e9',
      borderRadius: 6,
      barThickness: 18,
    },
  ],
}));

const productosChartOptions = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.15)' } },
    y: { grid: { display: false } },
  },
};

// ---- Chart 3: Movimientos por tipo (Doughnut) ----
const PALETA_DOUGHNUT = ['#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#6366f1', '#ec4899', '#14b8a6'];

const movimientosChartData = computed(() => ({
  labels: movimientosPorTipo.value.map(m =>
    etiquetaTipoMovimiento[m.tipo]?.texto ?? m.tipo
  ),
  datasets: [
    {
      data: movimientosPorTipo.value.map(m => m.unidades),
      backgroundColor: movimientosPorTipo.value.map((_, i) => PALETA_DOUGHNUT[i % PALETA_DOUGHNUT.length]),
      borderWidth: 2,
      borderColor: '#ffffff',
    },
  ],
}));

const movimientosChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: true, position: 'bottom' } },
  cutout: '60%',
};
</script>

<template>
  <section class="space-y-8">
    <h2 class="text-2xl font-bold text-slate-800">Reportes históricos</h2>

    <!-- KPIs -->
    <div class="grid gap-4 sm:grid-cols-3">
      <StatCard label="Total facturado" :value="(ventas?.totalGeneral ?? 0).toFixed(2)" />
      <StatCard label="Ventas confirmadas" :value="ventas?.cantidadVentas ?? 0" />
      <StatCard label="Ticket promedio" :value="(ventas?.ticketPromedio ?? 0).toFixed(2)" />
    </div>

    <!-- Ventas por día -->
    <Card>
      <h3 class="mb-4 text-sm font-semibold text-slate-700">Ventas por día</h3>
      <div v-if="ventasPorDia.length" class="h-72">
        <Line :data="ventasChartData" :options="ventasChartOptions" />
      </div>
      <p v-else class="text-center text-sm text-slate-400 py-10">Sin datos</p>
    </Card>

    <!-- Top productos + Movimientos por tipo -->
    <div class="grid gap-6 lg:grid-cols-2">
      <Card>
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Top productos por unidades</h3>
        <div v-if="topProductos.length" class="h-72">
          <Bar :data="productosChartData" :options="productosChartOptions" />
        </div>
        <p v-else class="text-center text-sm text-slate-400 py-10">Sin datos</p>
      </Card>

      <Card>
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Movimientos de stock por tipo</h3>
        <div v-if="movimientosPorTipo.length" class="h-72">
          <Doughnut :data="movimientosChartData" :options="movimientosChartOptions" />
        </div>
        <p v-else class="text-center text-sm text-slate-400 py-10">Sin datos</p>
      </Card>
    </div>
  </section>
</template>
