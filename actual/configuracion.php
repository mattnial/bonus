<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<div class="p-6 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <i class="fas fa-cogs text-blue-600"></i> Configuración del Sistema
    </h2>

    <div class="flex border-b border-gray-200 mb-6 overflow-x-auto no-scrollbar">
        <button onclick="switchConfigTab('staff')" id="tab-staff" class="px-6 py-3 text-blue-600 border-b-2 border-blue-600 font-bold focus:outline-none whitespace-nowrap transition">
            <i class="fas fa-users-cog"></i> Personal
        </button>
        <button onclick="switchConfigTab('menus')" id="tab-menus" class="px-6 py-3 text-gray-500 hover:text-blue-600 font-medium focus:outline-none whitespace-nowrap transition border-b-2 border-transparent">
            <i class="fas fa-list-ul"></i> Permisos Menú
        </button>
        <button onclick="switchConfigTab('import')" id="tab-import" class="px-6 py-3 text-gray-500 hover:text-blue-600 font-medium focus:outline-none whitespace-nowrap transition border-b-2 border-transparent">
            <i class="fas fa-file-excel"></i> Importar Órdenes
        </button>
        <button onclick="switchConfigTab('sequences')" id="tab-sequences" class="px-6 py-3 text-gray-500 hover:text-blue-600 font-medium focus:outline-none whitespace-nowrap transition border-b-2 border-transparent">
            <i class="fas fa-sort-numeric-up"></i> Numeración
        </button>
    </div>

    <div id="view-staff" class="config-view animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-700">Lista de Empleados</h3>
            <button onclick="document.getElementById('newStaffModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow font-bold text-sm transition">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </button>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Rol</th>
                        <th class="px-6 py-4">Acciones</th>
                    </tr>
                </thead>
                <tbody id="staffTableBody" class="divide-y divide-gray-100">
                    <tr><td colspan="3" class="text-center py-10 text-gray-400 italic">Cargando personal...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="view-menus" class="config-view hidden animate-fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Control de Acceso</h3>
                        <p class="text-xs text-gray-500">Asigna qué módulos puede ver cada empleado.</p>
                    </div>
                    <button onclick="saveMenuConfig()" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow hover:bg-green-700 transition flex items-center gap-2">
                        <i class="fas fa-save"></i> GUARDAR CAMBIOS
                    </button>
                </div>
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Empleado</label>
                        <select id="menuUserSelect" onchange="loadUserPermissions(this.value)" class="w-full border rounded-lg p-2.5 bg-gray-50 font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 outline-none h-64" size="10">
                            <option>Cargando...</option>
                        </select>
                    </div>
                    <div class="w-full md:w-2/3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Módulos Permitidos</label>
                        <div id="menuCheckboxes" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                            <div class="text-center py-10 text-gray-400 italic">Selecciona un empleado primero...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 h-fit">
                <h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle"></i> Importante</h3>
                <p class="text-sm text-blue-700 leading-relaxed">
                    Los cambios se aplicarán la próxima vez que el usuario inicie sesión.
                </p>
            </div>
        </div>
    </div>

    <div id="view-import" class="config-view hidden animate-fade-in">
        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center">
            <div class="max-w-md mx-auto">
                <i class="fas fa-cloud-upload-alt text-6xl text-green-100 mb-4 block"></i>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Cargar Archivo (.xlsx)</h3>
                <form id="excelForm" class="space-y-4">
                    <input type="file" id="excelInput" accept=".xlsx, .xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer border border-dashed border-gray-300 p-8 rounded-xl hover:bg-gray-50 transition"/>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow transition flex justify-center items-center gap-2">
                        <i class="fas fa-upload"></i> PROCESAR DATOS
                    </button>
                </form>
                <div id="importLog" class="mt-6 text-left text-xs bg-gray-900 text-green-400 p-4 rounded-lg font-mono hidden h-48 overflow-y-auto"></div>
            </div>
        </div>
    </div>

    <div id="view-sequences" class="config-view hidden animate-fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit lg:col-span-1">
                <h3 class="font-bold text-gray-800 mb-1 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-edit text-blue-600"></i> Editar Secuencia
                </h3>
                <p class="text-xs text-gray-500 mb-4">Selecciona una fila de la tabla para editar.</p>
                
                <form id="seqForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo de Documento</label>
                        <select id="seqType" class="w-full border rounded-lg p-2.5 bg-gray-50 font-bold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="CONTRATO">CONTRATO</option>
                            <option value="ANEXO">ANEXO (Hoja Técnica)</option>
                            <option value="CAMBIO_TITULAR">CAMBIO DE TITULAR</option>
                            <option value="CAMBIO_DOMICILIO">CAMBIO DE DOMICILIO</option>
                            <option value="CAMBIO_PLAN">CAMBIO DE PLAN</op tion>
                            <option value="RETIRO">RETIRO / BAJA</option>
                            <option value="REUBICACION">REUBICACIÓN</option>
                            <option value="PAUSA">PAUSA / SUSPENSIÓN</option>
                            <option value="CERTIFICADO">CERTIFICADO</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sucursal</label>
                        <select id="seqBranch" class="w-full border rounded-lg p-2.5 bg-gray-50 font-bold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="VILCA">Vilcabamba (VILCA)</option>
                            <option value="LOJA">Loja (LOJA)</option>
                            <option value="PALAN">Palanda (PALAN)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prefijo</label>
                            <input type="text" id="seqPrefix" placeholder="Ej: CON-" class="w-full border rounded-lg p-2.5 font-mono text-center uppercase outline-none focus:border-blue-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Último Número Usado</label>
                            <input type="number" id="seqVal" placeholder="0" class="w-full border rounded-lg p-2.5 font-mono text-lg font-bold text-blue-600 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow transition flex justify-center gap-2 items-center">
                        <i class="fas fa-save"></i> GUARDAR CAMBIOS
                    </button>
                    
                    <div class="bg-blue-50 p-3 rounded border border-blue-100 text-xs text-blue-800 mt-2">
                        <i class="fas fa-info-circle"></i> El sistema sumará +1 a este número para el siguiente documento generado.
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden lg:col-span-2 flex flex-col h-[600px]">
                <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h4 class="font-bold text-gray-700 text-sm uppercase">Secuencias Actuales</h4>
                    <button onclick="loadSequences()" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-sync-alt"></i> Actualizar</button>
                </div>
                
                <div class="overflow-y-auto flex-1 p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 text-gray-500 text-xs uppercase font-bold sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 border-b">Documento</th>
                                <th class="px-6 py-3 border-b">Sucursal</th>
                                <th class="px-6 py-3 border-b text-center">Prefijo</th>
                                <th class="px-6 py-3 border-b text-right">Contador Actual</th>
                            </tr>
                        </thead>
                        <tbody id="seqTableBody" class="divide-y divide-gray-100">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="newStaffModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-lg"><i class="fas fa-user-plus"></i> Gestión de Empleado</h3>
            <button onclick="closeStaffModal()" class="hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="staffForm" onsubmit="handleSaveStaff(event)" class="p-6 space-y-4">
            <input type="hidden" id="staffId"> <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                    <input type="text" id="staffName" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Corporativo</label>
                    <input type="email" id="staffEmail" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña</label>
                    <input type="password" id="staffPass" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Solo para cambiarla">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rol / Cargo</label>
                    <select id="staffRole" class="w-full border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="TECNICO">Técnico</option>
                        <option value="VENTAS">Ventas</option>
                        <option value="COBRANZAS">Cobranzas</option>
                        <option value="RRHH">RRHH</option>
                        <option value="GERENCIA">Gerencia</option>
                        <option value="BODEGA">Bodega</option>
                    </select>
                </div>
            </div>

            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                <label class="block text-xs font-bold text-blue-700 uppercase mb-1">
                    <i class="fas fa-desktop"></i> Vista de Dashboard (Sistema)
                </label>
                <select id="staffDashboard" class="w-full border border-blue-200 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="default">Estándar (Default)</option>
                    <option value="gerencia">Gerencia (Admin)</option>
                    <option value="tecnico">Técnico / Operaciones</option>
                    <option value="ventas">Ventas / Comercial</option>
                    <option value="cobranzas">Cobranzas</option>
                    <option value="rrhh">Recursos Humanos</option>
                    <option value="bodega">Bodega / Inventario</option>
                </select>
                <p class="text-[10px] text-blue-500 mt-1">Define qué diseño visual verá este usuario al entrar.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeStaffModal()" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg font-bold text-sm transition">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-sm shadow transition">GUARDAR USUARIO</button>
            </div>
        </form>
    </div>
</div>
<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

<div id="confirmModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform transition-all scale-100">
        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
            <i class="fas fa-question"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">¿Estás seguro?</h3>
        <p id="confirmMsg" class="text-gray-500 text-sm mb-6 leading-relaxed">Confirma para proceder con esta acción.</p>
        <div class="flex justify-center gap-3">
            <button id="btnConfirmNo" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition active:scale-95">
                Cancelar
            </button>
            <button id="btnConfirmYes" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5 active:scale-95">
                Sí, Confirmar
            </button>
        </div>
    </div>
</div>
<script>
// Funciones para el Modal de Empleados

function openStaffModal(user = null) {
    const modal = document.getElementById('newStaffModal');
    modal.classList.remove('hidden');
    
    if (user) {
        // MODO EDICIÓN:
        // Como ya corregimos el PHP, ahora 'user.email' y 'user.assigned_dashboard' SÍ tienen datos.
        
        document.getElementById('staffId').value = user.id;
        document.getElementById('staffName').value = user.name;
        
        // Aquí se arregla el "undefined"
        document.getElementById('staffEmail').value = user.email || ''; 
        
        document.getElementById('staffRole').value = user.role;
        document.getElementById('staffDashboard').value = user.assigned_dashboard || user.default_view || 'default';
        
        // La contraseña se deja vacía para indicar "no cambiar"
        document.getElementById('staffPass').value = '';
        document.getElementById('staffPass').placeholder = "Dejar vacío para mantener la actual";
        
    } else {
        // MODO CREAR NUEVO
        document.getElementById('staffForm').reset();
        document.getElementById('staffId').value = '';
        document.getElementById('staffDashboard').value = 'default';
        document.getElementById('staffPass').placeholder = "Requerida para nuevos";
    }
}

function closeStaffModal() {
    document.getElementById('newStaffModal').classList.add('hidden');
}

async function handleSaveStaff(e) {
    e.preventDefault();

    // 1. REEMPLAZO DE CONFIRM(): Usamos el modal bonito
    // El 'await' espera a que el usuario haga clic en Sí o No
    if (!await askConfirm("¿Estás seguro de guardar los cambios de este usuario?")) {
        return; // Si dice que no, cancelamos todo
    }

    const formData = new FormData();
    formData.append('id', document.getElementById('staffId').value);
    formData.append('name', document.getElementById('staffName').value);
    formData.append('email', document.getElementById('staffEmail').value);
    formData.append('password', document.getElementById('staffPass').value);
    formData.append('role', document.getElementById('staffRole').value);
    formData.append('assigned_dashboard', document.getElementById('staffDashboard').value);

    try {
        const res = await fetch(`${API_URL}/admin/save_staff_config.php`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            // 2. REEMPLAZO DE ALERT() EXITOSO
            // showToast("Mensaje", "tipo") -> tipos: success, error, warning, info
            showToast("✅ Usuario guardado correctamente", "success");
            
            closeStaffModal();
            loadStaffTable(); 
        } else {
            // 3. REEMPLAZO DE ALERT() ERROR
            showToast("❌ Error: " + data.message, "error");
        }
    } catch (err) {
        showToast("⚠️ Error de conexión con el servidor", "error");
    }
}


// --- A. NAVEGACIÓN DE PESTAÑAS ---
function switchConfigTab(tab) {
    // 1. Ocultar todas las sub-vistas
    document.querySelectorAll('.config-view').forEach(el => el.classList.add('hidden'));
    
    // 2. Mostrar la seleccionada
    const target = document.getElementById('view-' + tab);
    if (target) target.classList.remove('hidden');
    
    // 3. Resetear estilos de botones
    ['staff', 'menus', 'import', 'sequences'].forEach(t => {
        const btn = document.getElementById('tab-' + t);
        if(!btn) return;
        if(t === tab) {
            btn.className = "px-6 py-3 text-blue-600 border-b-2 border-blue-600 font-bold focus:outline-none whitespace-nowrap transition";
        } else {
            btn.className = "px-6 py-3 text-gray-500 hover:text-blue-600 font-medium focus:outline-none whitespace-nowrap transition border-b-2 border-transparent";
        }
    });

    // 4. Cargar datos específicos
    if(tab === 'staff') loadStaffTable();
    if(tab === 'menus') { loadUsersForConfig(); loadAllMenuItems(); }
    if(tab === 'sequences') loadSequences();
}

// --- B. CARGA DE DATOS ---
async function loadStaffTable() {
    const tbody = document.getElementById('staffTableBody');
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}/admin/get_staff_list.php`);
        const users = await res.json();
        
        tbody.innerHTML = '';
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-10">No hay empleados</td></tr>';
            return;
        }

        users.forEach(u => {
            tbody.innerHTML += `
                <tr class="hover:bg-gray-50 transition border-b">
                    <td class="px-6 py-4 font-bold text-gray-800">${u.name}</td>
                    <td class="px-6 py-4"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">${u.role}</span></td>
                    <td class="px-6 py-4">
                        <button onclick='openStaffModal(${JSON.stringify(u)})' class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
        });
    } catch (e) { 
        console.error("Error tabla staff:", e);
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-10 text-red-500">Error de conexión</td></tr>';
    }
}

// ... (Mantén aquí tus funciones de loadUsersForConfig, loadAllMenuItems, saveMenuConfig, etc. tal como las tienes) ...

// --- C. EL OBSERVADOR MÁGICO (Detecta cuando la vista se activa) ---
const observerConfig = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class') {
            const isHidden = mutation.target.classList.contains('hidden');
            // Si el contenedor 'view-config' deja de tener la clase 'hidden', activamos la carga
            if (!isHidden) {
                console.log("Configuración detectada como visible. Cargando...");
                switchConfigTab('staff'); 
            }
        }
    });
});

// Iniciamos la observación del contenedor padre
const configContainer = document.getElementById('view-config');
if (configContainer) {
    observerConfig.observe(configContainer, { attributes: true });
}

// Ejecución inicial por si acaso ya está visible
document.addEventListener('DOMContentLoaded', () => {
    if (typeof API_URL === 'undefined') window.API_URL = 'api';
    if (configContainer && !configContainer.classList.contains('hidden')) {
        switchConfigTab('staff');
    }
});

//Nuevos scripts
// =========================================================
// LOGICA DE NUMERACIÓN (SECUENCIAS) - VERSIÓN INTELIGENTE
// =========================================================

// Variable global para guardar los datos
window.currentSequencesList = [];

// 1. CARGAR DATOS DE LA BD
window.loadSequences = async function() {
    const tbody = document.getElementById('seqTableBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</td></tr>';

    try {
        // Agregamos un timestamp para evitar que el navegador guarde caché viejo
        const res = await fetch(`${API_URL}/admin/get_sequences.php?t=${Date.now()}`);
        const data = await res.json();
        
        window.currentSequencesList = data; // Guardar en memoria

        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400">No hay secuencias configuradas.</td></tr>';
            return;
        }

        data.forEach((seq, index) => {
            let branchBadge = 'bg-gray-100 text-gray-600';
            if (seq.branch === 'VILCA') branchBadge = 'bg-green-100 text-green-700';
            if (seq.branch === 'LOJA') branchBadge = 'bg-blue-100 text-blue-700';
            if (seq.branch === 'PALAN') branchBadge = 'bg-orange-100 text-orange-700';

            tbody.innerHTML += `
                <tr class="hover:bg-blue-50 cursor-pointer transition group border-b" onclick="fillSeqFormFromIndex(${index})">
                    <td class="px-6 py-4 font-bold text-gray-700 group-hover:text-blue-700">${seq.doc_type}</td>
                    <td class="px-6 py-4">
                        <span class="${branchBadge} px-2 py-1 rounded text-xs font-bold">${seq.branch}</span>
                    </td>
                    <td class="px-6 py-4 text-center font-mono text-xs text-gray-500">${seq.prefix || ''}</td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-lg text-blue-600">
                        ${seq.current_value}
                    </td>
                </tr>
            `;
        });
    } catch (e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-400">Error al conectar con el servidor</td></tr>';
    }
};

// 2. LLENAR FORMULARIO DESDE LA TABLA (Clic en fila)
window.fillSeqFormFromIndex = function(index) {
    const seq = window.currentSequencesList[index];
    if(!seq) return;

    // Rellenar campos
    document.getElementById('seqType').value = seq.doc_type;
    setBranchValue(seq.branch); // Función auxiliar para manejar sucursales nuevas
    document.getElementById('seqPrefix').value = seq.prefix || '';
    document.getElementById('seqVal').value = seq.current_value;
    
    highlightInput();
};

// 3. BUSCAR AUTOMÁTICAMENTE AL CAMBIAR LOS SELECTS (¡NUEVO!)
// Si el usuario cambia el select manualmente, buscamos si ya existe ese dato en la lista
function autoFindSequence() {
    const type = document.getElementById('seqType').value;
    const branch = document.getElementById('seqBranch').value;

    // Buscar en la lista cargada en memoria
    const found = window.currentSequencesList.find(s => s.doc_type === type && s.branch === branch);

    if (found) {
        // Si existe, mostramos sus datos
        document.getElementById('seqPrefix').value = found.prefix || '';
        document.getElementById('seqVal').value = found.current_value;
        highlightInput();
    } else {
        // Si no existe, limpiamos para que ingrese uno nuevo (empieza en 0 o 1)
        document.getElementById('seqPrefix').value = ''; 
        document.getElementById('seqVal').value = '';
    }
}

// Conectamos los eventos "change" a la función de búsqueda
document.addEventListener('DOMContentLoaded', () => {
    const sType = document.getElementById('seqType');
    const sBranch = document.getElementById('seqBranch');
    
    if(sType && sBranch) {
        sType.addEventListener('change', autoFindSequence);
        sBranch.addEventListener('change', autoFindSequence);
    }
});


// 4. GUARDAR CAMBIOS
const formSeq = document.getElementById('seqForm');
if(formSeq) {
    formSeq.addEventListener('submit', async function(e) {
        e.preventDefault();

        const data = {
            doc_type: document.getElementById('seqType').value,
            branch: document.getElementById('seqBranch').value,
            prefix: document.getElementById('seqPrefix').value.toUpperCase(),
            current_value: document.getElementById('seqVal').value
        };

        if (data.current_value === '') return alert("Por favor ingresa un número válido");

        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;

        try {
            const res = await fetch(`${API_URL}/admin/update_settings_single.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (result.success) {
                if(typeof showToast === 'function') showToast("✅ Secuencia guardada", "success");
                else alert("✅ Guardado");
                
                loadSequences(); // Recargar tabla
            } else {
                alert("❌ Error: " + (result.message || 'Desconocido'));
            }
        } catch (error) {
            alert("❌ Error de conexión");
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

// --- UTILIDADES ---
function setBranchValue(branch) {
    const select = document.getElementById('seqBranch');
    select.value = branch;
    // Si no existe (caso raro), lo creamos
    if(select.value !== branch) {
        let opt = new Option(branch, branch);
        select.add(opt);
        select.value = branch;
    }
}

function highlightInput() {
    const input = document.getElementById('seqVal');
    input.classList.add('bg-yellow-100');
    setTimeout(() => input.classList.remove('bg-yellow-100'), 500);
}
</script>