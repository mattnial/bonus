<div class="p-6">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                <h3 class="font-bold text-gray-800 text-xl">Cartera de Clientes</h3>
                <p class="text-sm text-gray-500">Gestiona contratos, nuevas instalaciones y servicios.</p>
            </div>
            
            <button onclick="openNewClientModal()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow transition flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Nuevo Cliente
            </button>
        </div>

        <div class="relative">
            <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            <input type="text" id="clientSearchInput" 
                   placeholder="Buscar por cédula, nombre o contrato..." 
                   class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 pr-4 py-3 outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
        </div>
    </div>

    <div id="clientsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div class="col-span-full text-center py-10 text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>Cargando cartera de clientes...</p>
        </div>
    </div>
</div>

<script>
    // Pequeño script específico para esta vista
    document.addEventListener('DOMContentLoaded', () => {
        // Forzamos la carga de clientes al entrar a esta vista
        if(typeof loadRecentClients === 'function') loadRecentClients();
    });
</script>