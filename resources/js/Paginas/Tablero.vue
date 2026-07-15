<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Card from '../components/Card.vue';
import StatCard from '../components/StatCard.vue';
import Badge from '../components/Badge.vue';
import { useFormato } from '../composables/useFormato.js';
import {
  etiquetaMetodoPago,
  etiquetaUbicacion,
  etiquetaTipoMovimiento,
} from '../constants/etiquetas.js';

const props = defineProps({
  tipo: String,
  datos: Object,
});

const d = computed(() => props.datos ?? {});
const { dinero } = useFormato();

const colorTipo = {
  venta: 'amber',
  reposicion: 'emerald',
  reabastecimiento: 'sky',
  ajuste: 'slate',
  devolucion: 'purple',
};

const colorUbicacion = {
  gondola: 'amber',
  deposito: 'red',
};
</script>

<template>
  <section class="space-y-6">
    <header class="flex items-center gap-3">
      <h2 class="text-xl font-semibold text-slate-800">Tablero</h2>
    </header>

    <!-- ============================ CAJERO ============================ -->
    <template v-if="tipo === 'cajero'">
      <div class="grid gap-4 sm:grid-cols-3">
        <StatCard label="Total vendido hoy" value-class="text-emerald-600">
          {{ dinero(d.totalVentas) }} <span class="text-sm text-slate-400">{{ d.moneda }}</span>
        </StatCard>
        <StatCard label="Ventas del día" :value="d.cantidadVentas" />
        <StatCard label="Ticket promedio">
          {{ dinero(d.ticketPromedio) }} <span class="text-sm text-slate-400">{{ d.moneda }}</span>
        </StatCard>
      </div>

      <!-- Desglose por método de pago -->
      <Card v-if="Object.keys(d.desglosePorMetodo ?? {}).length > 0">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Desglose por método de pago</h3>
        <div class="space-y-2">
          <div v-for="(monto, metodo) in d.desglosePorMetodo" :key="metodo" class="flex items-center gap-3">
            <span class="w-40 text-sm text-slate-600">{{ etiquetaMetodoPago[metodo] ?? metodo }}</span>
            <div class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full bg-emerald-500"
                :style="{ width: Math.max(4, (monto / d.totalVentas) * 100) + '%' }"
              />
            </div>
            <span class="w-24 text-right text-sm font-medium text-slate-700">{{ dinero(monto) }}</span>
          </div>
        </div>
      </Card>

      <!-- Últimas ventas -->
      <Card padding="p-0">
        <div class="border-b border-slate-100 px-5 py-3">
          <h3 class="text-sm font-semibold text-slate-700">Últimas ventas</h3>
        </div>
        <table v-if="d.ultimasVentas?.length" class="w-full text-sm">
          <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
              <th class="px-5 py-2">Hora</th>
              <th class="px-5 py-2">Cliente</th>
              <th class="px-5 py-2">Método</th>
              <th class="px-5 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="vta in d.ultimasVentas" :key="vta.id" class="border-t border-slate-100">
              <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ vta.hora }}</td>
              <td class="px-5 py-3 text-slate-700">{{ vta.cliente }}</td>
              <td class="px-5 py-3">
                <Badge color="slate">{{ etiquetaMetodoPago[vta.metodo] ?? vta.metodo }}</Badge>
              </td>
              <td class="px-5 py-3 text-right font-medium text-emerald-600">{{ dinero(vta.total) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin ventas hoy todavía.</p>
      </Card>
    </template>

    <!-- ========================= DEPOSITISTA ========================= -->
    <template v-else-if="tipo === 'depositista'">
      <div class="grid gap-4 sm:grid-cols-4">
        <StatCard label="Alertas activas" :value="d.alertasActivas" value-class="text-red-600" />
        <StatCard label="De depósito" :value="d.alertasDeposito" value-class="text-red-600" />
        <StatCard label="De góndola" :value="d.alertasGondola" value-class="text-amber-600" />
        <StatCard label="Reabastecimientos hoy" :value="d.reabastecimientosHoy" value-class="text-emerald-600" />
      </div>

      <!-- Alertas recientes -->
      <Card padding="p-0">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
          <h3 class="text-sm font-semibold text-slate-700">Alertas recientes</h3>
          <Link href="/alertas" class="text-xs text-emerald-600 hover:underline">Ver todas</Link>
        </div>
        <table v-if="d.alertas?.length" class="w-full text-sm">
          <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
              <th class="px-5 py-2">Producto</th>
              <th class="px-5 py-2">Ubicación</th>
              <th class="px-5 py-2 text-right">Cantidad</th>
              <th class="px-5 py-2">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in d.alertas" :key="i" class="border-t border-slate-100">
              <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ a.productoId }}</td>
              <td class="px-5 py-3">
                <Badge :color="colorUbicacion[a.ubicacion] ?? 'slate'">
                  {{ etiquetaUbicacion[a.ubicacion]?.texto ?? a.ubicacion }}
                </Badge>
              </td>
              <td class="px-5 py-3 text-right text-slate-700">{{ a.cantidad }}</td>
              <td class="px-5 py-3 font-mono text-xs text-slate-400">{{ a.fecha }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin alertas.</p>
      </Card>

      <!-- Movimientos recientes -->
      <Card padding="p-0">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
          <h3 class="text-sm font-semibold text-slate-700">Movimientos recientes</h3>
          <Link href="/movimientos" class="text-xs text-emerald-600 hover:underline">Ver todos</Link>
        </div>
        <table v-if="d.movimientosRecientes?.length" class="w-full text-sm">
          <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
              <th class="px-5 py-2">Producto</th>
              <th class="px-5 py-2">Tipo</th>
              <th class="px-5 py-2 text-right">Cantidad</th>
              <th class="px-5 py-2">Referencia</th>
              <th class="px-5 py-2">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(m, i) in d.movimientosRecientes" :key="i" class="border-t border-slate-100">
              <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ m.productoId }}</td>
              <td class="px-5 py-3">
                <Badge :color="colorTipo[m.tipo] ?? 'slate'">
                  {{ etiquetaTipoMovimiento[m.tipo]?.texto ?? m.tipo }}
                </Badge>
              </td>
              <td class="px-5 py-3 text-right text-slate-700">{{ m.cantidad }}</td>
              <td class="px-5 py-3 text-xs text-slate-400">{{ m.referencia ?? '—' }}</td>
              <td class="px-5 py-3 font-mono text-xs text-slate-400">{{ m.fecha }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin movimientos.</p>
      </Card>
    </template>

    <!-- ========================== REPOSITOR ========================== -->
    <template v-else-if="tipo === 'repositor'">
      <div class="grid gap-4 sm:grid-cols-3">
        <StatCard label="Góndola baja" :value="d.productosGondolaBaja" :value-class="d.productosGondolaBaja > 0 ? 'text-amber-600' : 'text-slate-800'" />
        <StatCard label="Depósito bajo" :value="d.productosDepositoBajo" :value-class="d.productosDepositoBajo > 0 ? 'text-red-600' : 'text-slate-800'" />
        <StatCard label="Total productos" :value="d.totalProductos" />
      </div>

      <!-- Stock crítico -->
      <Card padding="p-0">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
          <h3 class="text-sm font-semibold text-slate-700">Stock crítico</h3>
          <Link href="/stock" class="text-xs text-emerald-600 hover:underline">Ver stock completo</Link>
        </div>
        <table v-if="d.stockCritico?.length" class="w-full text-sm">
          <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
              <th class="px-5 py-2">Producto</th>
              <th class="px-5 py-2 text-right">Góndola</th>
              <th class="px-5 py-2 text-right">Depósito</th>
              <th class="px-5 py-2">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in d.stockCritico" :key="s.productId" class="border-t border-slate-100">
              <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ s.productId }}</td>
              <td class="px-5 py-3 text-right text-slate-700">{{ s.shelfQuantity }}</td>
              <td class="px-5 py-3 text-right text-slate-700">{{ s.warehouseQuantity }}</td>
              <td class="px-5 py-3">
                <span class="flex gap-1">
                  <Badge v-if="s.shelfLow" color="amber">Góndola baja</Badge>
                  <Badge v-if="s.warehouseLow" color="red">Depósito bajo</Badge>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-emerald-600">Todo el stock está en niveles saludables ✓</p>
      </Card>
    </template>
  </section>
</template>
