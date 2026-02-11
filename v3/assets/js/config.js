// ARCHIVO: assets/js/config.js

// Usamos window.API_URL para evitar el error de "redeclaration"
// si el archivo llega a cargarse dos veces por accidente.
if (typeof window.API_URL === 'undefined') {
    window.API_URL = "api";
}