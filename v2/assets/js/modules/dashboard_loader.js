/**
 * ARCHIVO: assets/js/modules/dashboard_loader.js
 * UBICACIÓN CORRECTA: public_html/assets/js/modules/dashboard_loader.js
 */

document.addEventListener('DOMContentLoaded', initDashboardLoader);

async function initDashboardLoader() {
    console.log("🧠 Dashboard Loader: Iniciando desde modules...");

    const userStr = localStorage.getItem('vilcanet_staff');
    if (!userStr) {
        console.error("No hay sesión válida.");
        return;
    }

    const user = JSON.parse(userStr);

    // 1. Determinar el nombre del archivo
    let dashboardFile = 'gerencia'; // Default

    if (user.assigned_dashboard && user.assigned_dashboard !== 'default') {
        dashboardFile = user.assigned_dashboard;
    } else {
        const role = user.role.toUpperCase();
        if (role.includes('TECNICO') || role.includes('SOPORTE')) dashboardFile = 'tecnico';
        else if (role.includes('VENTAS') || role.includes('COMERCIAL')) dashboardFile = 'ventas';
        else if (role.includes('COBRANZAS') || role.includes('CAJA')) dashboardFile = 'cobranzas';
    }

    // Limpieza
    dashboardFile = dashboardFile.toLowerCase().replace(/[^a-z0-9_]/g, '');

    // 2. DEFINIR LA RUTA CORRECTA (Aquí estaba el error)
    // Ahora apuntamos a: assets/js/modules/dashboards/
    const basePath = 'assets/js/modules/dashboards/';

    console.log(`📂 Cargando módulo: ${basePath}${dashboardFile}.js`);

    // 3. Cargar el script
    loadScript(`${basePath}${dashboardFile}.js`)
        .then(() => console.log("✅ Interfaz cargada."))
        .catch(() => {
            console.warn(`⚠️ No existe ${dashboardFile}.js. Cargando Gerencia.`);
            loadScript(`${basePath}gerencia.js`);
        });
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src + '?v=' + new Date().getTime(); // Evitar caché
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
    });
}