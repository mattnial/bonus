/** WIDGET INCUMPLIMIENTO **/
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
                        <button onclick="showPaymentForm(${numericDebt})" class="bg-blue-600 text-white p-4 rounded-xl shadow-lg flex flex-col items-center gap-2"><i class="fas fa-file-invoice-dollar text-2xl"></i><span class="font-bold text-[10px] uppercase">Informar Pago</span></button>
                        <button onclick="saveBrokenAction(${id}, 'BROKEN_PROMISE')" class="bg-red-500 text-white p-4 rounded-xl shadow-lg flex flex-col items-center gap-2"><i class="fas fa-heart-broken text-2xl"></i><span class="font-bold text-[10px] uppercase">No cumplió</span></button>
                    </div>
                </div>
                <div id="step-payment" class="p-6 hidden"></div>
            </div>
        </div>`);
};

window.saveBrokenAction = function (id, type) {
    window.notificar.advertencia(
        "¿Confirmar incumplimiento?",
        "El cliente perderá su protección y regresará a la lista de corte inmediatamente.",
        async () => {
            try {
                const res = await fetch('api/admin/save_cobranza.php', {
                    method: 'POST',
                    body: JSON.stringify({ client_id: id, type: type, notes: '🚨 CONVENIO NO CUMPLIDO. PROCEDER AL CORTE.', contact_method: 'SISTEMA' })
                });
                if ((await res.json()).success) {
                    document.getElementById('modal-cobranzas-manage').remove();
                    loadDebtorsList();
                    updateStats();
                    window.notificar.exito("Cliente enviado a lista de corte.");
                }
            } catch (e) { window.notificar.error("Error al procesar la solicitud."); }
        }
    );
};