/**
 * MÓDULO: GESTIÓN DE CORTES
 * Funciones: Lista de Corte, Reactivación, Cálculo Pasivo ($6.25)
 */

if (!window.notificar) {
    window.notificar = {
        exito: (msg) => typeof showToast === 'function' ? showToast(msg, 'success') : alert(msg),
        error: (msg) => typeof showToast === 'function' ? showToast(msg, 'error') : alert(msg),
        confirmar: (title, msg, callback) => {
            if (confirm(title + "\n" + msg)) callback();
        }
    };
}

window.renderCortesView = async function (viewType) {
    const container = document.getElementById('mainContent');
    const isCutView = viewType === 'cortes_pendientes';
    const title = isCutView ? '✂️ Lista de Cortes Pendientes' : '🔋 Reactivación de Servicios';
    const color = isCutView ? 'red' : 'green';

    container.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up">
            <h2 class="text-xl font-bold text-${color}-600 mb-6 border-b pb-2">${title}</h2>
            
            ${!isCutView ? `
            <div class="flex gap-3 mb-4 text-sm">
                <select id="filter_year" class="border p-2 rounded"><option>2025</option></select>
                <select id="filter_month" class="border p-2 rounded"><option>Enero</option></select>
                <button onclick="applyFilters()" class="bg-slate-100 px-3 py-2 rounded hover:bg-slate-200">Filtrar</button>
            </div>` : ''}

            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="p-3">Cliente</th>
                        <th class="p-3">Estado / Fecha</th>
                        <th class="p-3 text-right">Deuda Global</th>
                        <th class="p-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody id="cortes-list-body"></tbody>
            </table>
        </div>
    `;

    if (isCutView) loadCortesList();
    else loadReactivacionesList();
};

async function loadCortesList() {
    // Lógica para cargar clientes con collection_stage=4 o deuda > 2 meses
    const tbody = document.getElementById('cortes-list-body');
    const res = await fetch('api/admin/get_cut_candidates.php');
    const list = await res.json();

    list.forEach(c => {
        const btn = c.cut_request === 'pendiente_corte'
            ? `<span class="text-orange-500 font-bold text-xs animate-pulse">⏳ Procesando...</span>`
            : `<button onclick="orderCut(${c.id})" class="bg-red-600 text-white px-4 py-2 rounded shadow text-xs font-bold hover:bg-red-700">CORTAR SERVICIO</button>`;

        tbody.innerHTML += `
            <tr class="border-b">
                <td class="p-3 font-bold">${c.name}</td>
                <td class="p-3 text-red-500 text-sm">Vencido: ${c.months_owed} meses</td>
                <td class="p-3 text-right font-black">$${c.total_debt}</td>
                <td class="p-3 text-center">${btn}</td>
            </tr>`;
    });
}

async function loadReactivacionesList() {
    const tbody = document.getElementById('cortes-list-body');
    const res = await fetch('api/admin/get_suspended_clients.php');
    const list = await res.json();

    list.forEach(c => {
        // Cálculo visual de deuda pasiva
        const passiveDebt = parseFloat(c.passive_debt || 0);
        const total = parseFloat(c.total_debt) + passiveDebt;

        tbody.innerHTML += `
            <tr class="border-b hover:bg-green-50">
                <td class="p-3">
                    <div class="font-bold">${c.name}</div>
                    <div class="text-xs text-gray-400">Cortado: ${c.cut_date}</div>
                </td>
                <td class="p-3">
                    ${c.equipment_returned == 1
                ? '<span class="badge bg-gray-200 text-gray-600 text-[10px]">Equipos Devueltos</span>'
                : '<span class="badge bg-blue-100 text-blue-600 text-[10px] animate-pulse">Generando +$6.25/mes</span>'}
                </td>
                <td class="p-3 text-right">
                    <div class="text-xs text-gray-400 line-through">$${c.total_debt}</div>
                    <div class="font-black text-green-700 text-lg">$${total.toFixed(2)}</div>
                    <div class="text-[9px] text-red-400">(Incluye $${passiveDebt} mantenimiento)</div>
                </td>
                <td class="p-3 text-center flex flex-col gap-1">
                    <button onclick="reactivateService(${c.id})" class="bg-green-500 text-white py-1 px-3 rounded text-xs font-bold hover:bg-green-600">REACTIVAR</button>
                    ${c.equipment_returned == 0 ? `<button onclick="returnEquip(${c.id})" class="text-gray-400 text-[10px] hover:text-blue-500 underline">Ya devolvió equipos</button>` : ''}
                </td>
            </tr>`;
    });
}

// --- ACCIONES ---
window.orderCut = async (id) => { /* Fetch a toggle_service.php action:cut */ };
window.reactivateService = async (id) => { /* Modal para nota y Fetch action:activate */ };