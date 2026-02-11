/**
 * WIDGET: EXTENSIÓN DE FUNCIONALIDAD (Pagos, Promesas, Alertas)
 * Versión Limpia: Sin alerts nativos.
 */

console.log("🧩 Widget Cobranzas: Activado con Notificaciones Modernas");

window.currentTab = 'all'; // Default

window.initCobranzas = function (tabName) {
    window.currentTab = tabName || 'all';
    if (typeof loadDebtorsList === 'function') loadDebtorsList();
};

if (!window.notificar) {
    window.notificar = {
        exito: (msg) => typeof showToast === 'function' ? showToast(msg, 'success') : alert(msg),
        error: (msg) => typeof showToast === 'function' ? showToast(msg, 'error') : alert(msg),
        confirmar: (title, msg, callback) => {
            if (confirm(title + "\n" + msg)) callback();
        }
    };
}

// 1. CARGA DE TABLA
window.loadDebtorsList = async function () {
    const list = document.getElementById('debtors-list');
    list.innerHTML = '<tr><td colspan="6" class="text-center py-20"><i class="fas fa-spinner fa-spin text-indigo-600 text-2xl"></i></td></tr>';
    try {
        const res = await fetch(`api/admin/get_debtors.php?filter=${currentTab}`);
        const d = await res.json();
        list.innerHTML = d.length === 0 ? '<tr><td colspan="6" class="text-center py-12 text-gray-400">Sin registros.</td></tr>' : '';

        d.forEach(c => {
            const safeName = c.name.replace(/'/g, "").replace(/"/g, "");
            const badge = currentTab === 'promises' ? `<span class="bg-green-100 text-green-700 px-2 font-bold text-[10px] rounded border border-green-200">PROMESAS: ${c.promise_date}</span>` : (c.months_owed >= 3 ? '<span class="bg-red-100 text-red-700 px-2 font-bold text-[10px] rounded">CRÍTICO</span>' : '<span class="bg-yellow-100 text-yellow-700 px-2 font-bold text-[10px] rounded">ALERTA</span>');

            const action = currentTab === 'promises'
                ? `openBrokenPromiseModal(${c.id}, '${safeName}', '${c.total_debt}')`
                : `openManagementModal(${c.id}, '${safeName}', '${c.total_debt}')`;

            list.innerHTML += `<tr class="hover:bg-gray-50 border-b group"><td class="px-6 py-4"><div class="font-bold text-gray-800 text-sm">${c.name}</div><div class="text-[10px] text-gray-400 font-mono">${c.cedula}</div></td><td class="px-6 py-4 text-center">${badge}</td><td class="px-6 py-4 font-black text-red-600">$${c.total_debt}</td><td class="px-6 py-4"><a href="#" class="text-green-600 font-bold text-xs">Chat</a></td><td class="px-6 py-4"><button onclick="viewClientHistory(${c.id}, '${safeName}')" class="text-indigo-600 font-bold hover:underline text-[10px] truncate w-24 text-left">${c.last_note || 'Ver'}</button></td><td class="px-6 py-4 text-right"><button onclick="${action}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-[10px] font-bold shadow hover:bg-indigo-700 uppercase transition transform active:scale-95">${currentTab === 'promises' ? 'RESOLVER' : 'GESTIONAR'}</button></td></tr>`;
        });
    } catch (e) {
        list.innerHTML = '<tr><td colspan="6" class="text-center text-red-500">Error al cargar datos.</td></tr>';
    }
};

// 2. MODAL RESOLVER PROMESA
window.openBrokenPromiseModal = function (id, name, debt) {
    const modalId = 'modal-cobranzas-manage';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();
    const numericDebt = parseFloat(debt.replace(/[^0-9.-]+/g, ""));

    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden border-t-4 border-green-600">
                <div class="bg-white px-5 py-4 flex justify-between items-center border-b">
                    <h3 class="font-bold text-gray-800 uppercase text-xs">Resolver Convenio: ${name}</h3>
                    <button onclick="document.getElementById('${modalId}').remove()"><i class="fas fa-times"></i></button>
                </div>
                <div id="step-1" class="p-6 text-center">
                    <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Monto Pendiente</p>
                    <p class="text-4xl font-black text-green-600 mb-8">$${numericDebt.toFixed(2)}</p>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="showPaymentForm(${numericDebt})" class="bg-blue-600 text-white p-4 rounded-xl shadow-lg flex flex-col items-center gap-2 transition hover:bg-blue-700">
                            <i class="fas fa-file-invoice-dollar text-2xl"></i><span class="font-bold text-[10px] uppercase">Informar Pago</span>
                        </button>
                        <button onclick="saveBrokenAction(${id}, 'BROKEN_PROMISE')" class="bg-red-500 text-white p-4 rounded-xl shadow-lg flex flex-col items-center gap-2 transition hover:bg-red-600">
                            <i class="fas fa-heart-broken text-2xl"></i><span class="font-bold text-[10px] uppercase">No cumplió</span>
                        </button>
                    </div>
                </div>
                <div id="step-payment" class="p-6 hidden">
                    <form id="form-pago-widget">
                        <input type="hidden" id="total_debt_val" value="${numericDebt}">
                        <div class="mb-4"><label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Método</label>
                            <select id="pay_method" class="w-full border rounded p-2 text-sm bg-white"><option value="Oficinas">🏢 Oficinas</option><option value="Transferencia">📱 Transferencia</option></select>
                        </div>
                        <div class="mb-4"><label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Monto</label>
                            <input type="number" id="pay_amount" step="0.01" value="${numericDebt}" class="w-full border rounded p-2 font-bold text-lg text-blue-600 outline-none" oninput="validatePartialPayment(this.value, ${numericDebt})">
                        </div>
                        <div id="partial_logic_container" class="hidden mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg animate-fade-in">
                            <p class="text-[10px] font-bold text-orange-700 uppercase mb-2">Abono Parcial Detectado</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div><label class="text-[9px] font-bold uppercase">Pagos restantes</label><input type="number" id="installments" min="1" class="w-full border rounded p-1 text-xs"></div>
                                <div><label class="text-[9px] font-bold uppercase">Próxima Fecha</label><input type="date" id="next_date" class="w-full border rounded p-1 text-xs"></div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="showStep1()" class="flex-1 bg-gray-100 py-3 rounded text-xs uppercase font-bold">Atrás</button>
                            <button type="submit" class="flex-[2] bg-green-600 text-white font-black py-3 rounded text-xs uppercase shadow-lg transition hover:bg-green-700">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`);

    document.getElementById('form-pago-widget').onsubmit = (e) => { e.preventDefault(); submitPayment(id); };
};

// 3. LÓGICA DE PAGOS
window.showPaymentForm = () => { document.getElementById('step-1').classList.add('hidden'); document.getElementById('step-payment').classList.remove('hidden'); };
window.showStep1 = () => { document.getElementById('step-payment').classList.add('hidden'); document.getElementById('step-1').classList.remove('hidden'); };
window.validatePartialPayment = (val, total) => {
    const isP = parseFloat(val) < total;
    const box = document.getElementById('partial_logic_container');
    if (isP) { box.classList.remove('hidden'); document.getElementById('next_date').required = true; }
    else { box.classList.add('hidden'); document.getElementById('next_date').required = false; }
};

window.submitPayment = async (id) => {
    const total = parseFloat(document.getElementById('total_debt_val').value);
    const amount = parseFloat(document.getElementById('pay_amount').value);
    const isPartial = amount < total;
    const data = { client_id: id, type: 'YA_PAGO', amount_paid: amount, method: document.getElementById('pay_method').value, is_total: !isPartial, next_promise: isPartial ? document.getElementById('next_date').value : null, installments: isPartial ? document.getElementById('installments').value : 1 };

    try {
        const res = await fetch('api/admin/save_cobranza.php', { method: 'POST', body: JSON.stringify(data) });
        const r = await res.json();
        if (r.success) {
            document.getElementById('modal-cobranzas-manage').remove();
            loadDebtorsList();
            updateStats();
            // ALERTA MODERNA AQUÍ:
            window.notificar.exito(isPartial ? "Abono registrado correctamente." : "Pago total completado satisfactoriamente.");
        } else {
            window.notificar.error("Error: " + r.message);
        }
    } catch (e) {
        window.notificar.error("Error de comunicación con el servidor.");
    }
};

window.saveBrokenAction = async function (id, type) {
    // REEMPLAZO DE CONFIRM POR NOTIFICAR.CONFIRMAR
    window.notificar.confirmar(
        "¿Confirmar Incumplimiento?",
        "El cliente perderá su protección y regresará a la lista de corte inmediatamente.",
        async () => {
            try {
                const res = await fetch('api/admin/save_cobranza.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        client_id: id,
                        type: type,
                        notes: '🚨 CONVENIO NO CUMPLIDO. PROCEDER AL CORTE.',
                        contact_method: 'SISTEMA'
                    })
                });
                if ((await res.json()).success) {
                    document.getElementById('modal-cobranzas-manage').remove();
                    loadDebtorsList();
                    updateStats();
                    window.notificar.exito("Incumplimiento registrado. Cliente enviado a corte.");
                }
            } catch (e) {
                window.notificar.error("Error al procesar la solicitud.");
            }
        }
    );
};

// 4. HISTORIAL
window.viewClientHistory = async function (clientId, clientName) {
    const modalId = 'modal-cobranzas-history';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();
    document.body.insertAdjacentHTML('beforeend', `<div id="${modalId}" class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"><div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden"><div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center"><h3 class="font-bold text-lg">Historial: ${clientName}</h3><button onclick="document.getElementById('${modalId}').remove()"><i class="fas fa-times"></i></button></div><div class="p-6 max-h-[500px] overflow-y-auto" id="history-content"><div class="text-center py-10"><i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i></div></div></div></div>`);

    try {
        const res = await fetch(`api/admin/get_client_history.php?client_id=${clientId}`);
        const h = await res.json();
        let html = h.length === 0 ? `<p class="text-center text-gray-400">Sin historial.</p>` : `<div class="space-y-4">`;
        h.forEach(i => {
            const date = new Date(i.created_at).toLocaleString('es-EC');
            let style = 'bg-white border-gray-200 border-l-4 border-l-gray-400';
            if (i.type === 'YA_PAGO') style = 'bg-blue-50 border-blue-200 border-l-4 border-l-blue-500';
            if (i.type === 'CONVENIO_PAGO') style = 'bg-green-50 border-green-200 border-l-4 border-l-green-500';
            if (i.type === 'BROKEN_PROMISE') style = 'bg-red-50 border-red-200 border-l-4 border-l-red-600 shadow-md font-bold text-red-800';

            html += `<div class="p-4 rounded-r-lg border shadow-sm ${style}"><div class="flex justify-between text-[10px] uppercase font-bold mb-1 opacity-70"><span>${i.type.replace(/_/g, ' ')}</span><span>${date}</span></div><p class="text-xs leading-relaxed">${i.notes}</p></div>`;
        });
        document.getElementById('history-content').innerHTML = html + `</div>`;
    } catch (e) {
        document.getElementById('history-content').innerHTML = "Error al obtener historial.";
    }
};