<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen">
        <header class="bg-slate-900 text-white shadow">
            <div class="mx-auto max-w-5xl px-4 py-4 flex items-center gap-6">
                <h1 class="text-lg font-semibold">🛒 Supermercado</h1>
                <nav class="flex gap-4 text-sm">
                    <Link href="/stock" class="hover:text-emerald-300">Stock</Link>
                    <Link href="/cobrar" class="hover:text-emerald-300">Cobrar</Link>
                    <Link href="/movimientos" class="hover:text-emerald-300">Movimientos</Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
