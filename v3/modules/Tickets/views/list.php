<div class="space-y-6 fade-in p-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Bandeja de Soporte</h2>
        <div class="flex gap-2">
            <select id="filterStatus" onchange="loadGlobalTickets()" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">Todos (Abiertos)</option>
                <option value="ABIERTO">Solo Abiertos</option>
                <option value="EN_PROCESO">En Proceso</option>
                <option value="URGENTE">Urgentes</option>
                <option value="CERRADO">Cerrados / Historial</option>
            </select>
            <button onclick="loadGlobalTickets()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
            <button onclick="openCreateTicketModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2 transition transform hover:scale-105">
                <i class="fas fa-plus-circle"></i>
                <span>Nuevo Ticket</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-900 text-white uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">ID / Fecha</th>
                        <th class="px-6 py-3">Asunto</th>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">Departamento</th>
                        <th class="px-6 py-3 text-center">Prioridad</th>
                        <th class="px-6 py-3 text-center">Estado</th>
                        <th class="px-6 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody id="globalTicketsTable" class="divide-y divide-gray-200 bg-white">
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">Cargando tickets...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i = setInterval(() => {
            if (typeof window.loadScript === 'function') {
                clearInterval(i);
                window.loadScript('assets/js/modules/tickets.js');
                window.loadScript('assets/js/modules/tickets_chat.js');
            }
        }, 100);
    });
</script>