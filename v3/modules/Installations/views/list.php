<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Órdenes de Trabajo (Saldos de v1)</h2>
        <div class="flex gap-2">
            <button class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50"><i class="fas fa-filter"></i> Filtros</button>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700"><i class="fas fa-plus"></i> Nueva Orden</button>
        </div>
    </div>

    <!-- NOTA PARA EL USUARIO: Esta vista fue recuperada de v1 pero no tenía lógica JS conectada.
         Se recomienda implementar la carga dinámica desde la API de Tickets filtrando por tipo 'INSTALACION'. -->
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-gray-100 p-4 rounded-xl h-full">
            <h3 class="font-bold text-gray-500 uppercase text-xs mb-4 flex justify-between">
                Pendientes <span class="bg-gray-200 text-gray-600 px-2 rounded-full">0</span>
            </h3>
            <div id="col-pending" class="space-y-3">
                <div class="text-center text-gray-400 py-10">No hay órdenes pendientes</div>
            </div>
        </div>

        <div class="bg-blue-50 p-4 rounded-xl h-full">
            <h3 class="font-bold text-blue-500 uppercase text-xs mb-4 flex justify-between">
                En Ejecución <span class="bg-blue-200 text-blue-700 px-2 rounded-full">0</span>
            </h3>
            <div id="col-process" class="space-y-3">
                <!-- Ejemplo Estático recuperado -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-100">
                    <div class="flex justify-between mb-2">
                        <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded">MIGRACIÓN</span>
                        <span class="text-xs text-gray-400">Hace 30m</span>
                    </div>
                    <h4 class="font-bold text-gray-800 text-sm">Cango Marquez Clara</h4>
                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-map-marker-alt"></i> Barrio El Porvenir</p>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-gray-200 text-[10px] flex items-center justify-center font-bold">TC</div>
                        <span class="text-xs text-gray-500">Téc. Carlos</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-green-50 p-4 rounded-xl h-full">
            <h3 class="font-bold text-green-600 uppercase text-xs mb-4 flex justify-between">
                Finalizadas Hoy <span class="bg-green-200 text-green-700 px-2 rounded-full">0</span>
            </h3>
            <div id="col-finished" class="space-y-3">
            </div>
        </div>
    </div>
</div>
