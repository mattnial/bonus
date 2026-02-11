// ARCHIVO: assets/js/modules/naps_data.js
// Aquí listamos tus Cajas NAP (Exportadas de tu Google Maps o MyMaps)

const CAJAS_NAP = [
    // EJEMPLOS EN VILCABAMBA (Reemplaza con tus datos reales)
    { id: 'NAP-VIL-01', lat: -4.2605, lng: -79.2230, zona: 'Vilcabamba Centro' },
    { id: 'NAP-VIL-02', lat: -4.2615, lng: -79.2240, zona: 'Vilcabamba Parque' },
    { id: 'NAP-VIL-03', lat: -4.2590, lng: -79.2210, zona: 'Vilcabamba Entrada' },

    // EJEMPLOS EN MALACATOS
    { id: 'NAP-MAL-01', lat: -4.2150, lng: -79.2550, zona: 'Malacatos' },

    // EJEMPLOS EN LOJA (Sector Estadio)
    { id: 'NAP-LOJ-01', lat: -4.0070, lng: -79.2050, zona: 'Loja Estadio' }
];

// Configuración de distancias (en metros)
const UMBRALES = {
    OPTIMO: 200,      // Menos de 200m: Fibra directa (Verde)
    FACTIBLE: 400,    // 200m a 400m: Requiere revisión (Amarillo)
    LEJANO: 1000      // Más de 400m: Radioenlace o No factible (Rojo)
};