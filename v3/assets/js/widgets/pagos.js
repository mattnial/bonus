/** WIDGET: FORMULARIO DE PAGOS **/
// Nota: No declaramos currentClient aquí, usamos la del archivo principal si fuera necesaria, o pasamos parámetros.

window.showPaymentForm = function (totalDebt) {
    document.getElementById('step-1').classList.add('hidden');
    const container = document.getElementById('step-payment');
    container.classList.remove('hidden');

    // Obtenemos el ID del cliente del botón "No Cumplió" que ya está renderizado
    // Truco para no depender de variables globales complejas en este scope
    const btnBroken = document.querySelector('button[onclick^="saveBrokenAction"]');
    const clientId = btnBroken ? btnBroken.getAttribute('onclick').match(/\d+/)[0] : 0;

    container.innerHTML = `
        <form id="form-pago-widget">
            <div class="mb-4"><label class="text-[10px] font-black text-gray-400 uppercase">Método</label>
                <select id="pay_method" class="w-full border rounded p-2 text-sm"><option value="Pago en Oficinas">🏢 Oficinas</option><option value="Transferencia">📱 Transferencia</option><option value="Cheque">✍️ Cheque</option></select></div>
            <div class="mb-4"><label class="text-[10px] font-black text-gray-400 uppercase">Monto</label>
                <input type="number" id="pay_amount" step="0.01" value="${totalDebt}" class="w-full border rounded p-2 font-bold text-lg text-blue-600 outline-none" oninput="validatePartialPayment(this.value, ${totalDebt})"></div>
            
            <div id="partial_logic_container" class="hidden mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg animate-fade-in">
                <p class="text-[10px] font-bold text-orange-700 uppercase mb-2"><i class="fas fa-exclamation-triangle"></i> Pago Parcial</p>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="text-[9px] font-bold text-gray-500 uppercase">Pagos restantes</label><input type="number" id="installments" min="1" class="w-full border rounded p-1 text-xs"></div>
                    <div><label class="text-[9px] font-bold text-gray-500 uppercase">Próxima Fecha</label><input type="date" id="next_date" class="w-full border rounded p-1 text-xs"></div>
                </div>
            </div>
            <div class="flex gap-2"><button type="button" onclick="document.getElementById('step-payment').classList.add('hidden');document.getElementById('step-1').classList.remove('hidden');" class="flex-1 bg-gray-100 py-3 rounded text-xs font-bold uppercase">Atrás</button><button type="submit" class="flex-[2] bg-green-600 text-white font-black py-3 rounded text-xs uppercase shadow-lg">Confirmar</button></div>
        </form>`;

    document.getElementById('form-pago-widget').onsubmit = async (e) => {
        e.preventDefault();
        const amount = parseFloat(document.getElementById('pay_amount').value);
        const isPartial = amount < totalDebt;

        // Validación de fecha si es parcial
        if (isPartial && !document.getElementById('next_date').value) {
            alert("⚠️ Al ser pago parcial, debes ingresar la próxima fecha de pago.");
            return;
        }

        const data = {
            client_id: clientId,
            type: 'YA_PAGO',
            amount_paid: amount,
            method: document.getElementById('pay_method').value,
            is_total: !isPartial,
            next_promise: isPartial ? document.getElementById('next_date').value : null,
            installments: isPartial ? document.getElementById('installments').value : 1
        };

        try {
            const res = await fetch('api/admin/save_cobranza.php', { method: 'POST', body: JSON.stringify(data) });
            const r = await res.json();
            if (r.success) {
                document.getElementById('modal-cobranzas-manage').remove();
                if (window.loadDebtorsList) window.loadDebtorsList();
                if (window.updateStats) window.updateStats();
                window.notificar.exito(isPartial ? "Abono registrado correctamente." : "Pago total completado.");
            } else { alert("Error servidor: " + r.message); }
        } catch (e) { alert("Error conexión"); }
    };
};

window.validatePartialPayment = (val, total) => {
    const isP = parseFloat(val) < total;
    const div = document.getElementById('partial_logic_container');
    if (isP) div.classList.remove('hidden'); else div.classList.add('hidden');
    document.getElementById('next_date').required = isP;
};