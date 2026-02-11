<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Logística y Finanzas</h2>
        
        <div class="bg-white p-1 rounded-lg shadow border flex">
            <button onclick="switchFinanceTab('debt')" id="tab-debt" class="px-6 py-2 rounded-md text-sm font-bold transition bg-blue-100 text-blue-700">
                <i class="fas fa-hand-holding-usd"></i> Cartera
            </button>
            <button onclick="switchFinanceTab('stock')" id="tab-stock" class="px-6 py-2 rounded-md text-sm font-bold transition text-gray-500 hover:bg-gray-50">
                <i class="fas fa-dolly"></i> Bodega
            </button>
        </div>
    </div>

    <div id="view-debt" class="finance-view animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-700">Cobranzas</h3>
            <button onclick="loadDebtors()" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 font-bold text-sm"><i class="fas fa-sync"></i> Refrescar</button>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-red-50 text-red-700 uppercase text-xs">
                    <tr><th class="px-6 py-3">Cliente</th><th class="px-6 py-3 text-center">Deuda</th><th class="px-6 py-3 text-center">Estado</th><th class="px-6 py-3 text-center">Acción</th></tr>
                </thead>
                <tbody id="debtorsTable" class="divide-y divide-gray-200">
                    <tr><td colspan="4" class="text-center py-8 text-gray-400">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="view-stock" class="finance-view hidden animate-fade-in">
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6" id="stockSummary"></div>
        <div id="stockAlerts" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-6 text-sm font-bold rounded-r shadow-sm">
            <i class="fas fa-exclamation-triangle"></i> <span id="stockAlertText"></span>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" id="stockSearch" placeholder="Buscar..." class="border rounded-lg px-4 py-2 text-sm w-64">
                    <select id="filterState" class="border rounded-lg px-3 py-2 text-sm bg-gray-50">
                        <option value="">Todo</option>
                        <option value="BODEGA">📦 Bodega</option>
                        <option value="ASIGNADO_TECNICO">👷 Asignado</option>
                        <option value="INSTALADO">🏠 Instalado</option>
                        <option value="BAJA">🗑️ Baja</option>
                    </select>
                    <button onclick="loadInventory()" class="bg-gray-100 px-3 rounded-lg"><i class="fas fa-search"></i></button>
                </div>

                <div class="flex gap-2">
                    <button onclick="openHistoryModal()" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-bold text-sm shadow flex items-center gap-2">
                        <i class="fas fa-history"></i> Historial
                    </button>
                    <button onclick="openDispatchModal()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow flex items-center gap-2">
                        <i class="fas fa-truck-loading"></i> Despachar
                    </button>
                    <button onclick="openStockModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow flex items-center gap-2">
                        <i class="fas fa-plus"></i> Ingreso
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-center"><input type="checkbox" id="selectAllStock" onchange="toggleSelectAll(this)"></th>
                        <th class="px-4 py-3">Foto</th>
                        <th class="px-4 py-3">Detalle</th>
                        <th class="px-4 py-3">Ubicación</th>
                        <th class="px-4 py-3 text-center">Stock</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="stockTable" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="stockModal" class="fixed inset-0 bg-black/60 hidden z-[50] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-lg">Ingreso de Material</h3>
            <button onclick="closeStockModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 overflow-y-auto">
            <div id="scannerContainer" class="hidden mb-4 bg-black rounded-lg h-40 relative"><div id="reader"></div></div>
            <form id="stockForm" onsubmit="handleStockSubmit(event)" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Tipo</label>
                        <select id="invType" class="w-full border rounded p-2 text-sm bg-gray-50 font-bold" onchange="toggleSerialInput()">
                            <optgroup label="Equipos (Únicos)">
                                <option value="ONT">ONT (Fibra)</option>
                                <option value="ROUTER">Router WiFi</option>
                                <option value="MESH">Mesh</option>
                            </optgroup>
                            <optgroup label="Material (Stock)">
                                <option value="BOBINA">Bobina Fibra</option>
                                <option value="CABLE_RED">Cable UTP</option>
                                <option value="MATERIAL">Insumos</option>
                            </optgroup>
                        </select>
                    </div>
                    <div><label class="text-xs font-bold text-gray-500 uppercase">Ubicación</label><select id="invLocation" class="w-full border rounded p-2 text-sm"><option value="Bodega Central">Bodega Central</option><option value="Oficina">Oficina</option></select></div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="text-xs font-bold text-gray-500 uppercase">Marca</label><input type="text" id="invBrand" class="w-full border rounded p-2 text-sm" placeholder="Huawei"></div>
                    <div><label class="text-xs font-bold text-gray-500 uppercase">Modelo</label><input type="text" id="invModel" class="w-full border rounded p-2 text-sm"></div>
                </div>

                <div id="sectionEquipment" class="space-y-3">
                    <div id="divSerial"><label class="text-xs font-bold text-gray-500 uppercase">Serie (S/N)</label><div class="flex gap-2"><input type="text" id="invSerial" class="w-full border rounded p-2 text-sm uppercase" placeholder="Escanear..."><button type="button" onclick="startScanner()" class="bg-blue-100 px-3 rounded"><i class="fas fa-camera"></i></button></div></div>
                    <div id="divMac"><label class="text-xs font-bold text-gray-500 uppercase">MAC</label><input type="text" id="invMac" class="w-full border rounded p-2 text-sm uppercase"></div>
                    <div id="divPhoto"><label class="text-xs font-bold text-gray-500 uppercase">Foto</label><input type="file" id="invPhoto" accept="image/*" class="w-full text-xs"></div>
                </div>

                <div id="sectionMaterial" class="hidden bg-yellow-50 p-3 rounded border border-yellow-200">
                    <label class="text-xs font-bold text-yellow-700 uppercase" id="lblQty">Cantidad</label>
                    <input type="number" id="invQty" class="w-full border rounded p-2 font-bold" value="1" min="1">
                </div>

                <div class="hidden"><input type="number" id="invCost" value="0"></div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded shadow">GUARDAR</button>
            </form>
        </div>
    </div>
</div>

<!-- Funcionalidad de Pestañas Finanzas (Inline para evitar conflictos de carga) -->
<script>
    function switchFinanceTab(tab) {
        // 1. Resetear botones
        document.getElementById('tab-debt').className = "px-6 py-2 rounded-md text-sm font-bold transition text-gray-500 hover:bg-gray-50";
        document.getElementById('tab-stock').className = "px-6 py-2 rounded-md text-sm font-bold transition text-gray-500 hover:bg-gray-50";
        
        // 2. Activar botón seleccionado
        if(tab === 'debt') {
            document.getElementById('tab-debt').className = "px-6 py-2 rounded-md text-sm font-bold transition bg-blue-100 text-blue-700";
        } else {
            document.getElementById('tab-stock').className = "px-6 py-2 rounded-md text-sm font-bold transition bg-blue-100 text-blue-700";
        }

        // 3. Mostrar vista correspondiente
        document.getElementById('view-debt').classList.add('hidden');
        document.getElementById('view-stock').classList.add('hidden');

        document.getElementById('view-' + tab).classList.remove('hidden');
    }
</script>

<div id="dispatchModal" class="fixed inset-0 bg-black/60 hidden z-[50] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-orange-600 text-white px-6 py-4">
            <h3 class="font-bold text-lg"><i class="fas fa-truck-loading"></i> Despacho Masivo</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Se enviarán <span id="countDispatch" class="font-bold text-orange-600">0</span> items.</p>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Técnico Destino</label>
            <select id="dispatchTech" class="w-full border rounded p-2 mb-4 bg-gray-50 font-bold text-gray-700"></select>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nota</label>
            <textarea id="dispatchNote" class="w-full border rounded p-2 mb-4 text-sm"></textarea>
            
            <div class="flex justify-end gap-2">
                <button onclick="document.getElementById('dispatchModal').classList.add('hidden')" class="px-4 py-2 text-gray-500 font-bold">Cancelar</button>
                <button onclick="processDispatch()" class="px-4 py-2 bg-orange-600 text-white font-bold rounded hover:bg-orange-700 shadow">CONFIRMAR ENVÍO</button>
            </div>
        </div>
    </div>
</div>

<div id="fastAssignModal" class="fixed inset-0 bg-black/60 hidden z-[50] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-blue-700 text-white px-6 py-4">
            <h3 class="font-bold text-lg">Asignar y Generar Acta</h3>
        </div>
        <div class="p-6">
            <input type="hidden" id="assignItemId">
            <p id="assignItemName" class="font-bold text-gray-800 mb-4 text-lg">...</p>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Entregar a:</label>
            <select id="assignTech" class="w-full border rounded p-2 mb-4 font-bold text-gray-700"></select>
            <div id="assignQtyDiv" class="hidden mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cantidad:</label>
                <input type="number" id="assignQty" class="w-full border rounded p-2 font-bold" value="1">
                <p class="text-xs text-gray-400 mt-1">Stock: <span id="assignMax">0</span></p>
            </div>
            <button onclick="processFastAssign()" class="w-full bg-blue-700 text-white font-bold py-3 rounded shadow flex justify-center gap-2"><i class="fas fa-print"></i> GENERAR ACTA</button>
            <button onclick="document.getElementById('fastAssignModal').classList.add('hidden')" class="w-full text-gray-500 font-bold py-2 mt-2 text-sm">Cancelar</button>
        </div>
    </div>
</div>

<div id="historyModal" class="fixed inset-0 bg-black/60 hidden z-[50] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-lg">Historial</h3>
            <button onclick="document.getElementById('historyModal').classList.add('hidden')"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-0">
            <table class="w-full text-sm text-left"><tbody id="historyTableBody" class="divide-y divide-gray-100"></tbody></table>
        </div>
    </div>
</div>

<div id="installModal" class="hidden"></div>

<!-- Safe Script Loading -->
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Simple polling to ensure core.js is loaded
        const waitForCore = setInterval(async () => {
            if (typeof window.loadScript === 'function') {
                clearInterval(waitForCore);
                try {
                    await window.loadScript('assets/js/modules/dashboards/cobranzas.js');
                    await window.loadScript('assets/js/widgets/cobranzas.js');
                    await window.loadScript('assets/js/widgets/cartera.js');
                    
                    if(typeof loadDebtors === 'function') loadDebtors();
                } catch(e) { console.error("Error loading billing scripts", e); }
            }
        }, 100);
    });
</script>