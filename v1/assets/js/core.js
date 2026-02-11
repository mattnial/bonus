// ARCHIVO: assets/js/core.js

// --- VARIABLES GLOBALES ---
let CURRENT_CLIENT_ID = null;
let CURRENT_TICKET_ID = null;
let CURRENT_STAFF_ID = null;

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

// --- RENDERIZADO DE MENÚ LATERAL (ESTILO PRO) ---
async function renderDynamicMenu() {
    const container = document.getElementById('dynamicMenuContainer');
    if (!container) return;

    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
    if (!user) return;

    try {
        const res = await fetch(`${API_URL}/admin/get_my_menu.php?id=${user.id}`);
        const menuItems = await res.json();

        container.innerHTML = '';

        if (menuItems.length === 0) {
            container.innerHTML = '<div class="p-4 text-center text-xs text-slate-500 border border-dashed border-slate-700 rounded-lg">Sin accesos asignados.</div>';
            return;
        }

        menuItems.forEach(item => {
            const btn = document.createElement('button');

            // ESTILOS BASE (Botón inactivo)
            // Usamos clases de Tailwind para transiciones suaves y colores modernos
            const baseClasses = "w-full flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 rounded-xl transition-all duration-300 group border border-transparent";

            // ESTILOS ACTIVO (Degradado azul brillante + Sombra)
            const activeClasses = "bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/20 border-blue-400/20 font-bold translate-x-1";

            btn.className = baseClasses;
            btn.id = `menu-btn-${item.view_name}`;

            btn.onclick = () => {
                // 1. Resetear todos los botones
                document.querySelectorAll('#dynamicMenuContainer button').forEach(b => {
                    b.className = baseClasses; // Volver al estado base
                    // Quitar icono activo
                    const icon = b.querySelector('i');
                    if (icon) icon.className = icon.className.replace('text-white', 'text-slate-400');
                });

                // 2. Activar el actual
                btn.className = baseClasses.replace('hover:bg-slate-800/60', '') + ' ' + activeClasses;

                // 3. Cerrar sidebar en móvil
                if (window.innerWidth < 1024) document.getElementById('sidebar').classList.add('-translate-x-full');

                switchView(item.view_name);
            };

            // HTML del interior del botón
            btn.innerHTML = `
                <i class="${item.icon} w-6 text-center text-lg transition-colors duration-300 group-hover:text-blue-400"></i> 
                <span class="tracking-wide">${item.label}</span>
                <i class="fas fa-chevron-right ml-auto text-[10px] opacity-0 group-hover:opacity-100 transition-opacity -translate-x-2 group-hover:translate-x-0"></i>
            `;
            container.appendChild(btn);
        });

        // Activar Home por defecto
        setTimeout(() => {
            const homeBtn = document.getElementById('menu-btn-home');
            if (homeBtn && !document.querySelector('.bg-gradient-to-r')) homeBtn.click();
        }, 100);

    } catch (e) {
        console.error("Error menú", e);
        container.innerHTML = '<p class="text-red-400 text-xs p-4">Error de conexión</p>';
    }
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
    // Ocultar todas las vistas
    document.querySelectorAll('[id^="view-"]').forEach(el => {
        el.classList.add('hidden');
    });

    // Mostrar la seleccionada
    const target = document.getElementById('view-' + viewName);
    if (target) target.classList.remove('hidden');

    // Hooks
    if (viewName === 'tickets' && typeof loadGlobalTickets === 'function') loadGlobalTickets();
    if (viewName === 'rrhh' && typeof loadRRHH === 'function') loadRRHH();
    if (viewName === 'billing') {
        if (typeof loadDebtors === 'function') loadDebtors();
        const stockView = document.getElementById('view-stock');
        if (stockView && !stockView.classList.contains('hidden')) {
            if (typeof loadInventory === 'function') { loadInventory(); if (typeof loadInventoryStats === 'function') loadInventoryStats(); }
        }
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