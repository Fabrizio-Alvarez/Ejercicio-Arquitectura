<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCart } from '../composables/useCart.js';
import { useFormato } from '../composables/useFormato.js';

const { count, items, subtotal, increment, decrement, remove } = useCart();
const formato = useFormato();

const cartOpen = ref(false);

function toggleCart() {
    cartOpen.value = !cartOpen.value;
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-50">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/tienda" class="flex items-center gap-2">
                        <span class="text-2xl">🛒</span>
                        <span class="font-bold text-lg text-slate-800">Supermercado</span>
                    </Link>

                    <!-- Nav -->
                    <nav class="hidden sm:flex items-center gap-6 text-sm">
                        <Link href="/tienda" class="text-slate-600 hover:text-emerald-600 transition-colors">Inicio</Link>
                        <Link href="/tienda/catalogo" class="text-slate-600 hover:text-emerald-600 transition-colors">Catálogo</Link>
                    </nav>

                    <!-- Cart button -->
                    <button
                        @click="toggleCart"
                        class="relative flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="hidden sm:inline">Carrito</span>
                        <span
                            v-if="count > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white"
                        >{{ count }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🛒</span>
                        <span class="font-semibold text-white">Supermercado</span>
                    </div>
                    <p class="text-sm">© 2026 Supermercado · Ejercicio de arquitectura DDD</p>
                </div>
            </div>
        </footer>

        <!-- Cart Drawer -->
        <Teleport to="body">
            <Transition name="drawer">
                <div v-if="cartOpen" class="fixed inset-0 z-50 flex justify-end">
                    <!-- Backdrop -->
                    <div @click="toggleCart" class="absolute inset-0 bg-black/40"></div>

                    <!-- Panel -->
                    <div class="relative w-full max-w-md bg-white shadow-xl flex flex-col">
                        <div class="flex items-center justify-between border-b px-5 py-4">
                            <h2 class="text-lg font-bold text-slate-800">Tu carrito</h2>
                            <button @click="toggleCart" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-5">
                            <div v-if="items.length === 0" class="text-center py-16 text-slate-400">
                                <p class="text-lg">Tu carrito está vacío</p>
                                <Link href="/tienda/catalogo" @click="toggleCart" class="mt-4 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                    Ver catálogo
                                </Link>
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="item in items" :key="item.id" class="flex items-center gap-3 border-b pb-3">
                                    <div class="flex-1">
                                        <p class="font-medium text-slate-800">{{ item.nombre }}</p>
                                        <p class="text-sm text-slate-500">{{ formato.dinero(item.precio * 100) }} {{ item.moneda }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="decrement(item.id)" class="w-7 h-7 rounded bg-slate-100 hover:bg-slate-200 font-bold text-slate-600">−</button>
                                        <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                                        <button @click="increment(item.id)" class="w-7 h-7 rounded bg-slate-100 hover:bg-slate-200 font-bold text-slate-600">+</button>
                                        <button @click="remove(item.id)" class="ml-1 text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="items.length > 0" class="border-t px-5 py-4 space-y-3">
                            <div class="flex justify-between font-semibold text-slate-800">
                                <span>Subtotal</span>
                                <span>{{ formato.dinero(subtotal * 100) }} ARS</span>
                            </div>
                            <Link
                                href="/tienda/checkout"
                                @click="toggleCart"
                                class="block w-full rounded-lg bg-emerald-600 py-3 text-center font-semibold text-white hover:bg-emerald-700 transition-colors"
                            >
                                Finalizar compra
                            </Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.2s ease;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}
</style>
