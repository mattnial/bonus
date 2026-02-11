<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Generador de Documentos</h2>
        <div class="text-xs text-right text-gray-500">
            Usuario: <span class="font-bold text-blue-800"><?php echo $_SESSION['staff_name'] ?? 'Admin'; ?></span>
            <input type="hidden" id="currentStaffId" value="<?php echo $_SESSION['staff_id'] ?? 1; ?>">
            <input type="hidden" id="currentStaffName" value="<?php echo $_SESSION['staff_name'] ?? 'Admin'; ?>">
        </div>
    </div>

    <div class="relative mb-8 z-50"> 
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Buscar Cliente (Nombre o Cédula):</label>
        
        <div class="relative">
            <input type="text" id="docSearchInput" 
                   autocomplete="off"
                   placeholder="Escribe aquí... (Ej: Perez)" 
                   class="w-full border-2 border-blue-100 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 text-lg shadow-sm transition"
                   onkeyup="buscarClienteContrato(this.value)">
            
            <div id="searchSpinner" class="absolute right-4 top-4 hidden text-blue-500">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            
            <div id="searchResults" class="absolute w-full bg-white shadow-2xl rounded-b-xl border border-gray-100 max-h-80 overflow-y-auto hidden" style="z-index: 100;">
                </div>
        </div>

        <div id="selectedClientPanel" class="hidden mt-4 bg-green-50 border border-green-200 rounded-xl p-4 flex justify-between items-center animate-fade-in">
            <div>
                <p class="text-[10px] text-green-600 font-bold uppercase tracking-widest">CLIENTE SELECCIONADO</p>
                <h3 class="text-xl font-bold text-gray-800" id="lblClientName"></h3>
                <p class="text-sm text-gray-600 mt-1">
                    <i class="fas fa-id-card"></i> <span id="lblClientCedula"></span> | 
                    <i class="fas fa-wifi"></i> <span id="lblClientPlan" class="font-bold"></span>
                </p>
                <input type="hidden" id="selectedClientId">
            </div>
            <button onclick="clearSelection()" class="text-gray-400 hover:text-red-500 px-4">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Botonera de Acciones -->
    <div id="docActions" class="grid grid-cols-2 md:grid-cols-4 gap-4 opacity-40 pointer-events-none transition-all duration-300 relative z-0">
        <button onclick="openDocModal('CAMBIO_TITULAR')" class="btn-doc bg-blue-50 hover:bg-blue-100 text-blue-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-exchange-alt text-2xl"></i> <span class="text-xs font-bold">Cambio Titular</span>
        </button>
        <button onclick="openDocModal('CAMBIO_DOMICILIO')" class="btn-doc bg-orange-50 hover:bg-orange-100 text-orange-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-truck text-2xl"></i> <span class="text-xs font-bold">Cambio Domicilio</span>
        </button>
        <button onclick="openDocModal('REUBICACION')" class="btn-doc bg-teal-50 hover:bg-teal-100 text-teal-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-arrows-alt text-2xl"></i> <span class="text-xs font-bold">Mover Router</span>
        </button>
        <button onclick="openDocModal('CAMBIO_PLAN')" class="btn-doc bg-purple-50 hover:bg-purple-100 text-purple-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-tachometer-alt text-2xl"></i> <span class="text-xs font-bold">Cambio Plan</span>
        </button>
        <button onclick="openDocModal('PAUSA')" class="btn-doc bg-gray-50 hover:bg-gray-100 text-gray-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-pause text-2xl"></i> <span class="text-xs font-bold">Solicitud Pausa</span>
        </button>
        <button onclick="openDocModal('RETIRO')" class="btn-doc bg-red-50 hover:bg-red-100 text-red-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-user-times text-2xl"></i> <span class="text-xs font-bold">Retiro / Baja</span>
        </button>
        <button onclick="openDocModal('INSPECCION')" class="btn-doc bg-indigo-50 hover:bg-indigo-100 text-indigo-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-search-location text-2xl"></i> <span class="text-xs font-bold">Inspección</span>
        </button>
        <button onclick="openDocModal('CERTIFICADO')" class="btn-doc bg-green-50 hover:bg-green-100 text-green-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-certificate text-2xl"></i> <span class="text-xs font-bold">Certificado</span>
        </button>
        <button onclick="openDocModal('REACTIVACION')" class="btn-doc bg-lime-50 hover:bg-lime-100 text-lime-700 p-4 rounded-xl border flex flex-col items-center gap-2 transform hover:-translate-y-1 transition">
            <i class="fas fa-power-off text-2xl"></i> <span class="text-xs font-bold">Reactivación</span>
        </button>
    </div>
</div>

<!-- Modal Generador -->
<div id="docGeneratorModal" class="fixed inset-0 bg-black/60 hidden z-[200] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in-up">
        <div class="bg-gray-800 text-white px-4 py-3 flex justify-between">
            <h3 class="font-bold">Completar Datos</h3>
            <button onclick="closeDocModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="docForm" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            <input type="hidden" id="formDocType">
            
            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 mb-4">
                <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Sucursal (Numeración)</label>
                <select name="branch" id="docBranchSelector" class="w-full border border-blue-200 rounded-lg p-2 bg-white font-bold text-blue-900 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="VILCA">Vilcabamba (VILCA)</option>
                    <option value="LOJA">Loja (LOJA)</option>
                    <option value="PALAN">Palanda (PALAN)</option>
                </select>
                <p class="text-[10px] text-blue-500 mt-1">Elige la ciudad para usar la secuencia correcta.</p>
            </div>

            <!-- Campos Dinámicos -->
            <div id="f-plan" class="dyn-field hidden space-y-3">
                <label class="block text-xs font-bold text-gray-500">Nuevo Plan</label>
                <select name="target_plan" class="w-full border rounded p-2 text-sm">
                    <option value="STANDAR">ESTÁNDAR</option>
                    <option value="ESENCIAL">ESENCIAL</option>
                    <option value="FAMILIAR">FAMILIAR</option>
                    <option value="PLUS">PLUS PROFESIONAL</option>
                    <option value="PRO">PRO PROFESIONAL</option>
                </select>
                <label class="block text-xs font-bold text-gray-500">Tecnología</label>
                <select name="conn_type" class="w-full border rounded p-2 text-sm">
                    <option value="FIBRA">Fibra Óptica</option>
                    <option value="RADIO">Radio Enlace</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>

            <div id="f-reubicacion" class="dyn-field hidden space-y-3">
                <label class="block text-xs font-bold text-gray-500">Tipo de Movimiento</label>
                <select name="move_type_int" class="w-full border rounded p-2 text-sm">
                    <option value="INTERNA">Interna (Misma casa)</option>
                    <option value="EXTERNA">Externa (Otra casa)</option>
                    <option value="OTRO">Otro</option>
                </select>
                <input type="text" name="new_address_reu" placeholder="Detalle / Nueva Ubicación" class="w-full border rounded p-2 text-sm uppercase">
            </div>

            <div id="f-domicilio" class="dyn-field hidden space-y-3">
                <label class="block text-xs font-bold text-gray-500">Nueva Dirección</label>
                <input type="text" name="new_address_dom" placeholder="Dirección Completa" class="w-full border rounded p-2 text-sm uppercase">
            </div>

            <div id="f-titular" class="dyn-field hidden space-y-3">
                <p class="text-xs font-bold text-blue-600 border-b">Datos Nuevo Dueño</p>
                <input type="text" name="new_name" placeholder="Nombre Completo" class="w-full border rounded p-2 text-sm uppercase">
                <input type="text" name="new_cedula" placeholder="Cédula" class="w-full border rounded p-2 text-sm">
                <input type="text" name="new_address" placeholder="Dirección" class="w-full border rounded p-2 text-sm uppercase">
                <input type="text" name="new_phone" placeholder="Celular" class="w-full border rounded p-2 text-sm">
                <input type="email" name="new_email" placeholder="Email" class="w-full border rounded p-2 text-sm">
            </div>

            <div id="f-motivo" class="dyn-field hidden space-y-3">
                <label class="block text-xs font-bold text-gray-500">Motivo</label>
                <select name="reason_general" class="w-full border rounded p-2 text-sm">
                    <option value="X_CAMBIO">Cambio Domicilio</option>
                    <option value="X_ELEVADO">Costo Elevado</option>
                    <option value="X_PESIMO">Mala Atención</option>
                    <option value="X_OTRO">Otro / Personal</option>
                    <option value="MUDANZA">Inspección por Mudanza</option>
                    <option value="EXTENSOR">Inspección por Extensor</option>
                </select>
                <label class="block text-xs font-bold text-gray-500 mt-2">Fecha Inicio</label>
                <input type="date" name="date_start" value="<?php echo date('Y-m-d'); ?>" class="w-full border rounded p-2 text-sm">
            </div>

            <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded shadow">
                GENERAR Y CREAR TICKET
            </button>
        </form>
    </div>
</div>

<!-- Lógica del Módulo -->
<script src="modules/Sales/assets/contracts.js"></script>
