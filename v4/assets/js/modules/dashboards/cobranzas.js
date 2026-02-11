/**
 * CORE: COBRANZAS.JS - VERSIÓN BLINDADA (V17)
 */

// --- 1. PROTECCIÓN DE VARIABLES GLOBALES ---
window.currentTab = window.currentTab || 'pending';
window.currentClient = window.currentClient || {};

// Protección de Plantillas (Evita error de redeclaración de const)
if (typeof window.COBRO_TEMPLATES === 'undefined') {
    window.COBRO_TEMPLATES = {
        gentil: "Estimado/a {cliente}, notamos un saldo de ${deuda}. Le invitamos a cancelar.",
        urgente: "AVISO DE SUSPENSIÓN. Su cuenta presenta mora crítica.",
    };
}

// 2. INICIALIZADOR
(async function () {
    const main = document.getElementById('main-dashboard-content');
    const subtitle = document.getElementById('dashboard-subtitle');
    if (subtitle) subtitle.innerText = "Gestión de Recuperación de Cartera";

    // Definimos la función updateStats en window para que sea accesible globalmente
    window.updateStats = async function () {
        const w = document.getElementById('dashboard-widgets');
        if (!w) return;
        try {
            const res = await fetch('api/admin/cobranzas_stats.php');
            const s = await res.json();
            w.innerHTML = `
                <div class="bg-white rounded-xl p-6 shadow-sm border border-red-100 flex justify-between"><div><p class="text-red-500 text-xs font-bold uppercase">Vencido</p><h3 class="text-2xl font-black text-gray-800">$${s.total_debt}</h3></div><div class="bg-red-50 text-red-500 w-12 h-12 rounded-lg flex items-center justify-center text-xl"><i class="fas fa-hand-holding-usd"></i></div></div>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-green-200 flex justify-between"><div><p class="text-green-600 text-xs font-bold uppercase">Recuperado</p><h3 class="text-2xl font-black text-gray-800">$${s.recovered_debt}</h3></div><div class="bg-green-50 text-green-600 w-12 h-12 rounded-lg flex items-center justify-center text-xl"><i class="fas fa-check-double"></i></div></div>
                <div class="bg-gradient-to-r from-indigo-700 to-indigo-900 rounded-xl p-6 text-white shadow-lg col-span-1 md:col-span-2 flex justify-between"><div><h3 class="font-bold text-lg">Convenios</h3><p class="text-indigo-100 text-sm">Hoy: <b>${s.agreements_today}</b></p></div><button onclick="loadDebtorsList(); updateStats();" class="bg-white/20 border border-white/30 text-white py-2 px-4 rounded-lg font-bold text-sm hover:bg-white/30">Refrescar</button></div>`;
        } catch (e) { console.error("Error stats", e); }
    };

    await window.updateStats();

    if (main) {
        main.innerHTML = `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[500px]">
                <div class="flex border-b border-gray-200">
                    <button onclick="switchDebtTab('pending')" id="tab-pending" class="flex-1 py-4 text-sm font-bold text-red-600 border-b-2 border-red-600 bg-red-50 transition uppercase">Por Gestionar</button>
                    <button onclick="switchDebtTab('promises')" id="tab-promises" class="flex-1 py-4 text-sm font-bold text-gray-500 hover:text-green-600 border-b-2 border-transparent hover:bg-green-50 transition uppercase">Promesas Vigentes</button>
                </div>
                <div class="px-6 py-4 border-b bg-white flex justify-between items-center sticky top-0 z-10">
                    <h3 class="font-bold text-red-700">🚨 Listado de Clientes</h3>
                    <input type="text" id="searchDebtor" onkeyup="searchInTable()" placeholder="Buscar..." class="px-4 py-2 border rounded-lg text-sm w-64 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-gray-100 text-gray-500 font-bold uppercase text-[10px]"><tr><th class="px-6 py-3">Cliente</th><th class="px-6 py-3 text-center">Estado</th><th class="px-6 py-3">Deuda</th><th class="px-6 py-3">Contacto</th><th class="px-6 py-3">Historial</th><th class="px-6 py-3 text-right">Acción</th></tr></thead><tbody id="debtors-list" class="divide-y divide-gray-100 bg-white"></tbody></table></div>
            </div>`;
        setTimeout(() => switchDebtTab(window.currentTab), 100);
    }
})();

// --- FUNCIONES CORE ---
window.switchDebtTab = (t) => {
    window.currentTab = t;
    const p = document.getElementById('tab-pending'), m = document.getElementById('tab-promises');
    if (!p) return;
    if (t === 'pending') { p.className = "flex-1 py-4 text-sm font-bold text-red-600 border-b-2 border-red-600 bg-red-50 transition uppercase"; m.className = "flex-1 py-4 text-sm font-bold text-gray-500 hover:text-green-600 border-b-2 border-transparent transition uppercase"; }
    else { p.className = "flex-1 py-4 text-sm font-bold text-gray-500 hover:text-red-600 border-b-2 border-transparent transition uppercase"; m.className = "flex-1 py-4 text-sm font-bold text-green-600 border-b-2 border-green-600 bg-green-50 transition uppercase"; }
    loadDebtorsList();
};

window.loadDebtorsList = async function () {
    const list = document.getElementById('debtors-list');
    if (!list) return;
    list.innerHTML = '<tr><td colspan="6" class="text-center py-10"><i class="fas fa-spinner fa-spin text-indigo-600 text-2xl"></i></td></tr>';
    try {
        const res = await fetch(`api/admin/get_debtors.php?filter=${window.currentTab}`);
        const debtors = await res.json();
        list.innerHTML = debtors.length === 0 ? '<tr><td colspan="6" class="text-center py-12 text-gray-400">Sin datos.</td></tr>' : '';

        debtors.forEach(c => {
            const safeName = c.name.replace(/'/g, "").replace(/"/g, "");
            const badge = window.currentTab === 'promises' ? `<span class="bg-green-100 text-green-700 px-2 font-bold text-[10px] rounded">HASTA: ${c.promise_date}</span>` : (c.months_owed >= 3 ? '<span class="bg-red-100 text-red-700 px-2 font-bold text-[10px] rounded">CRÍTICO</span>' : '<span class="bg-yellow-100 text-yellow-700 px-2 font-bold text-[10px] rounded">ALERTA</span>');

            const action = window.currentTab === 'promises'
                ? `openBrokenPromiseModal(${c.id}, '${safeName}', '${c.total_debt}')`
                : `openManagementModal(${c.id}, '${safeName}', '${c.total_debt}')`;

            list.innerHTML += `<tr class="hover:bg-gray-50 border-b"><td class="px-6 py-4"><div class="font-bold text-gray-800 text-sm">${c.name}</div><div class="text-[10px] text-gray-400">${c.cedula}</div></td><td class="px-6 py-4 text-center">${badge}</td><td class="px-6 py-4 font-black text-red-600">$${c.total_debt}</td><td class="px-6 py-4"><a href="https://wa.me/593${c.phone.replace(/\D/g, '').substr(-9)}" target="_blank" class="text-green-600 font-bold text-xs"><i class="fab fa-whatsapp"></i> Chat</a></td><td class="px-6 py-4"><button onclick="viewClientHistory(${c.id}, '${safeName}')" class="text-indigo-600 font-bold hover:underline text-[10px] truncate max-w-[120px]">${c.last_note || 'Ver'}</button></td><td class="px-6 py-4 text-right"><button onclick="${action}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-[10px] font-bold shadow hover:bg-indigo-700 uppercase">GESTIONAR</button></td></tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="6" class="text-center text-red-500">Error.</td></tr>'; }
};

window.openManagementModal = function (clientId, clientName, debt) {
    window.currentClient = { id: clientId, name: clientName, debt: debt };
    const modalId = 'modal-cobranzas-manage';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();
    document.body.insertAdjacentHTML('beforeend', `<div id="${modalId}" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"><div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden"><div class="bg-indigo-600 text-white px-6 py-4 flex justify-between items-center"><h3 class="font-bold">Gestionar: ${clientName}</h3><button onclick="document.getElementById('${modalId}').remove()"><i class="fas fa-times"></i></button></div><div class="p-6"><div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg mb-6 border"><div><p class="text-xs font-black uppercase">Valor</p><p class="text-2xl font-black text-red-600">$${debt}</p></div></div><form id="form-gestion"><div class="grid grid-cols-2 gap-4 mb-4"><div><label class="text-[10px] font-black text-gray-400 uppercase">Medio</label><select id="contactMethod" class="w-full border rounded p-2 text-sm"><option value="LLAMADA">Llamada</option><option value="WHATSAPP">WhatsApp</option><option value="EMAIL">Email</option></select></div><div><label class="text-[10px] font-black text-gray-400 uppercase">Acuerdo</label><select id="resultType" class="w-full border rounded p-2 text-sm"><option value="LLAMADA_NO_CONTESTA">Informativo</option><option value="CONVENIO_PAGO">Generar Convenio</option></select></div></div><div id="convBox" class="hidden mb-4 p-3 bg-yellow-50 rounded border"><label class="text-[10px] font-black text-yellow-600 uppercase">Protección hasta:</label><input type="date" id="promiseDate" class="w-full border rounded p-2 text-sm font-bold bg-white"></div><textarea id="notes" class="w-full border rounded p-3 text-sm h-20" placeholder="Detalles..."></textarea><button type="submit" class="w-full bg-indigo-600 text-white font-black py-3 rounded-lg mt-4 shadow uppercase">Guardar</button></form></div></div></div>`);

    document.getElementById('resultType').onchange = (e) => document.getElementById('convBox').classList.toggle('hidden', e.target.value !== 'CONVENIO_PAGO');

    document.getElementById('form-gestion').onsubmit = async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button');
        btn.disabled = true;
        const data = { client_id: clientId, type: document.getElementById('resultType').value, contact_method: document.getElementById('contactMethod').value, notes: document.getElementById('notes').value, promise_date: document.getElementById('promiseDate') ? document.getElementById('promiseDate').value : null };
        try {
            const res = await fetch('api/admin/save_cobranza.php', { method: 'POST', body: JSON.stringify(data) });
            if ((await res.json()).success) { document.getElementById('modal-cobranzas-manage').remove(); loadDebtorsList(); updateStats(); }
        } catch (err) { alert("Error"); btn.disabled = false; }
    };
};

window.searchInTable = () => {
    const v = document.getElementById('searchDebtor').value.toUpperCase();
    const rows = document.getElementById('debtors-list').getElementsByTagName('tr');
    for (let r of rows) { const t = r.getElementsByTagName('td')[0]; if (td) r.style.display = td.innerText.toUpperCase().includes(v) ? "" : "none"; }
};