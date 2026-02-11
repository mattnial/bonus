/**
 * VISTA DE GERENCIA
 * Replica exactamente el diseño y funcionalidades solicitados.
 */
(async function () {
    console.log("🏢 Cargando Diseño Gerencial...");

    // 1. Referencias a los contenedores del esqueleto (inicio.php)
    const widgetsContainer = document.getElementById('dashboard-widgets');
    const mainContainer = document.getElementById('main-dashboard-content');

    // Opcional: Actualizar subtítulo
    const sub = document.getElementById('dashboard-subtitle');
    if (sub) sub.innerText = "Resumen de actividad del día.";

    // 2. INYECTAR WIDGETS (Exactamente tu HTML)
    if (widgetsContainer) {
        widgetsContainer.innerHTML = `
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-blue-100 text-blue-600 rounded-lg"><i class="fas fa-users text-xl"></i></div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Clientes Activos</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="local-kpi-clients">--</h3>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-orange-100 text-orange-600 rounded-lg"><i class="fas fa-ticket-alt text-xl"></i></div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Tickets Abiertos</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="local-kpi-tickets">--</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-red-100 text-red-600 rounded-lg animate-pulse"><i class="fas fa-exclamation-circle text-xl"></i></div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Urgentes</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="local-kpi-urgent">--</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-purple-100 text-purple-600 rounded-lg"><i class="fas fa-hand-holding-usd text-xl"></i></div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">En Mora</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="local-kpi-debt">--</h3>
                </div>
            </div>
        `;
    }

    // 3. INYECTAR BUSCADOR (Exactamente tu HTML)
    if (mainContainer) {
        mainContainer.innerHTML = `
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8 animate-fade-in">
                <h3 class="font-bold text-gray-800 mb-4">Búsqueda Rápida de Clientes</h3>
                <input type="text" id="clientSearchInput" placeholder="Buscar por Nombre, Cédula o IP..." 
                    class="w-full border rounded-lg px-4 py-3 text-lg outline-none focus:ring-2 focus:ring-blue-500 transition">
                
                <div id="clientsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6"></div>
            </div>
        `;

        // Activar la lógica del buscador (usando la función global handleSearch de dashboard.js)
        const input = document.getElementById('clientSearchInput');
        if (input && window.handleSearch) {
            // Usamos debounce si existe, si no, directo
            const debouncer = window.debounce ? window.debounce(window.handleSearch, 500) : window.handleSearch;
            input.addEventListener('input', debouncer);

            // Cargar clientes recientes al inicio
            try {
                const res = await fetch('api/admin/search_clients.php?q=');
                const data = await res.json();
                if (window.renderClients) window.renderClients(data);
            } catch (e) { }
        }
    }

    // 4. LÓGICA DE DATOS Y ANIMACIÓN (Tu script original adaptado)

    // Función de animación
    function animateValue(obj, start, end, duration) {
        if (!obj) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Obtener datos del servidor
    try {
        const response = await fetch('api/admin/get_dashboard_stats.php');
        const data = await response.json();

        // Elementos del DOM (inyectados arriba)
        const kpiClients = document.getElementById('local-kpi-clients');
        const kpiTickets = document.getElementById('local-kpi-tickets');
        const kpiUrgent = document.getElementById('local-kpi-urgent');
        const kpiDebt = document.getElementById('local-kpi-debt');

        // Ejecutar animación
        // Nota: data.clients_active vs data.clients (depende de tu PHP, ajusto para compatibilidad)
        const valClients = data.clients || data.clients_active || 0;
        const valTickets = data.tickets || data.tickets_open || 0;
        const valUrgent = data.urgent || data.tickets_urgent || 0;
        const valDebt = data.debt || data.clients_debt || 0;

        animateValue(kpiClients, 0, valClients, 1000);
        animateValue(kpiTickets, 0, valTickets, 1000);
        animateValue(kpiUrgent, 0, valUrgent, 1000);
        animateValue(kpiDebt, 0, valDebt, 1000);

    } catch (error) {
        console.error('Error cargando KPIs:', error);
    }

})();