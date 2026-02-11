/** WIDGET: HISTORIAL **/
window.viewClientHistory = async function (clientId, clientName) {
    const modalId = 'modal-cobranzas-history';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();

    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Historial: ${clientName}</h3>
                    <button onclick="document.getElementById('${modalId}').remove()"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 max-h-[500px] overflow-y-auto" id="history-content">
                    <div class="text-center py-5"><i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i></div>
                </div>
            </div>
        </div>
    `);

    try {
        const res = await fetch(`api/admin/get_client_history.php?client_id=${clientId}`);
        const history = await res.json();
        const container = document.getElementById('history-content');

        if (history.length === 0) {
            container.innerHTML = `<p class="text-center text-gray-400">Sin gestiones.</p>`;
            return;
        }

        let html = `<div class="space-y-4">`;
        history.forEach(item => {
            const date = new Date(item.created_at).toLocaleString('es-EC');
            let styles = "bg-white border-gray-200 border-l-4 border-l-gray-400";

            if (item.type === 'YA_PAGO') styles = "bg-blue-50 border-blue-200 border-l-4 border-l-blue-500";
            else if (item.type === 'CONVENIO_PAGO') styles = "bg-green-50 border-green-200 border-l-4 border-l-green-500";
            else if (item.type === 'BROKEN_PROMISE') styles = "bg-red-50 border-red-200 border-l-4 border-l-red-600 font-bold text-red-800";

            html += `<div class="p-4 rounded-r-lg border shadow-sm ${styles}">
                <div class="flex justify-between text-[10px] uppercase font-bold mb-1 opacity-70">
                    <span>${item.type.replace(/_/g, ' ')}</span><span>${date}</span>
                </div>
                <p class="text-xs leading-relaxed">${item.notes}</p>
            </div>`;
        });
        document.getElementById('history-content').innerHTML = html + `</div>`;
    } catch (e) { document.getElementById('history-content').innerHTML = "Error."; }
};