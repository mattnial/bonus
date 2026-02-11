<div id="cobranzas-dashboard" class="p-6 max-w-7xl mx-auto animate-fade-in">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-hand-holding-usd text-indigo-600"></i> Gestión de Cobranzas
            </h2>
            <p class="text-sm text-gray-500">Administra la morosidad y evita cortes de servicio.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="loadDebtorsList()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-bold hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Exportar Reporte
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-indigo-500 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cartera Vencida</p>
                <h3 class="text-2xl font-extrabold text-gray-800 mt-1" id="kpi-total-deuda">---</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xl">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">En Corte / Suspendidos</p>
                <h3 class="text-2xl font-extrabold text-red-600 mt-1" id="kpi-clientes-cortados">---</h3>
            </div>
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-xl">
                <i class="fas fa-user-slash"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Promesas de Pago</p>
                <h3 class="text-2xl font-extrabold text-green-600 mt-1" id="kpi-promesas">---</h3>
            </div>
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl">
                <i class="fas fa-handshake"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Listado de Clientes en Mora</h3>
            
            <div class="relative">
                <input type="text" placeholder="Buscar cliente..." class="pl-9 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none w-64 transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-500 text-xs uppercase font-bold tracking-wider">
                        <th class="px-6 py-3 border-b">Cliente</th>
                        <th class="px-6 py-3 border-b text-center">Estado</th>
                        <th class="px-6 py-3 border-b">Deuda ($)</th>
                        <th class="px-6 py-3 border-b">Contacto</th>
                        <th class="px-6 py-3 border-b">Última Gestión</th>
                        <th class="px-6 py-3 border-b text-right">Acción</th>
                    </tr>
                </thead>
                <tbody id="debtors-table-body" class="divide-y divide-gray-100 text-sm">
                    </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center bg-gray-50">
            <span class="text-xs text-gray-500">Mostrando 10 de 50 resultados</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 border rounded hover:bg-white text-gray-600 disabled:opacity-50"><i class="fas fa-chevron-left"></i></button>
                <button class="px-3 py-1 border rounded hover:bg-white text-gray-600"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>