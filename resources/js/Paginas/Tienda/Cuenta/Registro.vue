<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/tienda/registro', {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-2xl bg-white p-8 shadow-sm">
            <div class="mb-6 text-center">
                <span class="text-4xl">🛒</span>
                <h1 class="mt-2 text-2xl font-bold text-slate-800">Crear cuenta</h1>
                <p class="mt-1 text-sm text-slate-500">Registrate para comprar más rápido</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Nombre</span>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Tu nombre"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-colors"
                    />
                    <span v-if="form.errors.name" class="text-sm text-red-500 mt-1 block">{{ form.errors.name }}</span>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Email</span>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="tu@email.com"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-colors"
                    />
                    <span v-if="form.errors.email" class="text-sm text-red-500 mt-1 block">{{ form.errors.email }}</span>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Contraseña</span>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Mínimo 8 caracteres"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-colors"
                    />
                    <span v-if="form.errors.password" class="text-sm text-red-500 mt-1 block">{{ form.errors.password }}</span>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Confirmar contraseña</span>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Repetí tu contraseña"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-colors"
                    />
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? 'Creando…' : 'Crear cuenta' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                ¿Ya tenés cuenta?
                <Link href="/tienda/login" class="font-medium text-emerald-600 hover:text-emerald-700">Ingresá</Link>
            </p>
        </div>
    </div>
</template>
