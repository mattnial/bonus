<div class="space-y-6 fade-in p-6">
    <h2 class="text-2xl font-bold text-gray-800">Centro de Reportes</h2>
    <p class="text-gray-500 text-sm">Descarga la información del sistema en formato PDF.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
            <div class="h-12 w-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl mb-4"><i class="fas fa-ticket-alt"></i></div>
            <h3 class="font-bold text-gray-800">Gestión de Tickets</h3>
            <p class="text-xs text-gray-400 mt-2 mb-4">Historial completo de soporte, tiempos y estados.</p>
            <button onclick="printReport('tickets')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded text-sm flex items-center justify-center gap-2"><i class="fas fa-print"></i> Imprimir PDF</button>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
            <div class="h-12 w-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center text-xl mb-4"><i class="fas fa-hand-holding-usd"></i></div>
            <h3 class="font-bold text-gray-800">Cartera Vencida</h3>
            <p class="text-xs text-gray-400 mt-2 mb-4">Listado de clientes con deudas activas.</p>
            <button onclick="printReport('debtors')" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded text-sm flex items-center justify-center gap-2"><i class="fas fa-print"></i> Imprimir PDF</button>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
            <div class="h-12 w-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-xl mb-4"><i class="fas fa-users"></i></div>
            <h3 class="font-bold text-gray-800">Sanciones RRHH</h3>
            <p class="text-xs text-gray-400 mt-2 mb-4">Registro de multas y memos del personal.</p>
            <button onclick="printReport('rrhh')" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded text-sm flex items-center justify-center gap-2"><i class="fas fa-print"></i> Imprimir PDF</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i = setInterval(() => {
            if (typeof window.loadScript === 'function') {
                clearInterval(i);
                window.loadScript('assets/js/modules/reports.js');
            }
        }, 100);
    });
</script>