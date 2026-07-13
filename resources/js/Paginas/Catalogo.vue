<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  productos: { type: Array, default: () => [] },
  ofertas: { type: Array, default: () => [] },
});

const errores = reactive({});

const formCrear = reactive({
  id: '', nombre: '', precio: '', moneda: 'ARS',
});

const editando = reactive({});

function empezarEditar(p) {
  editando[p.id] = { nombre: p.nombre, precio: p.precio, moneda: p.moneda };
}

function cancelarEditar(id) {
  delete editando[id];
}

async function crear() {
  try {
    const res = await fetch('/api/products', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(formCrear),
    });
    if (res.status === 422) {
      const data = await res.json();
      Object.assign(errores, data.errors ?? {});
      return;
    }
    formCrear.id = ''; formCrear.nombre = ''; formCrear.precio = ''; formCrear.moneda = 'ARS';
    router.reload({ only: ['productos'] });
  } catch (e) { console.error(e); }
}

async function guardar(id) {
  const d = editando[id];
  if (!d) return;
  const res = await fetch(`/api/products/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify(d),
  });
  if (res.ok) {
    cancelarEditar(id);
    router.reload({ only: ['productos'] });
  }
}

async function eliminar(id) {
  if (!confirm(`¿Eliminar el producto ${id}?`)) return;
  await fetch(`/api/products/${id}`, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  router.reload({ only: ['productos'] });
}

const formOferta = reactive({ productoId: '', porcentaje: '', validoDesde: '', validoHasta: '' });

async function crearOferta() {
  const res = await fetch('/api/offers', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify(formOferta),
  });
  if (res.ok || res.status === 201) {
    formOferta.productoId = ''; formOferta.porcentaje = ''; formOferta.validoDesde = ''; formOferta.validoHasta = '';
    router.reload({ only: ['ofertas'] });
  }
}

async function eliminarOferta(id) {
  await fetch(`/api/offers/${id}`, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  router.reload({ only: ['ofertas'] });
}

function formato(f) {
  return f ? new Date(f).toLocaleString('es-AR') : '';
}
</script>

<template>
  <section class="space-y-8">
    <h2 class="text-xl font-semibold text-slate-800">Catálogo de productos</h2>

    <!-- Productos -->
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
          <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Nombre</th>
            <th class="px-4 py-2 text-right">Precio</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in productos" :key="p.id" class="border-t border-slate-100">
            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ p.id }}</td>
            <td class="px-4 py-3">
              <span v-if="!editando[p.id]">{{ p.nombre }}</span>
              <input v-else v-model="editando[p.id].nombre" class="w-full rounded border border-slate-300 px-2 py-1" />
            </td>
            <td class="px-4 py-3 text-right">
              <span v-if="!editando[p.id]">{{ p.precio.toFixed(2) }} <span class="text-xs text-slate-400">{{ p.moneda }}</span></span>
              <div v-else class="flex justify-end gap-1">
                <input v-model.number="editando[p.id].precio" type="number" step="0.01" class="w-24 rounded border border-slate-300 px-2 py-1 text-right" />
                <input v-model="editando[p.id].moneda" class="w-16 rounded border border-slate-300 px-2 py-1" />
              </div>
            </td>
            <td class="px-4 py-3 text-right whitespace-nowrap">
              <template v-if="!editando[p.id]">
                <button @click="empezarEditar(p)" class="text-xs text-slate-600 hover:text-emerald-600">Editar</button>
                <button @click="eliminar(p.id)" class="ml-3 text-xs text-slate-600 hover:text-red-600">Eliminar</button>
              </template>
              <template v-else>
                <button @click="guardar(p.id)" class="text-xs text-emerald-600 hover:underline">Guardar</button>
                <button @click="cancelarEditar(p.id)" class="ml-3 text-xs text-slate-500 hover:underline">Cancelar</button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Crear producto -->
    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h3 class="mb-3 text-sm font-semibold text-slate-700">Nuevo producto</h3>
      <form @submit.prevent="crear" class="flex flex-wrap gap-3">
        <input v-model="formCrear.id" placeholder="ID (ej: p-99)" class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm" />
        <input v-model="formCrear.nombre" placeholder="Nombre" class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm" />
        <input v-model.number="formCrear.precio" type="number" step="0.01" placeholder="Precio" class="w-28 rounded border border-slate-300 px-3 py-2 text-sm" />
        <input v-model="formCrear.moneda" placeholder="ARS" class="w-20 rounded border border-slate-300 px-3 py-2 text-sm" />
        <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Crear</button>
      </form>
    </div>

    <!-- Ofertas -->
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
      <div class="border-b border-slate-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-slate-700">Ofertas activas</h3>
      </div>
      <table v-if="ofertas.length" class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
          <tr>
            <th class="px-4 py-2">Producto</th>
            <th class="px-4 py-2 text-right">Descuento</th>
            <th class="px-4 py-2">Vigencia</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in ofertas" :key="o.id" class="border-t border-slate-100">
            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ o.productoId }}</td>
            <td class="px-4 py-3 text-right">
              <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">{{ o.porcentaje }}%</span>
            </td>
            <td class="px-4 py-3 text-xs text-slate-500">{{ formato(o.validoDesde) }} → {{ formato(o.validoHasta) }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="eliminarOferta(o.id)" class="text-xs text-slate-600 hover:text-red-600">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Sin ofertas.</p>
    </div>

    <!-- Crear oferta -->
    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h3 class="mb-3 text-sm font-semibold text-slate-700">Nueva oferta</h3>
      <form @submit.prevent="crearOferta" class="flex flex-wrap gap-3">
        <select v-model="formOferta.productoId" class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm">
          <option value="" disabled>Producto…</option>
          <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.id }} — {{ p.nombre }}</option>
        </select>
        <input v-model.number="formOferta.porcentaje" type="number" step="1" min="0" max="100" placeholder="%" class="w-20 rounded border border-slate-300 px-3 py-2 text-sm" />
        <input v-model="formOferta.validoDesde" type="datetime-local" class="rounded border border-slate-300 px-3 py-2 text-sm" />
        <input v-model="formOferta.validoHasta" type="datetime-local" class="rounded border border-slate-300 px-3 py-2 text-sm" />
        <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Crear oferta</button>
      </form>
    </div>
  </section>
</template>
