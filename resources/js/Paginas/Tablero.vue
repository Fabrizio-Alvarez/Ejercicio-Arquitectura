<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  tipo: String,
  datos: Object,
});

const d = computed(() => props.datos ?? {});

function dinero(centavos) {
  return (centavos / 100).toFixed(2);
}

const etiquetaMetodo = {
  efectivo: 'Efectivo',
  tarjeta_credito: 'Tarjeta de crédito',
  tarjeta_debito: 'Tarjeta de débito',
  transferencia: 'Transferencia',
  qr: 'QR',
};

const etiquetaUbicacion = {
  gondola: { texto: 'Góndola', clase: 'bg-amber-100 text-amber-800' },
  deposito: { texto: 'Depósito', clase: 'bg-red-100 text-red-800' },
};

const etiquetaTipo = {
  venta: { texto: 'Venta', clase: 'bg-red-100 text-red-800' },
  reposicion: { texto: 'Reposición', clase: 'bg-amber-100 text-amber-800' },
  reabastecimiento: { texto: 'Reabastecimiento', clase: 'bg-emerald-100 text-emerald-800' },
  ajuste: { texto: 'Ajuste', clase: 'bg-slate-100 text-slate-800' },
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
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Total vendido hoy</p>
          <p class="mt-1 text-2xl font-bold text-emerald-600">
            {{ dinero(d.totalVentas) }} <span class="text-sm text-slate-400">{{ d.moneda }}</span>
          </p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Ventas del día</p>
          <p class="mt-1 text-2xl font-bold text-slate-800">{{ d.cantidadVentas }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Ticket promedio</p>
          <p class="mt-1 text-2xl font-bold text-slate-800">
            {{ dinero(d.ticketPromedio) }} <span class="text-sm text-slate-400">{{ d.moneda }}</span>
          </p>
        </div>
      </div>

      <!-- Desglose por método de pago -->
      <div v-if="Object.keys(d.desglosePorMetodo ?? {}).length > 0" class="rounded-lg bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Desglose por método de pago</h3>
        <div class="space-y-2">
          <div v-for="(monto, metodo) in d.desglosePorMetodo" :key="metodo" class="flex items-center gap-3">
            <span class="w-40 text-sm text-slate-600">{{ etiquetaMetodo[metodo] ?? metodo }}</span>
            <div class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full bg-emerald-500"
                :style="{ width: Math.max(4, (monto / d.totalVentas) * 100) + '%' }"
              />
            </div>
            <span class="w-24 text-right text-sm font-medium text-slate-700">{{ dinero(monto) }}</span>
          </div>
        </div>
      </div>

      <!-- Últimas ventas -->
      <div class="overflow-hidden rounded-lg bg-white shadow-sm">
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
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">
                  {{ etiquetaMetodo[vta.metodo] ?? vta.metodo }}
                </span>
              </td>
              <td class="px-5 py-3 text-right font-medium text-emerald-600">{{ dinero(vta.total) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin ventas hoy todavía.</p>
      </div>
    </template>

    <!-- ========================= DEPOSITISTA ========================= -->
    <template v-else-if="tipo === 'depositista'">
      <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Alertas activas</p>
          <p class="mt-1 text-2xl font-bold text-red-600">{{ d.alertasActivas }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">De depósito</p>
          <p class="mt-1 text-2xl font-bold text-red-600">{{ d.alertasDeposito }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">De góndola</p>
          <p class="mt-1 text-2xl font-bold text-amber-600">{{ d.alertasGondola }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Reabastecimientos hoy</p>
          <p class="mt-1 text-2xl font-bold text-emerald-600">{{ d.reabastecimientosHoy }}</p>
        </div>
      </div>

      <!-- Alertas recientes -->
      <div class="overflow-hidden rounded-lg bg-white shadow-sm">
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
                <span :class="['rounded-full px-2 py-0.5 text-xs', etiquetaUbicacion[a.ubicacion]?.clase]">
                  {{ etiquetaUbicacion[a.ubicacion]?.texto ?? a.ubicacion }}
                </span>
              </td>
              <td class="px-5 py-3 text-right text-slate-700">{{ a.cantidad }}</td>
              <td class="px-5 py-3 font-mono text-xs text-slate-400">{{ a.fecha }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin alertas.</p>
      </div>

      <!-- Movimientos recientes -->
      <div class="overflow-hidden rounded-lg bg-white shadow-sm">
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
                <span :class="['rounded-full px-2 py-0.5 text-xs', etiquetaTipo[m.tipo]?.clase]">
                  {{ etiquetaTipo[m.tipo]?.texto ?? m.tipo }}
                </span>
              </td>
              <td class="px-5 py-3 text-right text-slate-700">{{ m.cantidad }}</td>
              <td class="px-5 py-3 text-xs text-slate-400">{{ m.referencia ?? '—' }}</td>
              <td class="px-5 py-3 font-mono text-xs text-slate-400">{{ m.fecha }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin movimientos.</p>
      </div>
    </template>

    <!-- ========================== REPOSITOR ========================== -->
    <template v-else-if="tipo === 'repositor'">
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Góndola baja</p>
          <p class="mt-1 text-2xl font-bold" :class="d.productosGondolaBaja > 0 ? 'text-amber-600' : 'text-slate-800'">
            {{ d.productosGondolaBaja }}
          </p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Depósito bajo</p>
          <p class="mt-1 text-2xl font-bold" :class="d.productosDepositoBajo > 0 ? 'text-red-600' : 'text-slate-800'">
            {{ d.productosDepositoBajo }}
          </p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Total productos</p>
          <p class="mt-1 text-2xl font-bold text-slate-800">{{ d.totalProductos }}</p>
        </div>
      </div>

      <!-- Stock crítico -->
      <div class="overflow-hidden rounded-lg bg-white shadow-sm">
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
                  <span v-if="s.shelfLow" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">Góndola baja</span>
                  <span v-if="s.warehouseLow" class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-800">Depósito bajo</span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-else class="px-5 py-8 text-center text-sm text-emerald-600">Todo el stock está en niveles saludables ✓</p>
      </div>
    </template>
  </section>
</template>
