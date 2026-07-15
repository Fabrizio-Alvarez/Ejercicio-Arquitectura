import { reactive, ref } from 'vue';
import { apiFetch } from '../api.js';

/**
 * Composable para formularios que llaman a endpoints de API (no rutas Inertia).
 *
 * Provee el mismo DX que useForm de Inertia — processing, errors, reset —
 * pero funciona con respuestas JSON arbitrarias en lugar de páginas Inertia.
 *
 * @example
 * const { data, processing, errors, result, error, submit, reset } = useApiForm({
 *     productoId: '',
 *     cantidad: 1,
 * });
 * await submit('/api/checkout', { method: 'POST' });
 */
export function useApiForm(initial = {}) {
    const data = reactive({ ...initial });
    const processing = ref(false);
    const errors = ref({});
    const result = ref(null);
    const error = ref(null);

    async function submit(url, options = {}) {
        processing.value = true;
        error.value = null;
        result.value = null;
        errors.value = {};

        try {
            const method = options.method ?? 'POST';
            const body = method !== 'GET' ? JSON.stringify(data) : undefined;

            const res = await apiFetch(url, { ...options, method, body });
            const json = await res.json().catch(() => null);

            if (!res.ok) {
                if (res.status === 422 && json?.errors) {
                    errors.value = json.errors;
                }
                throw new Error(json?.message ?? `Error ${res.status}`);
            }

            result.value = json;
            return json;
        } catch (e) {
            error.value = e.message;
            throw e;
        } finally {
            processing.value = false;
        }
    }

    function reset() {
        Object.assign(data, initial);
        errors.value = {};
        result.value = null;
        error.value = null;
    }

    return { data, processing, errors, result, error, submit, reset };
}
