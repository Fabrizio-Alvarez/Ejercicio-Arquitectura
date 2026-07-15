<script setup>
import { etiquetaEvento } from '../constants/etiquetas.js';
import Badge from '../components/Badge.vue';
import DataTable from '../components/DataTable.vue';

const props = defineProps({
  eventos: { type: Array, default: () => [] },
});

const colorEvento = {
  CompraRealizada: 'amber',
  AlertaDeStock: 'red',
  DevolucionRegistrada: 'purple',
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

    <DataTable
      :headers="['Cuándo', 'Tipo', 'Detalle']"
      :items="eventos"
      empty="Sin eventos registrados. Las compras y alertas aparecerán aquí automáticamente."
    >
      <template #row="{ item: e }">
        <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ e.occurredAt }}</td>
        <td class="px-4 py-3">
          <Badge :color="colorEvento[e.tipo] ?? 'slate'">
            {{ etiquetaEvento[e.tipo]?.texto ?? e.tipo }}
          </Badge>
        </td>
        <td class="px-4 py-3 text-slate-600">{{ detalle(e) }}</td>
      </template>
    </DataTable>
  </section>
</template>
