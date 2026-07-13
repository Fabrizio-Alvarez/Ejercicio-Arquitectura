<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
</script>

<template>
    <section class="mx-auto max-w-md px-4 py-16">
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-slate-900">🛒 Supermercado</h1>
            <p class="mt-2 text-slate-500">Iniciá sesión para continuar</p>
        </div>

        <form @submit.prevent="form.post('/login')" class="space-y-4 bg-white p-8 rounded-lg shadow-sm">
            <label class="block">
                <span class="text-sm text-slate-600">Email</span>
                <input v-model="form.email" type="email" autocomplete="email" class="mt-1 w-full rounded border-slate-300" />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Contraseña</span>
                <input v-model="form.password" type="password" autocomplete="current-password" class="mt-1 w-full rounded border-slate-300" />
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input v-model="form.remember" type="checkbox" name="remember" />
                Recordarme
            </label>

            <button :disabled="form.processing" type="submit" class="w-full rounded bg-emerald-600 py-2 font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
            </button>
        </form>
    </section>
</template>
