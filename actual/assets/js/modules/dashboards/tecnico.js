/**
 * VISTA DE TÉCNICO V4 (Con Modales Interactivos)
 */
(async function () {
    console.log("🔧 Cargando Panel Técnico Interactivo...");

    const widgets = document.getElementById('dashboard-widgets');
    const main = document.getElementById('main-dashboard-content');
    const subtitle = document.getElementById('dashboard-subtitle');

    if (subtitle) subtitle.innerText = "Panel de Operaciones";

    // 1. STATS
    let stats = { my_tickets: 0, my_resolved: 0, my_inventory: 0 };
    try {
        const res = await fetch('api/admin/tech_stats.php');
        const data = await res.json();
        stats = data;
    } catch (e) { }

    // 2. WIDGETS (Con ONCLICK agregado)
    if (widgets) {
        widgets.innerHTML = `
            <div onclick="document.getElementById('tech-tickets-list').scrollIntoView({behavior: 'smooth'})" 
                 class="cursor-pointer bg-blue-600 rounded-xl p-6 text-white shadow-lg relative overflow-hidden group transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                    <i class="fas fa-tools text-8xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-blue-100 text-xs font-bold uppercase mb-1">Por Atender</p>
                    <h3 class="text-5xl font-bold">${stats.my_tickets}</h3>
                    <p class="text-xs mt-2 opacity-80">Ver lista abajo <i class="fas fa-arrow-down"></i></p>
                </div>
            </div>

            <div onclick="openResolvedHistory()" 
                 class="cursor-pointer bg-green-600 rounded-xl p-6 text-white shadow-lg relative overflow-hidden group transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                    <i class="fas fa-check-circle text-8xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-green-100 text-xs font-bold uppercase mb-1">Finalizados</p>
                    <h3 class="text-5xl font-bold">${stats.my_resolved}</h3>
                    <p class="text-xs mt-2 opacity-80">Click para ver historial <i class="fas fa-history"></i></p>
                </div>
            </div>

            <div onclick="openStockModal()" 
                 class="cursor-pointer bg-purple-600 rounded-xl p-6 text-white shadow-lg relative overflow-hidden group transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                    <i class="fas fa-boxes text-8xl"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-purple-100 text-xs font-bold uppercase mb-1">Mi Inventario</p>
                    <h3 class="text-5xl font-bold">${stats.my_inventory}</h3>
                    <p class="text-xs mt-2 opacity-80">Click para ver material <i class="fas fa-search"></i></p>
                </div>
            </div>
        `;
    }

    // 3. TABLA PRINCIPAL (Pendientes)
    if (main) {
        main.innerHTML = `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-clipboard-list text-blue-600"></i> Hoja de Ruta</h3>
                    <button onclick="location.reload()" class="p-2 text-gray-400 hover:text-blue-600 transition"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div id="tech-tickets-list" class="space-y-4">
                    <p class="text-center text-gray-400 py-6"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>
                </div>
            </div>
        `;
        loadPendingTickets();
    }
})();

// --- FUNCIONES AUXILIARES (Globales para que el onclick funcione) ---

async function loadPendingTickets() {
    try {
        const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
        const list = document.getElementById('tech-tickets-list');
        // Traemos todo lo asignado (filtrado por el PHP para no traer cerrados si no se pide)
        // Pero el PHP de get_all_tickets por defecto excluye cerrados si no pasamos status
        const res = await fetch(`api/admin/get_all_tickets.php?assigned_to=${user.id}`);
        const tickets = await res.json();

        list.innerHTML = '';
        const pending = tickets.filter(t => t.status !== 'RESUELTO' && t.status !== 'CERRADO');

        if (pending.length === 0) {
            list.innerHTML = `<div class="p-6 text-center text-gray-400 border border-dashed rounded-lg">No tienes pendientes.</div>`;
        } else {
            pending.forEach(t => {
                const isUrgent = t.priority === 'URGENTE';
                list.innerHTML += `
                    <div class="relative bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition ${isUrgent ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-blue-500'}">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-mono text-[10px] text-gray-500 bg-gray-100 px-2 rounded">#${t.id}</span>
                            ${isUrgent ? '<span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 rounded animate-pulse">URGENTE</span>' : ''}
                        </div>
                        <h4 class="font-bold text-gray-800 text-lg mb-1">${t.subject}</h4>
                        <p class="text-sm text-gray-600 mb-3"><i class="fas fa-user text-gray-400"></i> ${t.client_name}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="window.open('https://maps.google.com/?q=${encodeURIComponent(t.client_address || '')}', '_blank')" class="py-2 bg-gray-50 text-gray-600 rounded-lg text-sm font-bold border">Mapa</button>
                            <button onclick="openTicketChat(${t.id})" class="py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow">Gestionar</button>
                        </div>
                    </div>`;
            });
        }
    } catch (e) { console.error(e); }
}

// ==========================================
// MODALES ESTILIZADOS (DISEÑO LIMPIO)
// ==========================================

// --- 1. HISTORIAL DE TRABAJOS (Estilo Tarjeta Blanca) ---
window.openResolvedHistory = async function () {
    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
    const modalId = 'modal-history';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();

    // Estructura limpia tipo "Card"
    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
                
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="font-bold text-green-700 text-lg flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Mis Trabajos Realizados
                    </h3>
                    <button onclick="document.getElementById('${modalId}').remove()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="history-content" class="overflow-y-auto p-0 bg-gray-50 flex-1">
                    <div class="py-8 text-center text-gray-400">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando historial...
                    </div>
                </div>
            </div>
        </div>
    `);

    try {
        // Cargar Resueltos y Cerrados
        const [res1, res2] = await Promise.all([
            fetch(`api/admin/get_all_tickets.php?assigned_to=${user.id}&status=RESUELTO`),
            fetch(`api/admin/get_all_tickets.php?assigned_to=${user.id}&status=CERRADO`)
        ]);

        const data1 = await res1.json();
        const data2 = await res2.json();
        // Unir y ordenar por fecha (más reciente primero)
        const allResolved = [...data1, ...data2].sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at));

        const container = document.getElementById('history-content');
        container.innerHTML = '';

        if (allResolved.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <i class="fas fa-clipboard-check text-4xl mb-3 opacity-20"></i>
                    <p class="text-sm">Aún no hay trabajos finalizados.</p>
                </div>`;
            return;
        }

        // Renderizar lista limpia
        allResolved.forEach(t => {
            const dateObj = new Date(t.updated_at || t.created_at);
            const dateStr = dateObj.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + dateObj.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

            container.innerHTML += `
                <div class="bg-white border-b border-gray-100 p-4 hover:bg-gray-50 transition last:border-0">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-xs font-bold text-gray-400">#${t.id}</span>
                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">${dateStr}</span>
                    </div>
                    <h4 class="font-bold text-gray-800 text-sm mb-0.5">${t.subject}</h4>
                    <p class="text-xs text-gray-500 uppercase">${t.client_name || 'Cliente'}</p>
                </div>
            `;
        });

    } catch (e) {
        document.getElementById('history-content').innerHTML = '<p class="text-center text-red-400 py-6">Error al cargar datos.</p>';
    }
};

// --- 2. MI INVENTARIO (Estilo Tarjeta Blanca) ---
window.openStockModal = async function () {
    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
    const modalId = 'modal-stock';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();

    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
                
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="font-bold text-purple-700 text-lg flex items-center gap-2">
                        <i class="fas fa-boxes"></i> Mi Inventario
                    </h3>
                    <button onclick="document.getElementById('${modalId}').remove()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="stock-content" class="overflow-y-auto p-0 bg-gray-50 flex-1">
                    <div class="py-8 text-center text-gray-400">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando material...
                    </div>
                </div>
            </div>
        </div>
    `);

    try {
        const res = await fetch(`api/admin/get_inventory.php?assigned_to=${user.id}`);
        const items = await res.json();
        const container = document.getElementById('stock-content');
        container.innerHTML = '';

        if (!items || items.error || items.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <i class="fas fa-box-open text-4xl mb-3 opacity-20"></i>
                    <p class="text-sm">No tienes material asignado.</p>
                </div>`;
            return;
        }

        // Renderizar lista limpia
        items.forEach(i => {
            // Usamos los campos correctos de tu XML
            const titulo = i.brand && i.model ? `${i.brand} ${i.model}` : (i.serial_number || 'Item Desconocido');
            const serie = i.serial_number || i.mac_address || 'S/N';

            container.innerHTML += `
                <div class="bg-white border-b border-gray-100 p-4 hover:bg-purple-50 transition last:border-0 flex items-center gap-4">
                    <div class="bg-purple-100 text-purple-700 w-10 h-10 flex items-center justify-center rounded-lg font-bold text-sm shadow-sm">
                        ${i.quantity || 1}
                    </div>
                    
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm">${titulo}</h4>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] bg-gray-100 px-1.5 rounded text-gray-500 font-mono border border-gray-200">${serie}</span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wide">${i.type || 'MATERIAL'}</span>
                        </div>
                    </div>
                </div>
            `;
        });

    } catch (e) {
        document.getElementById('stock-content').innerHTML = '<p class="text-center text-red-400 py-6">Error de conexión.</p>';
        console.error(e);
    }
};