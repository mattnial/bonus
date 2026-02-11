// ARCHIVO: assets/js/core.js

// --- VARIABLES GLOBALES (Usamos var para evitar errores si se recarga) ---
var CURRENT_CLIENT_ID = window.CURRENT_CLIENT_ID || null;
var CURRENT_TICKET_ID = window.CURRENT_TICKET_ID || null;
var CURRENT_STAFF_ID = window.CURRENT_STAFF_ID || null;


// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', async () => {
    // 1. Verificación de seguridad
    if (!localStorage.getItem('vilcanet_staff')) {
        window.location.href = 'login.html';
        return;
    }

    // 2. Carga visual inmediata (Perfil y Menú)
    loadUserProfile();
    renderDynamicMenu(); // <--- ESTO DIBUJA EL MENÚ

    // 3. Carga escalonada
    await new Promise(r => setTimeout(r, 500));

    // A. Estadísticas (Solo en dashboard principal)
    if (document.getElementById('local-kpi-clients')) {
        if (typeof loadDashboardStats === 'function') await loadDashboardStats();
    }

    // B. Alertas
    await checkNotifications();

    // C. Configurar el buscador manual global
    const searchInput = document.getElementById('clientSearchInput');
    if (searchInput && typeof handleSearch === 'function') {
        searchInput.addEventListener('input', debounce(handleSearch, 500));
    }

    // D. Intervalo de actualización (cada 60s)
    setInterval(() => {
        checkNotifications();
    }, 60000);
});

// Esta seccion dibuja el menu
// REEMPLAZA ESTO EN: assets/js/core.js

async function renderDynamicMenu() {
    const container = document.getElementById('dynamicMenuContainer');
    if (!container) return;

    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
    if (!user) return;

    try {
        // 1. Obtenemos permisos
        const res = await fetch(`${API_URL}/admin/get_my_menu.php?id=${user.id}`);
        const menuItems = await res.json();
        const allowedIds = menuItems.map(i => i.view_name); // ['home', 'billing', etc.]

        container.innerHTML = '';

        // Variable temporal para guardar el título "en espera"
        let pendingHeader = null;

        // 2. Recorremos la configuración
        if (!window.APP_MENU_CONFIG) { console.error("Falta menu_config.js"); return; }

        window.APP_MENU_CONFIG.forEach(item => {

            // CASO A: Es un Título (Header)
            // NO lo dibujamos todavía. Lo guardamos en memoria.
            if (item.type === 'header') {
                pendingHeader = item;
                return;
            }

            // CASO B: Es un Ítem (Botón)
            // Verificamos si tiene permiso
            // CORRECCIÓN ANTIGRAVITY: Si es ADMIN, permitir todo (evita problemas de IDs dispares en v3)
            let hasAccess = (item.id === 'home' || allowedIds.includes(item.id));

            if (user.role === 'ADMIN' || user.role === 'SUPERADMIN' || user.role === 'GERENCIA') {
                hasAccess = true;
            } else {
                // Mapeo manual de IDs nuevos vs viejos (v2 DB)
                if (item.id === 'docs' && allowedIds.includes('contratos')) hasAccess = true;
                if (item.id === 'sales' && allowedIds.includes('ventas')) hasAccess = true;
            }

            if (hasAccess) {
                // ¡BINGO! El usuario puede ver este botón.

                // 1. Si había un título esperando, lo dibujamos ahora (porque ya sabemos que la sección no está vacía)
                if (pendingHeader) {
                    container.insertAdjacentHTML('beforeend', `
                        <div class="px-4 mt-6 mb-2">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest border-l-2 border-red-500 pl-2">
                                ${pendingHeader.label}
                            </p>
                        </div>`);
                    pendingHeader = null; // Ya lo dibujamos, limpiamos la variable
                }

                // 2. Dibujamos el botón (Dropdown o Simple)
                if (item.type === 'dropdown') {
                    const dropId = `menu-drop-${item.id}`;
                    container.insertAdjacentHTML('beforeend', `
                        <div class="mb-1">
                            <button onclick="toggleMenuDropdown('${dropId}', this)" 
                                class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 rounded-lg transition-all duration-200 group">
                                <div class="flex items-center gap-3">
                                    <i class="${item.icon} w-5 text-center text-lg transition-colors group-hover:text-blue-500"></i>
                                    <span>${item.label}</span>
                                </div>
                                <i class="fas fa-chevron-right text-[10px] arrow transition-transform duration-300"></i>
                            </button>
                            
                            <div id="${dropId}" class="hidden pl-10 pr-2 mt-1 space-y-1 overflow-hidden transition-all duration-300">
                                ${item.subitems.map(sub => {
                        let subAction = sub.action || `switchView('${sub.view}')`;
                        if (sub.href) subAction = `window.location.href='${sub.href}'`;

                        return `<button onclick="${subAction}" class="w-full text-left py-2 px-3 text-xs font-medium text-slate-500 hover:text-blue-400 hover:bg-slate-800/30 rounded-md transition-colors border-l-2 border-transparent hover:border-blue-500">
                                        ${sub.label}
                                    </button>`;
                    }).join('')}
                            </div>
                        </div>`);
                } else {
                    let action = item.action || `switchView('${item.view}')`;
                    if (item.href) action = `window.location.href='${item.href}'`;

                    container.insertAdjacentHTML('beforeend', `
                        <div class="mb-1">
                            <button onclick="${action}" id="menu-btn-${item.id}"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 rounded-lg transition-all duration-200 group">
                                <i class="${item.icon} w-5 text-center text-lg transition-colors group-hover:text-blue-500"></i>
                                <span>${item.label}</span>
                            </button>
                        </div>`);
                }
            }
            // Si no tiene acceso, no hacemos nada. 
            // Si el siguiente ítem es otro Header, 'pendingHeader' se sobrescribirá, 
            // así que el título anterior nunca se dibujará. ¡Problema resuelto!
        });

    } catch (e) { console.error("Error menu render:", e); }
}

// Función auxiliar necesaria para la animación
window.toggleMenuDropdown = function (id, btn) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
    const arrow = btn.querySelector('.arrow');
    if (arrow) arrow.classList.toggle('rotate-90');
}

// Función auxiliar para animar la flecha
window.toggleMenuDropdown = function (id, btn) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
    const arrow = btn.querySelector('.arrow');
    if (arrow) arrow.classList.toggle('rotate-90');
}

// --- MODAL DE PERFIL (LA FUNCIÓN QUE DABA ERROR) ---
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        // Si por alguna razón no existe el modal en el HTML, cerrar sesión directo como fallback
        if (confirm("¿Cerrar Sesión?")) logout();
    }
}

// --- NAVEGACIÓN ---
function switchView(viewName) {
    // 1. Ocultar el Dashboard viejo por si acaso
    const dashboard = document.getElementById('main-dashboard-content');
    if (dashboard) dashboard.style.display = 'none';

    // 2. Ocultar el ÁREA DE WIDGETS (Esto faltaba para limpiar Cobranzas)
    const widgetArea = document.getElementById('main-content');
    if (widgetArea) {
        widgetArea.style.display = 'none';
        widgetArea.innerHTML = ''; // Opcional: Limpiar memoria
    }

    // 3. Ocultar todas las vistas estándar
    document.querySelectorAll('[id^="view-"]').forEach(el => {
        el.classList.add('hidden');
    });

    // 4. Mostrar la seleccionada (Soporte, RRHH, etc)
    const target = document.getElementById('view-' + viewName);
    if (target) target.classList.remove('hidden');

    // 5. Hooks (Lógica específica de cada vista)
    if (viewName === 'tickets' && typeof loadGlobalTickets === 'function') loadGlobalTickets();
    if (viewName === 'rrhh' && typeof loadRRHH === 'function') loadRRHH();
    if (viewName === 'billing') {
        if (typeof loadDebtors === 'function') loadDebtors();
        // ... resto de tu lógica de billing ...
    }
}

// --- UTILIDADES UI ---
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const styles = {
        success: 'bg-green-600 border-green-800 text-white',
        error: 'bg-red-600 border-red-800 text-white',
        warning: 'bg-yellow-500 border-yellow-700 text-white',
        info: 'bg-blue-600 border-blue-800 text-white'
    };

    toast.className = `${styles[type] || styles.info} px-4 py-3 rounded-lg shadow-xl mb-3 flex items-center gap-3 transform transition-all duration-300 translate-x-10 opacity-0 pointer-events-auto border-l-4 min-w-[300px] z-[9999]`;
    toast.innerHTML = `<span class="font-bold text-sm">${message}</span>`;

    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.remove('translate-x-10', 'opacity-0'));
    setTimeout(() => {
        toast.classList.add('translate-x-10', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function askConfirm(message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        if (!modal) { if (confirm(message)) resolve(true); else resolve(false); return; }

        document.getElementById('confirmMsg').textContent = message;
        modal.classList.remove('hidden');

        const btnYes = document.getElementById('btnConfirmYes');
        const btnNo = document.getElementById('btnConfirmNo');

        const newYes = btnYes.cloneNode(true);
        const newNo = btnNo.cloneNode(true);
        btnYes.parentNode.replaceChild(newYes, btnYes);
        btnNo.parentNode.replaceChild(newNo, btnNo);

        newYes.addEventListener('click', () => { modal.classList.add('hidden'); resolve(true); });
        newNo.addEventListener('click', () => { modal.classList.add('hidden'); resolve(false); });
    });
}

function debounce(func, wait) {
    let timeout;
    return function (...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), wait); };
}

// --- PERFIL Y SESIÓN ---
function loadUserProfile() {
    try {
        const storedUser = localStorage.getItem('vilcanet_staff');
        if (!storedUser) { logout(); return; }

        const user = JSON.parse(storedUser);

        if (document.getElementById('userName')) document.getElementById('userName').textContent = user.name;
        if (document.getElementById('userRole')) document.getElementById('userRole').textContent = user.role;

        const img = document.getElementById('userAvatarImg');
        const defaultUrl = 'assets/img/default_admin.png';

        if (img) {
            const src = (user.avatar_url && user.avatar_url.length > 4)
                ? (user.avatar_url.includes('http') ? user.avatar_url : `uploads/avatars/${user.avatar_url}`)
                : defaultUrl;
            img.src = src;
            img.onerror = () => { img.src = defaultUrl; };
        }

        applyPermissions(user.role);
    } catch (e) { logout(); }
}

function logout() {
    localStorage.removeItem('vilcanet_staff');
    window.location.href = 'login.html';
}

function applyPermissions(role) {
    // Aquí puedes agregar lógica visual extra si es necesario
    // Pero el menú dinámico ya maneja la mayoría de restricciones
}

// --- NOTIFICACIONES ---
async function checkNotifications() {
    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
    if (!user) return;
    try {
        const res = await fetch(`${API_URL}/admin/get_alerts.php?id=${user.id}&role=${user.role}`);
        const alerts = await res.json();
        const badge = document.getElementById('notif-badge');
        if (badge) (alerts.length > 0) ? badge.classList.remove('hidden') : badge.classList.add('hidden');
    } catch (e) { }
}

function toggleNotifications() { document.getElementById('notifDropdown').classList.toggle('hidden'); }

function handleNotificationClick(action, alertId) {
    document.getElementById('notifDropdown').classList.add('hidden');
    const read = JSON.parse(localStorage.getItem('read_alerts') || '[]');
    read.push(alertId); localStorage.setItem('read_alerts', JSON.stringify(read));
    checkNotifications();
    if (action === 'URGENT_TICKETS') { switchView('tickets'); if (typeof loadGlobalTickets === 'function') loadGlobalTickets(); }
    else if (action === 'DEBTORS') { switchView('billing'); }
}

async function loadDashboardStats() {
    try {
        // 1. Cargar Números (KPIs)
        const res = await fetch(`${API_URL}/admin/get_dashboard_stats.php`);
        const stats = await res.json();

        if (document.getElementById('local-kpi-clients')) document.getElementById('local-kpi-clients').textContent = stats.clients_active;
        if (document.getElementById('local-kpi-tickets')) document.getElementById('local-kpi-tickets').textContent = stats.tickets_open;
        if (document.getElementById('local-kpi-urgent')) document.getElementById('local-kpi-urgent').textContent = stats.tickets_urgent;
        if (document.getElementById('local-kpi-debt')) document.getElementById('local-kpi-debt').textContent = stats.clients_debt;

        // 2. Cargar Lista de Clientes Inicial (Lo que faltaba)
        // Esto usa la función del módulo clients.js
        if (typeof handleSearch === 'function') {
            // Enviamos búsqueda vacía para traer los recientes/urgentes
            handleSearch('');
        }

    } catch (e) {
        console.error("Error dashboard", e);
    }
}
// =========================================================
// SISTEMA DE ALERTAS BONITAS (TOASTS)
// =========================================================
window.showToast = function (message, type = 'success') {
    // 1. Definir colores e iconos según el tipo
    const configs = {
        success: { icon: 'fa-check-circle', color: 'bg-green-500', title: '¡Éxito!' },
        error: { icon: 'fa-times-circle', color: 'bg-red-500', title: 'Error' },
        warning: { icon: 'fa-exclamation-triangle', color: 'bg-yellow-500', title: 'Atención' },
        info: { icon: 'fa-info-circle', color: 'bg-blue-500', title: 'Información' }
    };
    const style = configs[type] || configs.success;

    // 2. Crear el HTML del Toast
    const toastId = 'toast-' + Date.now();
    const html = `
        <div id="${toastId}" class="fixed top-5 right-5 z-[9999] flex items-center w-full max-w-xs p-4 space-x-4 text-white ${style.color} rounded-lg shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
            <div class="text-2xl"><i class="fas ${style.icon}"></i></div>
            <div class="flex-1">
                <p class="font-bold text-sm">${style.title}</p>
                <p class="text-xs opacity-90">${message}</p>
            </div>
            <button onclick="document.getElementById('${toastId}').remove()" class="text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
        </div>
    `;

    // 3. Insertar en el documento
    document.body.insertAdjacentHTML('beforeend', html);

    // 4. Animar entrada
    setTimeout(() => {
        const el = document.getElementById(toastId);
        if (el) el.classList.remove('translate-x-full');
    }, 10);

    // 5. Auto-eliminar a los 4 segundos
    setTimeout(() => {
        const el = document.getElementById(toastId);
        if (el) {
            el.classList.add('translate-x-full'); // Animar salida
            setTimeout(() => el.remove(), 300);   // Borrar del DOM
        }
    }, 4000);
};
// SISTEMA DE CARGA DINÁMICA DE MÓDULOS
window.loadModule = function (moduleName) {
    // 1. Limpiar pantalla
    const container = document.getElementById('main-content'); // O dynamicMenuContainer según tu HTML
    // container.innerHTML = '...cargando...';

    // 2. Cargar el JS correspondiente si no existe
    if (moduleName === 'cartera') {
        // Si ya cargamos el script, solo ejecutamos la vista
        if (window.renderCarteraView) window.renderCarteraView();
        else loadScript('assets/js/widgets/cartera.js', () => window.renderCarteraView());
    }
    else if (moduleName === 'cortes_pendientes') {
        if (window.renderCortesView) window.renderCortesView('cortes_pendientes');
        else loadScript('assets/js/widgets/cortes.js', () => window.renderCortesView('cortes_pendientes'));
    }
    else if (moduleName === 'reactivaciones') {
        if (window.renderCortesView) window.renderCortesView('reactivaciones');
        else loadScript('assets/js/widgets/cortes.js', () => window.renderCortesView('reactivaciones'));
    }
};

// Helper para cargar scripts bajo demanda
// --- SISTEMA DE CARGA DINÁMICA DE MÓDULOS (Versión Promesas) ---

// 1. Función para cargar scripts devolviendo una Promesa (Esto arregla tu error)
window.loadScript = function (url) {
    return new Promise((resolve, reject) => {
        // Verificar si ya existe para no cargarlo doble
        if (document.querySelector(`script[src="${url}"]`)) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = url;
        script.async = true;

        // Cuando termina de cargar, resuelve la promesa
        script.onload = () => resolve();

        // Si falla, la rechaza
        script.onerror = () => reject(new Error(`Error cargando script: ${url}`));

        document.body.appendChild(script);
    });
};

// 2. El Router de Módulos (Actualizado para usar la Promesa anterior)
window.loadModule = async function (moduleName) {
    // 1. Capturamos los contenedores
    const dashboard = document.getElementById('main-dashboard-content');
    const widgetArea = document.getElementById('main-content');

    if (!dashboard || !widgetArea) return;

    // --- LA MAGIA: OCULTAR TODO LO DEMÁS ---
    // Esto es lo que faltaba. Si estás en Soporte o RRHH, hay que cerrarlos primero.
    document.querySelectorAll('[id^="view-"]').forEach(el => {
        el.classList.add('hidden');
    });

    // --- ESCENARIO 1: VOLVER AL DASHBOARD ---
    if (moduleName === 'dashboard') {
        widgetArea.style.display = 'none';        // Apagar Widget
        dashboard.style.display = 'block';        // Prender Dashboard
        widgetArea.innerHTML = '';
        return;
    }

    // --- ESCENARIO 2: CARGAR UN WIDGET ---
    console.log(`🚀 Cargando módulo: ${moduleName}...`);

    dashboard.style.display = 'none';         // Ocultar Dashboard
    widgetArea.style.display = 'block';       // Mostrar zona de Widget

    // Spinner de carga limpio
    widgetArea.innerHTML = '<div class="flex justify-center items-center h-64"><i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i><span class="ml-3 text-gray-500">Cargando herramienta...</span></div>';

    try {
        // Router de Módulos
        if (moduleName === 'cartera') {
            await window.loadScript('assets/js/widgets/cartera.js');
            if (window.renderCarteraView) window.renderCarteraView();
        }
        else if (moduleName === 'cortes_pendientes') {
            await window.loadScript('assets/js/widgets/cortes.js');
            if (window.renderCortesView) window.renderCortesView('cortes_pendientes');
        }
        else if (moduleName === 'reactivaciones') {
            await window.loadScript('assets/js/widgets/cortes.js');
            if (window.renderCortesView) window.renderCortesView('reactivaciones');
        }
        else if (moduleName === 'messaging') {
            await window.loadScript('assets/js/widgets/messaging.js');
        }

    } catch (error) {
        console.error("Error cargando módulo:", error);
        widgetArea.innerHTML = `<div class="p-10 text-center text-red-500">Error: ${error.message}</div>`;
    }
};