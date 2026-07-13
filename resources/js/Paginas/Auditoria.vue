<script setup>
import { computed } from 'vue';

const props = defineProps({
  eventos: { type: Array, default: () => [] },
});

const etiquetaTipo = {
  CompraRealizada: { texto: 'Compra', clase: 'bg-amber-100 text-amber-700' },
  AlertaDeStock: { texto: 'Alerta', clase: 'bg-red-100 text-red-700' },
};

function detalle(e) {
  if (!e.payload) return '';
  if (e.tipo === 'CompraRealizada') {
    const n = (e.payload.lineas ?? []).length;
    return `${e.payload.metodoDePago ?? ''} · ${n} línea(s) · ${e.payload.ventaId ?? ''}`;
  }
  if (e.tipo === 'AlertaDeStock') {
    return `${e.payload.productoId ?? ''} · ${e.payload.ubicacion ?? ''} · ${e.payload.cantidad ?? 0} u.`;
  }
  return JSON.stringify(e.payload);
}
</script>

<template>
  <section class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold text-slate-800">Log de auditoría</h2>
      <span class="text-sm text-slate-500">{{ eventos.length }} eventos</span>
    </div>

    <div v-if="eventos.length === 0" class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">
      Sin eventos registrados. Las compras y alertas aparecerán aquí automáticamente.
    </div>

    <div v-else class="overflow-hidden rounded-lg bg-white shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
          <tr>
            <th class="px-4 py-2">Cuándo</th>
            <th class="px-4 py-2">Tipo</th>
            <th class="px-4 py-2">Detalle</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in eventos" :key="e.id" class="border-t border-slate-100">
            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ e.occurredAt }}</td>
            <td class="px-4 py-3">
              <span class="inline-block rounded px-2 py-0.5 text-xs" :class="etiquetaTipo[e.tipo]?.clase ?? 'bg-slate-100 text-slate-600'">
                {{ etiquetaTipo[e.tipo]?.texto ?? e.tipo }}
              </span>
            </td>
            <td class="px-4 py-3 text-slate-600">{{ detalle(e) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
