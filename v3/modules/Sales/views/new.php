<div class="p-6">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                <h3 class="font-bold text-gray-800 text-xl">Gestión de Ventas y Prospectos</h3>
                <p class="text-sm text-gray-500">Administra nuevos contratos y verifica factibilidad técnica.</p>
            </div>
            
            <div class="flex gap-2">
                <button onclick="openSmartCoverage()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow transition flex items-center gap-2">
                    <i class="fas fa-map-marked-alt"></i> Cobertura Inteligente
                </button>
                <button onclick="openContractModal()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow transition flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Nuevo Contrato
                </button>
            </div>
        </div>

        <!-- Dashboard Widgets -->
        <div id="dashboard-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Stats inyectados por JS -->
            <div class="p-6 border border-gray-100 rounded-xl bg-gray-50 text-center text-gray-400">
                <i class="fas fa-circle-notch fa-spin"></i> Cargando estadísticas...
            </div>
        </div>

        <div class="relative">
            <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            <input type="text" id="clientSearchInput" onkeyup="loadSalesLeads(this.value)"
                   placeholder="Buscar prospecto o cliente..." 
                   class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 pr-4 py-3 outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
        </div>
    </div>

    <div id="sales-list" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[200px]">
        <div class="text-center py-10 text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>Cargando lista...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i = setInterval(() => {
            if (typeof window.loadScript === 'function') {
                clearInterval(i);
                window.loadScript('assets/js/modules/dashboards/ventas.js');
                // Maps libs commonly needed for coverage
                if (!window.L) {
                    const link = document.createElement('link'); link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css'; document.head.appendChild(link);
                    window.loadScript('https://unpkg.com/leaflet@1.7.1/dist/leaflet.js').then(() => {
                        window.loadScript('https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js');
                    });
                }
            }
        }, 100);
    });
</script>
