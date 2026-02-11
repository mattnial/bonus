<div id="confirmModal" class="fixed inset-0 bg-black/60 hidden z-[10001] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 text-center max-w-sm w-full animate-fade-in-up">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
            <i class="fas fa-question text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">¿Estás seguro?</h3>
        <p class="text-sm text-gray-500 mb-6" id="confirmMsg">Acción irreversible.</p>
        <div class="flex justify-center gap-3">
            <button id="btnConfirmNo" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">Cancelar</button>
            <button id="btnConfirmYes" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 transition">Confirmar</button>
        </div>
    </div>
</div>

<div id="createTicketModal" class="fixed inset-0 bg-black/60 hidden z-[9997] flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in-up">
        <div class="bg-gray-800 p-4 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg"><i class="fas fa-ticket-alt mr-2"></i> Crear Nuevo Ticket</h3>
            <button type="button" onclick="closeCreateTicketModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="formCreateTicket" class="p-6 space-y-4">
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Cliente</label>
                <div id="clientSearchContainer" class="relative">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-search"></i></span>
                        <input type="text" id="clientAutocompleteInput" class="w-full border rounded pl-10 p-2 bg-gray-50 focus:ring-2 focus:ring-blue-300 outline-none transition" placeholder="Buscar por nombre o cédula..." autocomplete="off">
                        <span id="searchSpinner" class="absolute inset-y-0 right-0 flex items-center pr-3 text-blue-500 hidden"><i class="fas fa-spinner fa-spin"></i></span>
                    </div>
                    <ul id="clientSearchResultsList" class="hidden absolute z-50 w-full bg-white border border-gray-200 rounded-b-lg shadow-xl max-h-60 overflow-y-auto mt-1 divide-y divide-gray-100"></ul>
                </div>
                <div id="clientLockedContainer" class="hidden flex items-center bg-blue-50 border border-blue-200 rounded p-2 text-blue-800">
                    <i class="fas fa-user-check mr-2 text-xl"></i>
                    <span id="lockedClientName" class="font-bold text-lg ml-2">Nombre Cliente</span>
                    <button type="button" onclick="unlockClient()" class="ml-auto text-xs text-blue-500 underline hover:text-blue-700">Cambiar</button>
                </div>
                <input type="hidden" name="client_id" id="finalClientId">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Motivo / Tipo de Trabajo</label>
                <select id="ticketTypeSelector" onchange="toggleTicketUI(this)" class="w-full border-2 border-blue-100 rounded p-2 focus:border-blue-500 outline-none font-medium text-gray-700">
                    <option value="">-- Seleccione --</option>
                    <optgroup label="Técnico (Genera Orden)">
                        <option value="INSPECCION">Inspección / Visita</option>
                        <option value="INSTALACION_FO">Instalación Fibra Óptica</option>
                        <option value="INSTALACION_RE">Instalación Radio Enlace</option>
                        <option value="SOPORTE_FO">Soporte / Reparación F.O.</option>
                        <option value="SOPORTE_RE">Soporte / Reparación R.E.</option>
                        <option value="MIGRACION">Migración</option>
                        <option value="RETIRO">Retiro de Equipos</option>
                        <option value="CAMBIO_DOM_FO">Cambio Domicilio F.O.</option>
                        <option value="CAMBIO_DOM_RE">Cambio Domicilio R.E.</option>
                    </optgroup>
                    <optgroup label="Administrativo">
                        <option value="CAMBIO_CLAVE">Cambio de Clave WIFI</option>
                        <option value="REACTIVACION">Reactivación / Corte</option>
                        <option value="FACTURACION">Facturación</option>
                    </optgroup>
                    <option value="OTRO">OTRO (Especificar manualmente...)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prioridad</label>
                <select id="tPriority" class="w-full border-gray-300 border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white font-bold text-gray-700">
                    <option value="BAJA">🟢 Baja</option>
                    <option value="MEDIA" selected>🟡 Media</option>
                    <option value="ALTA">🔴 Alta</option>
                    <option value="URGENTE">🔥 URGENTE</option>
                </select>
            </div>
            <div id="manualSubjectContainer" class="hidden animate-fade-in-down mt-2">
                <input type="text" id="manualSubjectInput" class="w-full border border-gray-300 rounded p-2 bg-yellow-50 focus:bg-white" placeholder="Ej: Revisar router quemado...">
            </div>
            <input type="hidden" name="subject" id="finalSubject">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Detalles Adicionales</label>
                <textarea name="description" rows="3" class="w-full border rounded p-2" placeholder="Detalles específicos para el técnico..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeCreateTicketModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded transition">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-blue-900 text-white font-bold rounded hover:bg-blue-800 shadow-md transition transform active:scale-95">Guardar Ticket</button>
            </div>
        </form>
    </div>
</div>

<div id="ticketDetailModal" class="fixed inset-0 bg-black/80 hidden z-[9990] flex items-center justify-center p-2 lg:p-4 backdrop-blur-sm transition-opacity">
    <div class="bg-white w-full max-w-6xl h-[90vh] rounded-2xl shadow-2xl flex flex-col border border-gray-700 relative overflow-hidden">
        
        <div class="bg-gray-900 text-white px-6 py-4 flex justify-between items-center shrink-0 shadow-md relative z-[9992]">
            <div>
                <div class="flex items-center gap-3">
                    <span id="chatTicketId" class="bg-blue-600 px-3 py-1 rounded-md text-sm font-mono font-bold tracking-wider shadow-sm">#000</span>
                    <h3 id="chatSubject" class="font-bold text-xl truncate max-w-md">Cargando...</h3>
                </div>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-3">
                    <span class="flex items-center gap-1"><i class="fas fa-building text-blue-400"></i> <span id="chatDept">...</span></span>
                    <span class="text-gray-700">|</span>
                    <span class="flex items-center gap-1">
                        <i class="fas fa-user-astronaut text-orange-400"></i> 
                        Asignado a: <span id="chatStaff" class="text-white font-bold underline decoration-blue-500 underline-offset-4">Nadie</span>
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-3">

    <div class="relative group">
        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none text-blue-200 group-hover:text-white transition-colors z-10">
            <i class="fas fa-chevron-down text-[10px]"></i>
        </div>

        <select id="ticketStatusSelect" onchange="window.changeTicketStatus()" 
            class="appearance-none w-44 bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/10 rounded-lg pl-4 pr-10 py-2 outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer transition-all backdrop-blur-sm tracking-wide shadow-sm">
            
            <option value="ABIERTO" class="bg-gray-800 text-white">🟢 &nbsp; ABIERTO</option>
            <option value="EN_PROCESO" class="bg-gray-800 text-white">🟡 &nbsp; EN PROCESO</option>
            <option value="RESUELTO" class="bg-gray-800 text-white">🟣 &nbsp; RESUELTO</option>
            <option value="CERRADO" class="bg-gray-800 text-white">⚫ &nbsp; CERRADO</option>
        </select>
    </div>

    <button onclick="document.getElementById('ticketDetailModal').classList.add('hidden')" 
        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 hover:bg-red-500/90 text-gray-300 hover:text-white border border-white/10 transition-all duration-200">
        <i class="fas fa-times text-sm"></i>
    </button>
    
</div>
        </div>

        <div class="flex-1 flex overflow-hidden relative z-[9991]">
            <div class="flex-1 flex flex-col bg-slate-100 relative">
                <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4 scroll-smooth"></div>
                
                <div class="bg-white p-4 border-t border-gray-200 flex gap-3 items-end shadow-lg z-20">
                    <textarea id="replyInput" rows="1" class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white resize-none" placeholder="Escribe una respuesta..."></textarea>
                    <div class="flex flex-col gap-2 shrink-0">
                        <button onclick="sendReply(false)" class="bg-blue-600 hover:bg-blue-700 text-white w-10 h-10 rounded-xl shadow transition flex items-center justify-center" title="Enviar Público"><i class="fas fa-paper-plane"></i></button>
                        <button onclick="sendReply(true)" class="bg-yellow-500 hover:bg-yellow-600 text-white w-10 h-8 rounded-lg shadow text-xs transition flex items-center justify-center" title="Nota Privada"><i class="fas fa-lock"></i></button>
                    </div>
                </div>
            </div>

            <div class="w-80 bg-white border-l border-gray-200 p-6 hidden lg:flex flex-col overflow-y-auto shadow-xl z-20">
                <div class="mb-8">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-wider">Descripción Original</h4>
                    <div class="bg-blue-50/50 p-5 rounded-2xl text-sm text-gray-700 border border-blue-100/50 relative">
                        <p id="chatDescription" class="relative z-10 leading-relaxed">...</p>
                    </div>
                </div>
                
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-wider">Gestión</h4>
                <div class="space-y-3">
                    <button onclick="openAssignModal()" class="w-full text-left px-4 py-4 rounded-xl bg-gray-50 hover:bg-blue-50 text-sm text-gray-700 flex items-center gap-4 transition group border border-gray-100 hover:border-blue-200 hover:shadow-md relative z-10">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition shadow-sm"><i class="fas fa-user-plus"></i></div>
                        <div><span class="font-bold block text-gray-800">Asignar Técnico</span><span class="text-[10px] text-gray-500">Delegar caso</span></div>
                    </button>
                    
                    <input type="file" id="evidenceInput" class="hidden" accept="image/*,.pdf" onchange="uploadEvidence(this)">
                    <button onclick="document.getElementById('evidenceInput').click()" class="w-full text-left px-4 py-4 rounded-xl bg-gray-50 hover:bg-orange-50 text-sm text-gray-700 flex items-center gap-4 transition group border border-gray-100 hover:border-orange-200 hover:shadow-md relative z-10">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center group-hover:bg-orange-600 group-hover:text-white transition shadow-sm"><i class="fas fa-file-upload"></i></div>
                        <div><span class="font-bold block text-gray-800">Subir Evidencia</span><span class="text-[10px] text-gray-500">Adjuntar archivo</span></div>
                    </button>

                    <button id="btnGenerateOrder" onclick="window.generateOrderChat()" class="hidden w-full bg-white border border-gray-200 p-4 rounded-xl shadow-sm hover:shadow-md transition flex items-center gap-4 group text-left mt-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold text-lg group-hover:bg-green-600 group-hover:text-white transition">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700 text-sm group-hover:text-green-700">Generar Orden</p>
                            <p class="text-[10px] text-gray-400">Crear Excel según tipo</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="globalClientModal" class="fixed inset-0 bg-black/60 hidden z-[9980] backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl h-[85vh] flex flex-col overflow-hidden animate-fade-in-up">
        
        <div class="bg-blue-900 p-6 flex justify-between items-start shrink-0">
            <div class="flex gap-4 items-center">
                <div id="modalAvatar" class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center text-2xl font-bold text-white border-2 border-white shadow-lg">--</div>
                <div>
                    <h2 id="modalClientName" class="text-xl font-bold text-white">Cargando...</h2>
                    <p id="modalClientCedula" class="text-blue-200 text-sm font-mono">--</p>
                    <div class="flex gap-2 mt-2">
                        <span id="modalStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-700 text-white uppercase">--</span>
                    </div>
                </div>
            </div>
            <button onclick="document.getElementById('globalClientModal').classList.add('hidden')" class="text-white/70 hover:text-white transition"><i class="fas fa-times text-2xl"></i></button>
        </div>

        <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
            <div class="w-full md:w-1/3 bg-gray-50 p-6 border-r border-gray-200 overflow-y-auto">
                <input type="hidden" id="currentGlobalClientId" value="">
                
                <button onclick="openTicketFromGlobal()" class="w-full bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-lg font-bold shadow-lg mb-6 flex items-center justify-center gap-2 transition transform hover:-translate-y-1">
                    <i class="fas fa-plus-circle"></i> NUEVO TICKET
                </button>
                
                <div class="space-y-4 text-sm">
                    
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">
                            <i class="fas fa-wifi text-blue-500 mr-1"></i> PLAN CONTRATADO:
                        </p>
                        
                        <p id="modalPlan" class="text-blue-900 font-bold text-sm leading-snug break-words">
                            --
                        </p>

                        <div class="flex gap-2 text-xs text-gray-500 mt-1">
                            <span>IP: <span id="modalIp" class="font-mono text-gray-700">--</span></span>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200 shadow-inner">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-xs font-bold text-yellow-700 uppercase"><i class="fas fa-sticky-note mr-1"></i> Notas / Observaciones</label>
                            <button onclick="saveClientNote()" class="text-[10px] bg-yellow-200 hover:bg-yellow-300 text-yellow-800 px-2 py-1 rounded font-bold transition">GUARDAR</button>
                        </div>
                        <textarea id="modalNotes" rows="4" class="w-full text-xs p-2 rounded border border-yellow-300 bg-yellow-50 focus:bg-white focus:ring-1 focus:ring-yellow-500 outline-none resize-none text-gray-700" placeholder="Escribe notas importantes sobre el cliente aquí..."></textarea>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Contacto</p>
                        <div class="flex items-center gap-2 text-gray-700"><i class="fas fa-phone w-5 text-center text-blue-500"></i><span id="modalPhone">--</span></div>
                        <div class="flex items-center gap-2 text-gray-700 mt-2"><i class="fas fa-envelope w-5 text-center text-blue-500"></i><span id="modalEmail" class="truncate">--</span></div>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Dirección</p>
                        <p id="modalAddress" class="text-gray-700 text-xs leading-relaxed">--</p>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-2/3 bg-white flex flex-col">
                <div class="bg-gray-100 p-3 border-b border-gray-200 font-bold text-gray-700 text-sm">Historial de Soporte</div>
                <div class="flex-1 overflow-y-auto p-0" id="modalTicketsList">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-2"></i> Cargando...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="assignModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[99999] backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm flex flex-col overflow-hidden max-h-[80vh] border border-gray-200">
        
        <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center shrink-0">
            <h3 class="font-bold text-gray-800">Seleccionar Técnico</h3>
            <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 text-gray-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="techListContainer" class="flex-1 overflow-y-auto bg-white p-0">
            <div class="p-4 text-center text-gray-400">Cargando...</div>
        </div>
        
        <div class="bg-gray-50 px-4 py-2 border-t text-xs text-center text-gray-400">
            Selecciona un usuario para asignar
        </div>
    </div>
</div>

<div id="rrhhModal" class="hidden fixed inset-0 bg-black/60 z-[150] flex items-center justify-center backdrop-blur-sm"><div class="bg-white p-6 rounded shadow-xl w-full max-w-lg"><h3 id="rrhhName" class="font-bold text-lg mb-4">Empleado</h3><div id="sanctionsList" class="mb-4"></div><form id="sanctionForm" onsubmit="handleSanctionSubmit(event)"><select id="sancType" class="border p-2 w-full mb-2 rounded" onchange="toggleAmount(this.value)"><option value="">Tipo...</option><option value="MEMO">Llamado Atención</option><option value="MULTA">Multa Económica</option></select><input id="sancAmount" type="number" class="hidden border p-2 w-full mb-2 rounded" placeholder="Monto $"><textarea id="sancReason" class="border p-2 w-full mb-2 rounded" placeholder="Motivo"></textarea><input type="file" id="sancFile" class="mb-2"><div class="flex justify-end gap-2"><button type="button" onclick="document.getElementById('rrhhModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button><button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Guardar</button></div></form></div></div>
<div id="agreementModal" class="hidden fixed inset-0 bg-black/60 z-[160] flex items-center justify-center backdrop-blur-sm"><div class="bg-white p-6 rounded shadow-xl"><h3 class="font-bold mb-4">Nuevo Convenio</h3><input type="date" id="agDate" class="border p-2 w-full mb-2 rounded"><textarea id="agNotes" class="border p-2 w-full mb-4 rounded" placeholder="Nota"></textarea><div class="flex justify-end gap-2"><button onclick="document.getElementById('agreementModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</button><button onclick="createAgreement()" class="px-4 py-2 bg-purple-600 text-white rounded">Crear</button></div></div></div>
<div id="clientFormModal" class="hidden fixed inset-0 bg-black/60 z-[150] flex items-center justify-center backdrop-blur-sm"><div class="bg-white p-6 rounded w-full max-w-2xl"><h3 class="font-bold mb-4">Cliente</h3><form id="saveClientForm" onsubmit="handleSaveClient(event)"><input type="hidden" id="cliId"><div class="grid grid-cols-2 gap-2"><input id="cliName" placeholder="Nombre" class="border p-2 rounded"><input id="cliCedula" placeholder="Cédula" class="border p-2 rounded"><input id="cliPhone" placeholder="Celular" class="border p-2 rounded"><input id="cliEmail" placeholder="Email" class="border p-2 rounded"><input id="cliPlan" placeholder="Plan" class="border p-2 rounded"><input id="cliCoords" placeholder="GPS" class="border p-2 rounded"></div><textarea id="cliAddress" class="border p-2 w-full mt-2 rounded" placeholder="Dirección"></textarea><div class="mt-2"><label><input type="checkbox" id="checkInternet"> Internet</label> <label><input type="checkbox" id="checkTV"> TV</label></div><div class="flex justify-end gap-2 mt-4"><button type="button" onclick="document.getElementById('clientFormModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button><button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Guardar</button></div></form></div></div>

<div id="toast-container" class="fixed top-5 left-5 z-[200005] flex flex-col gap-3 pointer-events-none"></div>

<script>
    const CURRENT_USER_ID = <?php echo $_SESSION['staff_id'] ?? 0; ?>;
    const CURRENT_ROLE = "<?php echo $_SESSION['role'] ?? 'GUEST'; ?>";
</script>

<script src="assets/js/config.js"></script>
<script src="assets/js/core.js"></script>
<script src="assets/js/modules/clients.js"></script>
<script src="assets/js/modules/tickets.js?v=<?php echo rand(100,999); ?>"></script> 
<script src="assets/js/modules/inventory.js"></script>
<script src="assets/js/modules/rrhh.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/modules/reports.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/modules/admin_menu.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/modules/tickets_chat.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/modules/naps_data.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script src="assets/js/widgets/historial.js"></script>
<script src="assets/js/widgets/pagos.js"></script>
<script src="assets/js/widgets/incumplimiento.js"></script>
<script src="assets/js/modules/dashboards/cobranzas.js"></script>
<script src="assets/js/widgets/cobranzas.js"></script>
<script src="assets/js/widgets/alertas.js"></script>

<script src="assets/js/menu_config.js?v=1.0"></script> 
<script src="assets/js/core.js?v=1.0"></script>
</body>
</html>