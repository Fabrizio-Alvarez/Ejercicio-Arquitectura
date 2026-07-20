/**
 * Mapeo de productos a categorías.
 *
 * El dominio (Producto) no tiene categoría — es un concepto de presentación
 * del catálogo. Este mapa se genera a partir de la estructura del seeder
 * y mantiene las 7 categorías del supermercado.
 *
 * Si se agregan productos nuevos, alcanza con añadir su ID acá.
 */

export const CATEGORIAS = [
    { id: 'lacteos',     nombre: 'Lácteos',           emoji: '🥛' },
    { id: 'panaderia',   nombre: 'Panadería',         emoji: '🍞' },
    { id: 'bebidas',     nombre: 'Bebidas',           emoji: '🥤' },
    { id: 'almacen',     nombre: 'Almacén',           emoji: '🍝' },
    { id: 'frescos',     nombre: 'Frescos',           emoji: '🥬' },
    { id: 'limpieza',    nombre: 'Limpieza',          emoji: '🧼' },
    { id: 'cuidado',     nombre: 'Cuidado personal',  emoji: '🧴' },
];

const MAPA = {
    // Lácteos
    'p-1':  'lacteos',
    'p-4':  'lacteos',
    'p-5':  'lacteos',
    'p-6':  'lacteos',
    'p-7':  'lacteos',
    // Panadería
    'p-2':  'panaderia',
    'p-8':  'panaderia',
    'p-9':  'panaderia',
    'p-10': 'panaderia',
    // Bebidas
    'p-11': 'bebidas',
    'p-12': 'bebidas',
    'p-13': 'bebidas',
    'p-14': 'bebidas',
    'p-15': 'bebidas',
    // Almacén
    'p-3':  'almacen',
    'p-16': 'almacen',
    'p-17': 'almacen',
    'p-18': 'almacen',
    'p-19': 'almacen',
    'p-20': 'almacen',
    'p-21': 'almacen',
    // Frescos
    'p-22': 'frescos',
    'p-23': 'frescos',
    'p-24': 'frescos',
    'p-25': 'frescos',
    'p-26': 'frescos',
    'p-27': 'frescos',
    // Limpieza
    'p-28': 'limpieza',
    'p-29': 'limpieza',
    'p-30': 'limpieza',
    // Cuidado personal
    'p-31': 'cuidado',
    'p-32': 'cuidado',
};

/**
 * Obtiene el ID de categoría para un producto.
 * @param {string} productoId
 * @returns {string} ID de categoría, o 'otros' si no está mapeado.
 */
export function categoriaDe(productoId) {
    return MAPA[productoId] ?? 'otros';
}

/**
 * Obtiene el objeto de categoría completo para un producto.
 * @param {string} productoId
 * @returns {{id: string, nombre: string, emoji: string}}
 */
export function infoCategoria(productoId) {
    const catId = categoriaDe(productoId);
    return CATEGORIAS.find((c) => c.id === catId) ?? { id: 'otros', nombre: 'Otros', emoji: '📦' };
}
