/**
 * Mapa de productos a emojis basado en palabras clave del nombre.
 * Fallback determinístico a un emoji genérico si no hay match.
 */
const REGLAS = [
    { test: /leche|lácteo|yogurt|yoghurt|queso|manteca|crema/i, emoji: '🥛' },
    { test: /pan|factura|medialuna|croissant|bagel|bisco/i, emoji: '🍞' },
    { test: /café|cafe|capuchino|latte|espresso/i, emoji: '☕' },
    { test: /jugo/i, emoji: '🥤' },
    { test: /manzana/i, emoji: '🍎' },
    { test: /banana/i, emoji: '🍌' },
    { test: /naranja|pera|uva|sandía|melón|fruta/i, emoji: '🍎' },
    { test: /carne|res|vaca|lomo|milanesa|bife/i, emoji: '🥩' },
    { test: /pollo|ave|pavo/i, emoji: '🍗' },
    { test: /pescado|merluza|salmón|atún/i, emoji: '🐟' },
    { test: /bebida|gaseosa|coca|sprite|fanta|jugo/i, emoji: '🥤' },
    { test: /cerveza|alcohol|champán|whisky/i, emoji: '🍺' },
    { test: /vino/i, emoji: '🍷' },
    { test: /verdura|lechuga|tomate|cebolla|zanahoria|papa|cebolla/i, emoji: '🥬' },
    { test: /arroz|fideo|pasta|harina|sémola/i, emoji: '🍝' },
    { test: /huevo/i, emoji: '🥚' },
    { test: /aceite|oliva|girasol/i, emoji: '🫒' },
    { test: /dulce|chocolate|galleta|caramel|postre|torta|tarta|alfajor/i, emoji: '🍫' },
    { test: /limpieza|lavandina|jabón|detergente/i, emoji: '🧼' },
    { test: /shampoo|acondicionador|jabón|pasta dental|cepillo/i, emoji: '🧴' },
    { test: /pañal|pañuelo|papel higiénico|cocina/i, emoji: '🧻' },
    { test: /agua/i, emoji: '💧' },
    { test: /helado/i, emoji: '🍦' },
    { test: /pizza/i, emoji: '🍕' },
    { test: /salsa|ketchup|mayonesa|mostaza/i, emoji: '🥫' },
    { test: /miel|mermelada/i, emoji: '🍯' },
    { test: /té|te|infusión/i, emoji: '🍵' },
    { test: /azúcar|sal/i, emoji: '🧂' },
];

const COLORES_GRADIENTES = [
    'from-sky-400 to-blue-500',
    'from-emerald-400 to-teal-500',
    'from-amber-400 to-orange-500',
    'from-rose-400 to-pink-500',
    'from-violet-400 to-purple-500',
    'from-cyan-400 to-sky-500',
    'from-lime-400 to-green-500',
    'from-fuchsia-400 to-rose-500',
];

/**
 * Obtiene el emoji para un producto.
 * @param {string} nombre - Nombre del producto.
 * @returns {string} Emoji correspondiente o '📦' como fallback.
 */
export function emojiProducto(nombre) {
    if (!nombre) return '📦';
    const match = REGLAS.find((r) => r.test.test(nombre));
    return match ? match.emoji : '📦';
}

/**
 * Obtiene un color de gradiente determinístico basado en el ID del producto.
 * @param {string} id - ID del producto.
 * @returns {string} Clases de gradiente Tailwind.
 */
export function colorProducto(id) {
    let hash = 0;
    const str = String(id);
    for (let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash + str.charCodeAt(i)) | 0;
    }
    return COLORES_GRADIENTES[Math.abs(hash) % COLORES_GRADIENTES.length];
}
