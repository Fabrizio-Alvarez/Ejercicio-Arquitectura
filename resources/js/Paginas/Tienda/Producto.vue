<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCart } from '../../composables/useCart.js';
import { useFormato } from '../../composables/useFormato.js';

const props = defineProps({
    producto: { type: Object, required: true },
    ofertas: { type: Array, default: () => [] },
});

const { add } = useCart();
const formato = useFormato();
const cantidad = ref(1);

function agregarAlCarrito() {
    add({ ...props.producto });
    cantidad.value = 1;
}
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
            <Link href="/tienda" class="hover:text-emerald-600">Inicio</Link>
            <span>/</span>
            <Link href="/tienda/catalogo" class="hover:text-emerald-600">Catálogo</Link>
            <span>/</span>
            <span class="text-slate-700">{{ producto.nombre }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- "Image" -->
            <div class="aspect-square rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center shadow-sm">
                <span class="text-[12rem] opacity-20">{{ producto.nombre.charAt(0) }}</span>
            </div>

            <!-- Info -->
            <div class="flex flex-col">
                <h1 class="text-3xl font-bold text-slate-800">{{ producto.nombre }}</h1>
                <p class="mt-1 font-mono text-sm text-slate-400">SKU: {{ producto.id }}</p>

                <!-- Offers -->
                <div v-if="ofertas.length" class="mt-4 space-y-2">
                    <div
                        v-for="(oferta, i) in ofertas"
                        :key="i"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-50 border border-purple-200 px-3 py-1.5 text-sm text-purple-700"
                    >
                        🏷️ {{ oferta.porcentaje }}% de descuento
                        <span class="text-xs text-purple-400">
                            ({{ oferta.validoDesde }} – {{ oferta.validoHasta ?? 'sin fin' }})
                        </span>
                    </div>
                </div>

                <!-- Price -->
                <div class="mt-6">
                    <span class="text-4xl font-extrabold text-slate-800">
                        {{ formato.dinero(producto.precio * 100) }}
                    </span>
                    <span class="ml-2 text-lg text-slate-400">{{ producto.moneda }}</span>
                </div>

                <!-- Stock -->
                <div class="mt-4">
                    <span v-if="producto.disponible" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                        ✓ En stock ({{ producto.gondola }} en góndola)
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                        ✗ Sin stock
                    </span>
                </div>

                <!-- Add to cart -->
                <div class="mt-8 flex items-center gap-4">
                    <div class="flex items-center rounded-lg border border-slate-300">
                        <button @click="cantidad = Math.max(1, cantidad - 1)" class="w-10 h-10 text-slate-600 hover:bg-slate-100 rounded-l-lg font-bold">−</button>
                        <input v-model.number="cantidad" type="number" min="1" class="w-14 border-0 text-center font-medium focus:ring-0" />
                        <button @click="cantidad++" class="w-10 h-10 text-slate-600 hover:bg-slate-100 rounded-r-lg font-bold">+</button>
                    </div>
                    <button
                        @click="agregarAlCarrito"
                        :disabled="!producto.disponible"
                        class="flex-1 rounded-xl bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:bg-slate-300 transition-colors shadow-sm"
                    >
                        Agregar al carrito
                    </button>
                </div>

                <!-- Info sections -->
                <div class="mt-8 space-y-3 border-t pt-6 text-sm text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏪</span> Disponible para retiro en tienda
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg">💳</span> Efectivo, tarjetas, transferencia y QR
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
