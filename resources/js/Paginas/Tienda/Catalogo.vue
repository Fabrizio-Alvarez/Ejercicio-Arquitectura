<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCart } from '../../composables/useCart.js';
import { useFormato } from '../../composables/useFormato.js';

const props = defineProps({
    productos: { type: Array, default: () => [] },
    filtros: { type: Object, default: () => ({}) },
});

const { add } = useCart();
const formato = useFormato();

const busqueda = ref(props.filtros.q ?? '');
const orden = ref(props.filtros.sort ?? 'nombre');

const productosFiltrados = computed(() => {
    let lista = props.productos;

    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        lista = lista.filter((p) =>
            p.nombre.toLowerCase().includes(q) || p.id.toLowerCase().includes(q),
        );
    }

    return lista;
});

function buscar() {
    // Inertia navigation with query params
    const params = new URLSearchParams();
    if (busqueda.value.trim()) params.set('q', busqueda.value.trim());
    if (orden.value !== 'nombre') params.set('sort', orden.value);
    window.location.href = `/tienda/catalogo?${params}`;
}
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800">Catálogo</h1>
            <p class="mt-1 text-slate-500">{{ productosFiltrados.length }} productos disponibles</p>
        </div>

        <!-- Search + Sort bar -->
        <div class="mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="busqueda"
                    @keyup.enter="buscar"
                    type="text"
                    placeholder="Buscar productos..."
                    class="w-full rounded-lg border border-slate-300 py-2.5 pl-11 pr-4 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                />
            </div>
            <select
                v-model="orden"
                @change="buscar"
                class="rounded-lg border border-slate-300 py-2.5 px-4 text-sm text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
            >
                <option value="nombre">Nombre A-Z</option>
                <option value="precio-asc">Precio: menor a mayor</option>
                <option value="precio-desc">Precio: mayor a menor</option>
            </select>
        </div>

        <!-- Product grid -->
        <div v-if="productosFiltrados.length === 0" class="text-center py-20">
            <p class="text-lg text-slate-400">No se encontraron productos.</p>
            <p class="mt-2 text-sm text-slate-400">Probá con otro término de búsqueda.</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div
                v-for="producto in productosFiltrados"
                :key="producto.id"
                class="group flex flex-col rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg hover:border-emerald-300 transition-all duration-200"
            >
                <!-- Product "image" area -->
                <Link :href="`/tienda/producto/${producto.id}`" class="aspect-square bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                    <span class="text-5xl opacity-30 group-hover:scale-110 transition-transform">{{ producto.nombre.charAt(0) }}</span>
                </Link>

                <!-- Info -->
                <div class="flex flex-col flex-1 p-4">
                    <Link :href="`/tienda/producto/${producto.id}`" class="font-semibold text-slate-800 hover:text-emerald-600 transition-colors">
                        {{ producto.nombre }}
                    </Link>
                    <p class="mt-1 text-xs font-mono text-slate-400">{{ producto.id }}</p>

                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <span class="text-xl font-bold text-slate-800">
                            {{ formato.dinero(producto.precio * 100) }}
                            <span class="text-xs font-normal text-slate-400">{{ producto.moneda }}</span>
                        </span>
                        <button
                            @click="add(producto)"
                            :disabled="!producto.disponible"
                            class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ producto.disponible ? 'Agregar' : 'Sin stock' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
