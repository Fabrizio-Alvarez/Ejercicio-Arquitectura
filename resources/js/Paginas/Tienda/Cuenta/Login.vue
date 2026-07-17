<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/tienda/login', {
        onSuccess: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-2xl bg-white p-8 shadow-sm">
            <div class="mb-6 text-center">
                <span class="text-4xl">🛒</span>
                <h1 class="mt-2 text-2xl font-bold text-slate-800">Ingresá</h1>
                <p class="mt-1 text-sm text-slate-500">Accedé a tu cuenta de cliente</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
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
                        placeholder="Tu contraseña"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-colors"
                    />
                </label>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                    Recordarme
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                ¿No tenés cuenta?
                <Link href="/tienda/registro" class="font-medium text-emerald-600 hover:text-emerald-700">Registrate</Link>
            </p>
        </div>
    </div>
</template>
