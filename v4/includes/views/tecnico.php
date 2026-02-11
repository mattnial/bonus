<div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-red-50 p-4 rounded-lg border border-red-100 flex justify-between items-center">
            <div>
                <p class="text-xs text-red-500 font-bold uppercase">Urgencias</p>
                <h3 class="text-2xl font-bold text-red-700" id="kpi-urgent">0</h3>
            </div>
            <i class="fas fa-fire text-red-200 text-3xl"></i>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 flex justify-between items-center">
            <div>
                <p class="text-xs text-blue-500 font-bold uppercase">Asignados a Mí</p>
                <h3 class="text-2xl font-bold text-blue-700" id="kpi-mine">0</h3>
            </div>
            <i class="fas fa-user-tag text-blue-200 text-3xl"></i>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-100 flex justify-between items-center">
            <div>
                <p class="text-xs text-green-500 font-bold uppercase">Resueltos Hoy</p>
                <h3 class="text-2xl font-bold text-green-700" id="kpi-solved">0</h3>
            </div>
            <i class="fas fa-check-circle text-green-200 text-3xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Cola de Tickets</h3>
            <button class="text-sm text-blue-600 hover:underline"><i class="fas fa-sync"></i> Actualizar</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">Problema</th>
                        <th class="px-6 py-3">Prioridad</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-6 py-4 font-bold">#1024</td>
                        <td class="px-6 py-4">Juan Pérez</td>
                        <td class="px-6 py-4">Sin Internet (Los Rosales)</td>
                        <td class="px-6 py-4"><span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">ALTA</span></td>
                        <td class="px-6 py-4"><span class="text-yellow-600 font-bold">ABIERTO</span></td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-blue-600 hover:text-blue-800 font-bold">Ver</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>