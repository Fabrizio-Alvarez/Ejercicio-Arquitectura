<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const perfil = computed(() => page.props.perfil);
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <header class="bg-slate-900 text-white shadow">
            <div class="mx-auto flex max-w-5xl items-center gap-6 px-4 py-4">
                <h1 class="text-lg font-semibold">🛒 Supermercado</h1>
                <nav v-if="perfil" class="flex gap-4 text-sm">
                    <Link
                        v-for="pagina in perfil.paginas"
                        :key="pagina.ruta"
                        :href="`/${pagina.ruta}`"
                        class="hover:text-emerald-300"
                    >{{ pagina.etiqueta }}</Link>
                </nav>
                <div v-if="perfil" class="ml-auto flex items-center gap-3 text-sm">
                    <span class="rounded bg-slate-700 px-2 py-0.5">{{ perfil.etiqueta }}</span>
                    <Link href="/iniciar" class="text-slate-300 hover:text-emerald-300">Cambiar perfil</Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
