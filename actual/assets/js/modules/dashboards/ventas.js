/**
 * VISTA DE VENTAS V8 - CON ENRUTAMIENTO POR CARRETERA (OSRM)
 */
(async function () {
    console.log("💰 Cargando Panel Comercial...");

    const widgets = document.getElementById('dashboard-widgets');
    const main = document.getElementById('main-dashboard-content');
    const subtitle = document.getElementById('dashboard-subtitle');

    if (subtitle) subtitle.innerText = "Ventas y Factibilidad Técnica";

    // 1. STATS
    let stats = { pending_leads: 0, month_sales: 0 };
    try {
        const res = await fetch('api/admin/sales_stats.php');
        stats = await res.json();
    } catch (e) { }

    // 2. WIDGETS
    if (widgets) {
        widgets.innerHTML = `
            <div onclick="document.getElementById('sales-list').scrollIntoView({behavior:'smooth'})" class="cursor-pointer bg-white rounded-xl p-6 shadow-sm border border-orange-100 group hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div><p class="text-orange-500 text-xs font-bold uppercase mb-1">Prospectos</p><h3 class="text-4xl font-bold text-gray-800">${stats.pending_leads || 0}</h3></div>
                    <div class="bg-orange-50 text-orange-500 w-12 h-12 rounded-lg flex items-center justify-center text-xl"><i class="fas fa-user-clock"></i></div>
                </div>
            </div>

            <div onclick="openContractsHistory()" class="cursor-pointer bg-white rounded-xl p-6 shadow-sm border border-emerald-100 group hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div><p class="text-emerald-600 text-xs font-bold uppercase mb-1">Contratos</p><h3 class="text-4xl font-bold text-gray-800">${stats.month_sales || 0}</h3></div>
                    <div class="bg-emerald-50 text-emerald-600 w-12 h-12 rounded-lg flex items-center justify-center text-xl"><i class="fas fa-file-signature"></i></div>
                </div>
            </div>

            <div class="col-span-1 md:col-span-2 bg-gradient-to-r from-indigo-600 to-blue-800 rounded-xl p-6 text-white shadow-lg">
                <h3 class="font-bold text-lg mb-1"><i class="fas fa-route"></i> Factibilidad por Carretera</h3>
                <p class="text-blue-100 text-sm mb-4">Calcula la ruta real de fibra hasta la caja NAP más cercana.</p>
                <div class="flex gap-3">
                    <button onclick="openContractModal()" class="flex-1 bg-white/10 border border-white/30 text-white py-2 rounded-lg font-bold text-sm hover:bg-white/20 transition">
                        <i class="fas fa-file-contract"></i> Contrato
                    </button>
                    <button onclick="openSmartCoverage()" class="flex-1 bg-white text-indigo-700 py-2 rounded-lg font-bold text-sm hover:bg-indigo-50 transition shadow-md flex items-center justify-center gap-2">
                        <i class="fas fa-map-signs"></i> CALCULAR RUTA
                    </button>
                </div>
            </div>
        `;
    }

    // 3. TABLA
    if (main) {
        main.innerHTML = `<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"><div id="sales-list"></div></div>`;
        setTimeout(() => { if (window.loadSalesLeads) window.loadSalesLeads(); }, 100);
    }
})();



// =========================================================
// 2. CALCULADORA DE RUTA (CON UNIFICADOR DE MAPAS)
// =========================================================
let mapInstance = null;
let clientMarker = null;
let routingControl = null;
let napLayer = null; // Capa para los puntos
let napsData = [];   // Datos en memoria

// Función dedicada para CERRAR y limpiar eventos
window.closeSmartCoverage = function () {
    const modal = document.getElementById('modal-smart-coverage');
    if (modal) {
        modal.remove();
        document.removeEventListener('keydown', handleEscKey);
        if (mapInstance) {
            mapInstance.remove();
            mapInstance = null;
        }
    }
};

// Manejador de tecla ESC
function handleEscKey(e) {
    if (e.key === 'Escape') window.closeSmartCoverage();
}

window.openSmartCoverage = function () {
    const modalId = 'modal-smart-coverage';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();

    document.addEventListener('keydown', handleEscKey);

    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-gray-900/90 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl h-[90vh] flex flex-col overflow-hidden relative">
                
                <button onclick="closeSmartCoverage()" class="absolute top-4 right-4 z-[1001] bg-white text-gray-800 w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:bg-red-500 hover:text-white transition font-bold text-xl border-2 border-gray-100" title="Cerrar (Esc)">
                    <i class="fas fa-times"></i>
                </button>

                <div class="absolute top-4 left-4 z-[1000] w-80 bg-white/95 backdrop-blur rounded-xl shadow-xl border border-gray-200 p-4 flex flex-col gap-3">
                    <h3 class="font-bold text-gray-800 border-b pb-2">Factibilidad Técnica</h3>
                    
                    <div id="result-box" class="hidden">
                        <div id="status-badge" class="text-center py-2 px-3 rounded text-white font-bold text-sm mb-3"></div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mb-1">
                            <span>Caja NAP:</span>
                            <span class="font-bold text-gray-800 text-right truncate" id="nap-name">---</span>
                        </div>
                        <div class="bg-blue-50 rounded p-3 border border-blue-100 text-center">
                            <p class="text-xs text-blue-500 uppercase font-bold">Distancia Aprox. Fibra</p>
                            <p class="text-3xl font-extrabold text-blue-700" id="nap-dist">0 m</p>
                        </div>
                    </div>

                    <div id="instruction-box" class="text-center py-6 text-gray-500 text-sm">
                        <div class="w-12 h-12 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-network-wired text-xl"></i>
                        </div>
                        <p id="loading-naps-text"><i class="fas fa-spinner fa-spin"></i> Cargando cajas...</p>
                    </div>

                    <div id="calculating-box" class="hidden text-center py-6 text-indigo-500 text-sm">
                        <i class="fas fa-circle-notch fa-spin text-2xl mb-2"></i>
                        <p>Calculando ruta óptima...</p>
                    </div>
                </div>

                <div id="coverageMap" class="w-full h-full bg-gray-100"></div>
            </div>
        </div>
    `);

    // 1. Inicializar Mapa (Centrado en Loja/Vilcabamba por defecto)
    mapInstance = L.map('coverageMap', { zoomControl: false }).setView([-4.2605, -79.2230], 13);
    L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'Vilcanet' }).addTo(mapInstance);

    // 2. CARGAR EL FUSIONADOR (Aquí está la magia)
    // Esto llama al PHP que une tus 8 archivos .geojson
    fetch('api/admin/get_all_naps.php')
        .then(response => response.json())
        .then(data => {
            napsData = []; // Limpiar

            // Dibujar puntos en el mapa
            napLayer = L.geoJSON(data, {
                pointToLayer: function (feature, latlng) {
                    // Obtener nombre inteligente (a veces viene como name, Name, o description)
                    let pName = "NAP";
                    if (feature.properties) {
                        pName = feature.properties.name || feature.properties.Name || feature.properties.description || "NAP";
                    }

                    // Guardamos en memoria para calcular
                    napsData.push({
                        lat: latlng.lat,
                        lng: latlng.lng,
                        name: pName
                    });

                    return L.circleMarker(latlng, {
                        radius: 6,
                        fillColor: "#2563eb",
                        color: "#fff",
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    });
                },
                onEachFeature: function (feature, layer) {
                    let pName = feature.properties.name || "Elemento de Red";
                    layer.bindPopup(`<b>${pName}</b>`);
                }
            }).addTo(mapInstance);

            // Actualizar UI
            document.getElementById('loading-naps-text').innerHTML = `
                <b>¡Cajas Listas!</b><br>
                <span class="text-xs text-gray-400">Haz clic en la Ubicación del cliente para calcular.</span>`;

            // Auto-ajustar zoom para ver todos los puntos cargados
            if (napsData.length > 0) {
                try { mapInstance.fitBounds(napLayer.getBounds()); } catch (e) { }
            }

            // Habilitar clic
            mapInstance.on('click', function (e) {
                calculateRoute(e.latlng.lat, e.latlng.lng);
            });

        })
        .catch(err => {
            console.error("Error cargando mapas:", err);
            document.getElementById('loading-naps-text').innerHTML = `<span class="text-red-500 font-bold">Error cargando mapas.</span>`;
        });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(p => mapInstance.flyTo([p.coords.latitude, p.coords.longitude], 16));
    }
};

function calculateRoute(clientLat, clientLng) {
    if (napsData.length === 0) return alert("Aún no cargan los mapas. Espera un momento.");

    // UI
    document.getElementById('instruction-box').classList.add('hidden');
    document.getElementById('result-box').classList.add('hidden');
    document.getElementById('calculating-box').classList.remove('hidden');

    // Marcador Cliente
    if (clientMarker) mapInstance.removeLayer(clientMarker);
    const clientIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#ef4444; width:14px; height:14px; border-radius:50%; border:3px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3);'></div>",
        iconSize: [14, 14], iconAnchor: [7, 7]
    });
    clientMarker = L.marker([clientLat, clientLng], { icon: clientIcon }).addTo(mapInstance);

    // 1. Encontrar NAP más cercana (Lineal)
    let nearest = null;
    let minLinearDist = Infinity;

    napsData.forEach(nap => {
        const dist = mapInstance.distance([clientLat, clientLng], [nap.lat, nap.lng]);
        if (dist < minLinearDist) {
            minLinearDist = dist;
            nearest = nap;
        }
    });

    if (!nearest) {
        alert("No se encontró ninguna caja cercana.");
        document.getElementById('calculating-box').classList.add('hidden');
        return;
    }

    // 2. Calcular Ruta Real (OSRM)
    if (routingControl) {
        try { mapInstance.removeControl(routingControl); } catch (e) { }
        routingControl = null;
    }

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(clientLat, clientLng),
            L.latLng(nearest.lat, nearest.lng)
        ],
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile: 'foot' // 'foot' para seguir postes/calles peatonales
        }),
        lineOptions: { styles: [{ color: '#6366f1', opacity: 0.8, weight: 6 }] },
        createMarker: function () { return null; },
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        show: false
    }).addTo(mapInstance);

    routingControl.on('routesfound', function (e) {
        const routes = e.routes;
        const roadDistance = routes[0].summary.totalDistance;

        document.getElementById('calculating-box').classList.add('hidden');
        document.getElementById('result-box').classList.remove('hidden');

        document.getElementById('nap-name').innerText = nearest.name;
        document.getElementById('nap-dist').innerText = Math.round(roadDistance) + ' m';

        const badge = document.getElementById('status-badge');

        if (roadDistance <= 250) {
            badge.className = "text-center py-2 px-3 rounded text-white font-bold text-sm mb-3 shadow-lg bg-green-500";
            badge.innerText = "COBERTURA ÓPTIMA";
        } else if (roadDistance <= 450) {
            badge.className = "text-center py-2 px-3 rounded text-white font-bold text-sm mb-3 shadow-lg bg-yellow-500";
            badge.innerText = "FACTIBLE (REVISAR)";
        } else {
            badge.className = "text-center py-2 px-3 rounded text-white font-bold text-sm mb-3 shadow-lg bg-red-500";
            badge.innerText = "MUY LEJOS (PROYECTO)";
        }
    });

    routingControl.on('routingerror', function () {
        // Fallback si falla la ruta (ej: zona sin calles)
        document.getElementById('calculating-box').classList.add('hidden');
        document.getElementById('result-box').classList.remove('hidden');
        document.getElementById('nap-name').innerText = nearest.name;
        document.getElementById('nap-dist').innerText = Math.round(minLinearDist) + ' m (Lineal)';
        document.getElementById('status-badge').innerText = "RUTA NO ENCONTRADA (LINEAL)";
        document.getElementById('status-badge').className = "bg-gray-500 text-white p-2 rounded font-bold text-xs";

        // Dibujar línea recta
        L.polyline([[clientLat, clientLng], [nearest.lat, nearest.lng]], { color: 'red', dashArray: '5, 10' }).addTo(mapInstance);
    });
}









// --- FUNCIONES GLOBALES ---

window.loadSalesLeads = async function () {
    try {
        const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
        const list = document.getElementById('sales-list');
        if (!list) return;

        list.innerHTML = '<p class="text-center text-gray-400 py-6"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';
        const res = await fetch(`api/admin/get_all_tickets.php?assigned_to=${user.id}`);
        const tickets = await res.json();
        list.innerHTML = '';

        const leads = tickets.filter(t => t.status !== 'CERRADO' && t.status !== 'RESUELTO');

        if (leads.length === 0) {
            list.innerHTML = `<div class="p-8 text-center text-gray-400 border border-dashed rounded-xl">Sin prospectos pendientes.</div>`;
        } else {
            leads.forEach(t => {
                list.innerHTML += `
                    <div class="flex justify-between items-center p-4 border border-gray-100 rounded-xl hover:shadow-md transition bg-white">
                        <div>
                            <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">PROSPECTO #${t.id}</span>
                            <h4 class="font-bold text-gray-800 mt-1">${t.subject}</h4>
                            <p class="text-sm text-gray-600">${t.client_name || 'Sin Nombre'}</p>
                        </div>
                        <button onclick="openTicketChat(${t.id})" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-bold">Gestionar</button>
                    </div>`;
            });
        }
    } catch (e) { console.error(e); }
};

// --- NUEVA FUNCIÓN: HISTORIAL DE CONTRATOS ---
window.openContractsHistory = async function () {
    const modalId = 'modal-contracts-history';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();

    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[85vh]">
                
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Historial de Contratos</h3>
                        <p class="text-xs text-gray-500">Archivos generados en el sistema</p>
                    </div>
                    <button onclick="document.getElementById('${modalId}').remove()" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-200 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 bg-white p-0">
                    <div id="contracts-list-content" class="divide-y divide-gray-100">
                        <p class="text-center text-gray-400 py-10"><i class="fas fa-spinner fa-spin"></i> Cargando archivos...</p>
                    </div>
                </div>
            </div>
        </div>
    `);

    try {
        const res = await fetch('api/admin/get_contracts.php');
        const files = await res.json();
        const container = document.getElementById('contracts-list-content');
        container.innerHTML = '';

        if (!files || files.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <i class="fas fa-folder-open text-4xl mb-3 opacity-20"></i>
                    <p class="text-sm">No hay contratos generados aún.</p>
                </div>`;
            return;
        }

        files.forEach(f => {
            container.innerHTML += `
                <div class="flex items-center justify-between p-4 hover:bg-blue-50 transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                            <i class="fas fa-file-word"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 truncate max-w-[250px]">${f.name}</h4>
                            <p class="text-xs text-gray-500 flex gap-2">
                                <span><i class="far fa-calendar"></i> ${f.date}</span>
                                <span><i class="fas fa-hdd"></i> ${f.size}</span>
                            </p>
                        </div>
                    </div>
                    <a href="${f.url}" target="_blank" download class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                </div>
            `;
        });

    } catch (e) {
        document.getElementById('contracts-list-content').innerHTML = '<p class="text-center text-red-400 py-6">Error al cargar la lista.</p>';
    }
};

// GLOBAL: Modal Nuevo Contrato (Mantener igual que antes)
// =========================================================
// MODAL CONTRATO (LÓGICA DE RENOVACIÓN INTELIGENTE)
// =========================================================
window.openContractModal = function () {
    const modalId = 'modal-contract';
    if (document.getElementById(modalId)) document.getElementById(modalId).remove();

    // Estado global del cliente actual
    let clientExistsState = false;

    document.body.insertAdjacentHTML('beforeend', `
        <div id="${modalId}" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 bg-blue-600 text-white flex justify-between items-center">
                    <h3 class="font-bold text-lg"><i class="fas fa-file-contract"></i> Contrato / Renovación</h3>
                    <button onclick="document.getElementById('${modalId}').remove()" class="text-white/70 hover:text-white"><i class="fas fa-times text-xl"></i></button>
                </div>

                <div class="overflow-y-auto p-6 bg-gray-50 flex-1">
                    <form id="contractForm" class="space-y-6">
                        
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h4 class="text-sm font-bold text-blue-600 uppercase mb-4 border-b pb-2">1. Datos del Cliente</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Cédula / RUC *</label>
                                    <input type="text" name="cedula" id="inputCedula" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none transition" 
                                           placeholder="Ingrese Cédula" onblur="checkClientStatus(this)">
                                    <p id="cedulaFeedback" class="text-[10px] h-3 mt-1 font-bold"></p>
                                </div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Nombre Completo *</label><input type="text" name="nombre" id="inputNombre" required class="w-full border rounded p-2"></div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Celular *</label><input type="tel" name="celular" id="inputCelular" required class="w-full border rounded p-2"></div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Email *</label><input type="email" name="email" id="inputEmail" required class="w-full border rounded p-2"></div>
                                <div class="col-span-full"><label class="block text-xs font-bold text-gray-500 mb-1">Dirección Exacta *</label><input type="text" name="direccion" id="inputDireccion" required class="w-full border rounded p-2"></div>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h4 class="text-sm font-bold text-blue-600 uppercase mb-4 border-b pb-2">2. Datos del Servicio</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Trámite</label>
                                    <select name="tramite" id="selectTramite" class="w-full border rounded p-2 bg-white" onchange="validateFormRules()">
                                        <option value="INSTALACION">Instalación Nueva</option>
                                        <option value="RENOVACION">Renovación de Contrato</option>
                                        <option value="MIGRACION">Migración</option>
                                        <option value="CAMBIO_TITULAR">Cambio de Titular</option>
                                    </select>
                                </div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Sucursal</label><select name="sucursal" class="w-full border rounded p-2 bg-white"><option value="VILCA">Vilcabamba</option><option value="LOJA">Loja</option><option value="PALAN">Palanda</option></select></div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Plan</label><select name="plan" class="w-full border rounded p-2 bg-white"><option value="STANDARD">Plan Standard</option><option value="ESENCIAL">Plan Esencial</option><option value="FAMILIAR">Plan Familiar</option><option value="STANDARD_PRO">Standard Pro</option><option value="ESENCIAL_EXPERT">Esencial Expert</option><option value="EMPRESA">Empresa Premium</option></select></div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Grupo Prioritario</label><select name="prioritario" class="w-full border rounded p-2 bg-white"><option value="NINGUNO">Ninguno (Normal)</option><option value="TERCERA_EDAD">Tercera Edad</option><option value="DISCAPACIDAD">Discapacidad</option><option value="BONO">Bono Desarrollo Humano</option></select></div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Tiempo Contrato</label><select name="tiempo" class="w-full border rounded p-2 bg-white"><option value="36 meses">36 Meses (Estándar)</option><option value="24 meses">24 Meses</option><option value="12 meses">12 Meses</option></select></div>
                                <div><label class="block text-xs font-bold text-gray-500 mb-1">Conexión</label><div class="flex gap-4 mt-2"><label class="flex items-center gap-2 text-sm"><input type="radio" name="tipo_conexion" value="FIBRA" checked> Fibra</label><label class="flex items-center gap-2 text-sm"><input type="radio" name="tipo_conexion" value="RADIO"> Radio</label></div></div>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-200">
                            <h4 class="text-sm font-bold text-blue-800 uppercase mb-4 border-b border-blue-200 pb-2">3. Cláusulas Legales</h4>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b border-blue-100 pb-2"><label class="text-sm text-gray-700 w-2/3">¿Acepta la <b>Renovación Automática</b>?</label><div class="flex gap-4"><label class="flex items-center gap-1 text-sm"><input type="radio" name="legal_renovacion" value="SI" checked> SI</label><label class="flex items-center gap-1 text-sm"><input type="radio" name="legal_renovacion" value="NO"> NO</label></div></div>
                                <div class="flex justify-between items-center border-b border-blue-100 pb-2"><label class="text-sm text-gray-700 w-2/3">¿Acepta <b>Permanencia Mínima</b>?</label><div class="flex gap-4"><label class="flex items-center gap-1 text-sm"><input type="radio" name="legal_permanencia" value="SI" checked> SI</label><label class="flex items-center gap-1 text-sm"><input type="radio" name="legal_permanencia" value="NO"> NO</label></div></div>
                                <div class="flex justify-between items-center"><label class="text-sm text-gray-700 w-2/3">¿Acepta <b>Arbitraje</b>?</label><div class="flex gap-4"><label class="flex items-center gap-1 text-sm"><input type="radio" name="legal_arbitraje" value="SI" checked> SI</label><label class="flex items-center gap-1 text-sm"><input type="radio" name="legal_arbitraje" value="NO"> NO</label></div></div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" id="btnSaveContract" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg flex justify-center items-center gap-2">
                                <i class="fas fa-file-contract"></i> Generar Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `);

    // 2. FUNCIÓN DE REGLAS DE NEGOCIO
    window.validateFormRules = function () {
        const tramite = document.getElementById('selectTramite').value;
        const btn = document.getElementById('btnSaveContract');
        const feedback = document.getElementById('cedulaFeedback');

        // REGLA 1: Si el cliente existe Y es instalación nueva -> PROHIBIDO
        if (clientExistsState && tramite === 'INSTALACION') {
            feedback.innerHTML = `<span class="text-red-500 font-bold"><i class="fas fa-exclamation-circle"></i> Cliente existe. Selecciona RENOVACIÓN.</span>`;
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btn.innerText = "Cambia el trámite a Renovación";
        }
        // REGLA 2: Si el cliente existe Y es renovación -> PERMITIDO
        else if (clientExistsState && tramite === 'RENOVACION') {
            feedback.innerHTML = `<span class="text-blue-500 font-bold"><i class="fas fa-sync-alt"></i> Modo Renovación Activado</span>`;
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fas fa-file-signature"></i> Generar Renovación';
        }
        // REGLA 3: Si es nuevo -> PERMITIDO TODO
        else {
            if (!clientExistsState) {
                feedback.innerHTML = '<span class="text-green-500 font-bold"><i class="fas fa-check-circle"></i> Cédula disponible (Nuevo)</span>';
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.innerHTML = '<i class="fas fa-file-contract"></i> Generar Contrato';
            }
        }
    };

    // 3. VERIFICAR CLIENTE
    window.checkClientStatus = async function (input) {
        const val = input.value.trim();
        if (val.length < 5) return;

        try {
            const res = await fetch(`api/admin/search_clients.php?q=${val}`);
            const data = await res.json();
            const exists = data.find(c => c.cedula === val);

            if (exists) {
                clientExistsState = true;
                // Auto-llenar datos para facilitar la renovación
                document.getElementById('inputNombre').value = exists.name;
                document.getElementById('inputCelular').value = exists.phone || '';
                document.getElementById('inputEmail').value = exists.email || '';
                document.getElementById('inputDireccion').value = exists.address || '';

                showToast(`Cliente encontrado. Selecciona "Renovación" si deseas actualizar contrato.`, 'info');
            } else {
                clientExistsState = false;
            }
            // Correr validaciones
            validateFormRules();

        } catch (e) { console.error(e); }
    };

    // 4. ENVÍO
    document.getElementById('contractForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveContract');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        // Enviar a la API que maneja tanto creación como renovación
        try {
            const res = await fetch('api/admin/create_contract.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (result.success) {
                document.getElementById(modalId).remove();
                showToast('Documento generado y datos actualizados', 'success');
                setTimeout(() => {
                    if (confirm('¿Descargar documento?')) window.open(result.document_url, '_blank');
                    if (window.loadSalesLeads) window.loadSalesLeads();
                    location.reload();
                }, 1000);
            } else {
                showToast(result.message || 'Error', 'error');
            }
        } catch (err) { showToast('Error de conexión', 'error'); }
        finally { btn.innerHTML = originalText; btn.disabled = false; }
    });
};