/**
 * Wrapper around fetch for authenticated Inertia SPA requests.
 *
 * Reads the CSRF token from the <meta name="csrf-token"> tag and sends it
 * as the X-XSRF-TOKEN header so Sanctum stateful authentication works.
 */
export async function apiFetch(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token,
        ...(options.headers ?? {}),
    };

    return fetch(url, { ...options, headers, credentials: 'same-origin' });
}
