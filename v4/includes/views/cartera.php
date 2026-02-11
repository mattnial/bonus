<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div>
            <h3 class="font-bold text-gray-800 text-xl">Convenios de Pago</h3>
            <p class="text-sm text-gray-500">Monitorea las promesas de pago y reactivaciones temporales.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 shadow-sm transition">
                <i class="fas fa-filter"></i> Filtrar Vencidos
            </button>
            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 shadow-sm transition font-bold">
                <i class="fas fa-file-export"></i> Exportar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-purple-500 flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase">Convenios Activos</p>
                <h3 class="text-2xl font-bold text-gray-800">12</h3>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600"><i class="fas fa-handshake"></i></div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500 flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase">Vencidos (Sin Pago)</p>
                <h3 class="text-2xl font-bold text-red-600">4</h3>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-600 animate-pulse"><i class="fas fa-exclamation-circle"></i></div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500 flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase">Cumplidos este Mes</p>
                <h3 class="text-2xl font-bold text-green-600">28</h3>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600"><i class="fas fa-check-double"></i></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Cliente</th>
                    <th class="px-6 py-3">Fecha Límite</th>
                    <th class="px-6 py-3">Creado Por</th>
                    <th class="px-6 py-3">Observación</th>
                    <th class="px-6 py-3 text-center">Estado</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-800">Monica Velez</p>
                        <p class="text-xs text-gray-400">1104567890</p>
                    </td>
                    <td class="px-6 py-4 font-bold text-blue-600">
                        <i class="far fa-calendar-alt mr-1"></i> 20 Dic, 2025
                    </td>
                    <td class="px-6 py-4 text-xs">
                        <span class="bg-gray-100 px-2 py-1 rounded border">Admin Ventas</span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 italic truncate max-w-xs">
                        "Promete pagar con el décimo..."
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-bold uppercase">Vigente</span>
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                        <button class="text-green-600 hover:bg-green-50 p-2 rounded transition" title="Marcar Pagado"><i class="fas fa-check"></i></button>
                        <button class="text-red-500 hover:bg-red-50 p-2 rounded transition" title="Anular Convenio"><i class="fas fa-times"></i></button>
                        <a href="https://wa.me/5939999999?text=Estimado cliente, recuerde su convenio..." target="_blank" class="text-green-500 hover:bg-green-50 p-2 rounded transition" title="Recordar por WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </td>
                </tr>

                <tr class="bg-red-50 hover:bg-red-100 transition">
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-800">Pedro Picapiedra</p>
                        <p class="text-xs text-gray-400">1900123456</p>
                    </td>
                    <td class="px-6 py-4 font-bold text-red-600">
                        <i class="far fa-calendar-times mr-1"></i> Ayer
                    </td>
                    <td class="px-6 py-4 text-xs">
                        <span class="bg-white px-2 py-1 rounded border">Soporte Téc.</span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 italic truncate max-w-xs">
                        "Esperando transferencia..."
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-red-200 text-red-800 px-2 py-1 rounded text-[10px] font-bold uppercase animate-pulse">VENCIDO</span>
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                        <button class="bg-white border border-red-300 text-red-600 hover:bg-red-600 hover:text-white px-3 py-1 rounded text-xs font-bold shadow-sm">
                            <i class="fas fa-cut mr-1"></i> CORTAR
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>