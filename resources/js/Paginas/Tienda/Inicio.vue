<script setup>
import { Link } from '@inertiajs/vue3';
import { useCart } from '../../composables/useCart.js';
import { useFormato } from '../../composables/useFormato.js';
import { useToast } from '../../composables/useToast.js';
import { emojiProducto, colorProducto } from '../../constants/emojis.js';
import { CATEGORIAS, infoCategoria } from '../../constants/categorias.js';

defineProps({
    destacados: { type: Array, default: () => [] },
    totalProductos: { type: Number, default: 0 },
});

const { add } = useCart();
const formato = useFormato();
const toast = useToast();
const emoji = emojiProducto;
const color = colorProducto;
const cat = infoCategoria;
const cats = CATEGORIAS;
</script>

<template>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-slate-900 text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 text-9xl">🛒</div>
            <div class="absolute bottom-10 right-10 text-9xl">📦</div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">
                Hacé tu compra online
            </h1>
            <p class="mt-4 text-lg text-emerald-100 max-w-2xl mx-auto">
                Productos frescos, precios justos y pago fácil. Elegí, sumá al carrito y recibí tu pedido.
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <Link href="/tienda/catalogo" class="rounded-xl bg-white px-6 py-3 font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors shadow-lg">
                    Ver catálogo
                </Link>
                <Link href="#destacados" class="rounded-xl border-2 border-white/30 px-6 py-3 font-semibold text-white hover:bg-white/10 transition-colors">
                    Destacados
                </Link>
            </div>
        </div>
    </section>

    <!-- Category quick links -->
    <section class="border-b border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-2">
                <span class="text-sm font-medium text-slate-500 mr-2">Categorías:</span>
                <Link
                    v-for="categoria in cats"
                    :key="categoria.id"
                    href="/tienda/catalogo"
                    class="rounded-full bg-white px-3 py-1.5 text-sm font-medium text-slate-700 border border-slate-200 hover:border-emerald-300 hover:text-emerald-700 transition-colors"
                >
                    {{ categoria.emoji }} {{ categoria.nombre }}
                </Link>
            </div>
        </div>
    </section>

    <!-- Features strip -->
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                <div class="flex flex-col items-center gap-2">
                    <span class="text-3xl">🚚</span>
                    <p class="text-sm font-medium text-slate-700">Retiro en tienda</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <span class="text-3xl">💳</span>
                    <p class="text-sm font-medium text-slate-700">Múltiples medios de pago</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <span class="text-3xl">📦</span>
                    <p class="text-sm font-medium text-slate-700">{{ totalProductos }} productos disponibles</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured products -->
    <section id="destacados" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Productos destacados</h2>
            <Link href="/tienda/catalogo" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                Ver todos →
            </Link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div
                v-for="producto in destacados"
                :key="producto.id"
                class="group flex flex-col rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg hover:border-emerald-300 transition-all"
            >
                <Link :href="`/tienda/producto/${producto.id}`" :class="`relative aspect-square bg-gradient-to-br ${color(producto.id)} flex items-center justify-center`">
                    <span class="text-5xl drop-shadow-lg group-hover:scale-125 transition-transform duration-300">{{ emoji(producto.nombre) }}</span>
                    <!-- Category badge -->
                    <span class="absolute top-2 right-2 rounded-full bg-white/80 backdrop-blur px-2 py-0.5 text-xs font-medium text-slate-700">
                        {{ cat(producto.id).emoji }} {{ cat(producto.id).nombre }}
                    </span>
                    <!-- Sin stock overlay -->
                    <div v-if="!producto.disponible" class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <span class="rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white">Sin stock</span>
                    </div>
                </Link>
                <div class="flex flex-col flex-1 p-3">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ producto.nombre }}</p>
                    <div class="mt-auto pt-2 flex items-center justify-between">
                        <span class="font-bold text-slate-800">{{ formato.dinero(producto.precio * 100) }}</span>
                        <button
                            @click="add(producto); toast.success(`${producto.nombre} agregado`)"
                            :disabled="!producto.disponible"
                            class="rounded-lg bg-emerald-600 px-2 py-1.5 text-xs font-medium text-white hover:bg-emerald-700 disabled:bg-slate-300 transition-colors"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
