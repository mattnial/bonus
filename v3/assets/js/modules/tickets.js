// ARCHIVO: assets/js/modules/tickets.js
// VERSIÓN: MAESTRA DEFINITIVA

window.activeTicketId = null;
var ticketSearchTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Si estamos en la página de tickets, cargar la lista
    if (document.getElementById('globalTicketsTable')) {
        loadGlobalTickets();
    }
    // 2. Inicializar el modal de Crear Ticket
    initCreateTicketModal();
});

// --- INICIALIZAR LISTENERS ---
function initCreateTicketModal() {
    const form = document.getElementById('formCreateTicket');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            handleSaveTicketAjax(this);
        });
    }
    const searchInput = document.getElementById('clientAutocompleteInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () { handleClientSearch(this); });
    }
}

// --- ABRIR EL MODAL DE NUEVO TICKET (Esta es la función que te faltaba) ---
window.openCreateTicketModal = function (clientId = null, clientName = null) {
    const modal = document.getElementById('createTicketModal');
    if (!modal) return console.error("Falta modal createTicketModal");

    // Resetear formulario
    const form = modal.querySelector('form');
    if (form) form.reset();

    // Ocultar campo manual
    document.getElementById('manualSubjectContainer').classList.add('hidden');

    // Limpiar input de búsqueda
    const searchIn = document.getElementById('clientAutocompleteInput');
    if (searchIn) {
        searchIn.value = '';
        searchIn.classList.remove('text-green-700', 'font-bold', 'bg-green-50');
    }

    // Configurar si viene con cliente preseleccionado
    const searchCont = document.getElementById('clientSearchContainer');
    const lockCont = document.getElementById('clientLockedContainer');
    const lockName = document.getElementById('lockedClientName');
    const finalId = document.getElementById('finalClientId');

    if (clientId && clientName) {
        // MODO CLIENTE FIJO
        searchCont.classList.add('hidden');
        lockCont.classList.remove('hidden');
        lockName.innerText = clientName;
        finalId.value = clientId;
    } else {
        // MODO BÚSQUEDA
        searchCont.classList.remove('hidden');
        lockCont.classList.add('hidden');
        finalId.value = '';
    }

    // Mostrar modal
    modal.classList.remove('hidden');
};

// --- CERRAR MODALES ---
window.closeCreateTicketModal = () => document.getElementById('createTicketModal').classList.add('hidden');

// --- CAMBIAR CLIENTE (Desbloquear) ---
window.unlockClient = () => {
    document.getElementById('clientSearchContainer').classList.remove('hidden');
    document.getElementById('clientLockedContainer').classList.add('hidden');
    document.getElementById('finalClientId').value = '';
    document.getElementById('clientAutocompleteInput').value = '';
    document.getElementById('clientAutocompleteInput').focus();
};

// --- ABRIR TICKET DESDE EL PERFIL GLOBAL ---
window.openTicketFromGlobal = function () {
    const idInput = document.getElementById('currentGlobalClientId');
    const nameLabel = document.getElementById('modalClientName');
    if (idInput && nameLabel) {
        openCreateTicketModal(idInput.value, nameLabel.innerText);
    } else {
        openCreateTicketModal();
    }
};

// --- LÓGICA VISUAL (Selector OTRO) ---
window.toggleTicketUI = function (selectElement) {
    const manualContainer = document.getElementById('manualSubjectContainer');
    const manualInput = document.getElementById('manualSubjectInput');
    if (selectElement.value === 'OTRO') {
        manualContainer.classList.remove('hidden');
        manualInput.required = true;
        manualInput.focus();
    } else {
        manualContainer.classList.add('hidden');
        manualInput.required = false;
        manualInput.value = "";
    }
};

// --- GUARDAR TICKET (AJAX) ---
window.handleSaveTicketAjax = async function (formElement) {
    // 1. OBTENER EL BOTÓN Y BLOQUEARLO
    const btn = formElement.querySelector('button[type="submit"]') || formElement.querySelector('.btn-save');
    let originalText = "Guardar";
    if (btn) {
        originalText = btn.innerText;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    }

    try {
        // 2. OBTENER MOTIVO
        const selector = formElement.querySelector('#ticketTypeSelector');
        const manualInput = formElement.querySelector('#manualSubjectInput');

        // Soporte para tu nuevo select tSubject si existe
        const newSubjectSelector = document.getElementById('tSubject');

        let finalSubject = "";
        if (newSubjectSelector && newSubjectSelector.offsetParent !== null) {
            finalSubject = newSubjectSelector.value;
        } else if (selector) {
            finalSubject = selector.value === "OTRO" ? manualInput.value.trim() : selector.value;
        }

        if (!finalSubject || finalSubject === "") {
            throw new Error("⚠️ Por favor, seleccione un Motivo/Asunto.");
        }

        // 3. OBTENER PRIORIDAD (MÉTODO FUERZA BRUTA)
        // Buscamos el elemento por ID directamente, ignorando el formulario
        const elPriority = document.getElementById('tPriority');
        let finalPriority = 'MEDIA'; // Valor por defecto

        if (elPriority) {
            finalPriority = elPriority.value;
            console.log("📌 Prioridad capturada del HTML:", finalPriority); // Ver en consola F12
        } else {
            console.warn("⚠️ No se encontró el elemento con id='tPriority'. Se enviará MEDIA.");
        }

        // 4. PREPARAR DATOS (FormData)
        const formData = new FormData(formElement);

        // Sobrescribimos/Forzamos los valores clave
        formData.set('subject', finalSubject);
        formData.set('priority', finalPriority); // <--- AQUÍ ESTÁ LA CLAVE

        // ID del creador (asegurar que exista la variable global, si no, enviar 1)
        const staffId = (typeof CURRENT_USER_ID !== 'undefined') ? CURRENT_USER_ID : 1;
        formData.append('created_by', staffId);

        // 5. ENVIAR
        const res = await fetch('api/tickets/create.php', {
            method: 'POST',
            body: formData
        });

        // Verificamos si la respuesta es JSON válido antes de parsear
        const textResponse = await res.text();
        let result;
        try {
            result = JSON.parse(textResponse);
        } catch (err) {
            console.error("Respuesta del servidor no es JSON:", textResponse);
            throw new Error("Error del servidor (PHP). Revisa la consola.");
        }

        if (result.success) {
            // ÉXITO
            if (typeof showToast === 'function') showToast("✅ Ticket creado correctamente", "success");
            else alert("✅ Ticket creado");

            // Cerrar modal
            if (typeof closeCreateTicketModal === 'function') closeCreateTicketModal();
            else {
                const modal = document.getElementById('createTicketModal');
                if (modal) modal.classList.add('hidden');
            }

            // Recargas
            if (typeof loadGlobalTickets === 'function') loadGlobalTickets();
            if (typeof loadTickets === 'function') loadTickets(); // Para tu dashboard nuevo

        } else {
            throw new Error(result.message || "Error desconocido al crear ticket");
        }

    } catch (e) {
        console.error(e);
        if (typeof showToast === 'function') showToast("❌ " + e.message, "error");
        else alert("Error: " + e.message);
    } finally {
        // Restaurar botón
        if (btn) {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    }
};


window.confirmAssignment = async function () {
    const staffId = document.getElementById('staffSelect').value;
    if (!staffId) return alert("Seleccione un técnico");
    const staff = JSON.parse(localStorage.getItem('vilcanet_staff')) || { id: CURRENT_USER_ID };

    await fetch(`api/admin/ticket_manager.php?action=assign`, {
        method: 'POST',
        body: JSON.stringify({
            ticket_id: window.activeTicketId,
            staff_id: staffId,
            admin_id: staff.id
        })
    });
    document.getElementById('assignModal').classList.add('hidden');
    alert("✅ Asignado correctamente");

    if (!document.getElementById('ticketDetailModal').classList.contains('hidden')) {
        openTicketChat(window.activeTicketId);
    }
};

// --- CHAT Y LISTA ---
window.loadGlobalTickets = async function () {
    const table = document.getElementById('globalTicketsTable');
    if (!table) return;
    try {
        const res = await fetch('api/admin/get_all_tickets.php');
        const data = await res.json();
        table.innerHTML = '';
        if (data.length === 0) { table.innerHTML = '<tr><td colspan="7" class="text-center p-4">Vacío</td></tr>'; return; }

        data.forEach(t => {
            table.innerHTML += `<tr class="border-b hover:bg-gray-50">
                <td class="p-3">#${t.id}</td>
                <td class="p-3 font-bold">${t.subject}</td>
                <td class="p-3 text-blue-600 cursor-pointer" onclick="window.openClientModalGlobal(${t.client_id})">${t.client_name}</td>
                <td class="p-3">${t.department}</td>
                <td class="p-3">${t.priority}</td>
                <td class="p-3">${t.status}</td>
                <td class="p-3"><button onclick="openTicketChat(${t.id})" class="text-blue-600 font-bold">VER</button></td>
            </tr>`;
        });
    } catch (e) { }
};

// ... (Resto de funciones: openTicketChat, sendReply, changeTicketStatus, uploadEvidence, handleClientSearch igual que antes) ...
// (Asegúrate de incluir las funciones handleClientSearch, renderClientResults, etc. que ya tenías)
// Aquí las incluyo resumidas para completar el archivo:

window.handleClientSearch = function (input) {
    const list = document.getElementById('clientSearchResultsList');
    const spinner = document.getElementById('searchSpinner');
    clearTimeout(ticketSearchTimer);
    if (input.value.length < 2) { if (list) list.classList.add('hidden'); return; }
    spinner.classList.remove('hidden');
    ticketSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`api/admin/search_clients_json.php?q=${encodeURIComponent(input.value)}`);
            const clients = await res.json();
            list.innerHTML = '';
            clients.forEach(c => {
                const li = document.createElement('li');
                li.className = "p-2 hover:bg-blue-50 cursor-pointer border-b text-sm";
                li.innerHTML = `<b>${c.name}</b> <small>${c.cedula}</small>`;
                li.onmousedown = () => {
                    input.value = c.name;
                    document.getElementById('finalClientId').value = c.id;
                    list.classList.add('hidden');
                };
                list.appendChild(li);
            });
            list.classList.remove('hidden');
        } catch (e) { } finally { spinner.classList.add('hidden'); }
    }, 300);
};



function updateStatusButtonUI(status) {
    const btn = document.getElementById('statusButton');
    const txt = document.getElementById('currentStatusText');
    if (!btn) return;
    btn.className = "flex items-center gap-2 px-4 py-2 rounded-lg font-bold text-sm transition shadow-lg border";
    if (status === 'ABIERTO') { btn.classList.add('bg-green-600', 'text-white'); txt.innerHTML = 'ABIERTO'; }
    else if (status === 'EN_PROCESO') { btn.classList.add('bg-yellow-500', 'text-white'); txt.innerHTML = 'EN PROCESO'; }
    else { btn.classList.add('bg-gray-700', 'text-gray-300'); txt.innerHTML = 'CERRADO'; }
}

window.changeTicketStatus = async function (status) {
    updateStatusButtonUI(status);
    await fetch(`api/admin/ticket_manager.php?action=status`, {
        method: 'POST',
        body: JSON.stringify({ ticket_id: window.activeTicketId, status: status })
    });
    if (document.getElementById('globalTicketsTable')) loadGlobalTickets();
};

window.sendReply = async function (isInternal) {
    const msg = document.getElementById('replyInput').value;
    if (!msg) return;
    await fetch(`api/admin/ticket_manager.php?action=reply`, { method: 'POST', body: JSON.stringify({ ticket_id: window.activeTicketId, staff_id: CURRENT_USER_ID, message: msg, is_internal: isInternal }) });
    document.getElementById('replyInput').value = '';
};

window.uploadEvidence = async function (input) {
    if (input.files[0]) {
        const fd = new FormData();
        fd.append('file', input.files[0]);
        fd.append('ticket_id', window.activeTicketId);
        fd.append('staff_id', CURRENT_USER_ID);
        await fetch(`api/admin/upload_evidence.php`, { method: 'POST', body: fd });
        alert("Subido");
    }
};

// --- ABRIR CHAT ---
window.openTicketChat = function (ticketId) {
    window.activeTicketId = ticketId;

    // Mostrar modal
    const modal = document.getElementById('ticketDetailModal');
    if (modal) modal.classList.remove('hidden');

    // Resetear textos
    document.getElementById('chatTicketId').innerText = `#${ticketId}`;
    document.getElementById('chatSubject').innerText = "Cargando...";
    document.getElementById('chatMessages').innerHTML = '<div class="flex justify-center p-10"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i></div>';

    // Cargar mensajes
    loadChatMessages(ticketId);
};

// --- CARGAR MENSAJES (LÓGICA RECUPERADA) ---
async function loadChatMessages(ticketId) {
    try {
        const res = await fetch(`api/admin/get_ticket_messages.php?id=${ticketId}`);
        const data = await res.json();

        if (data.error || !data.ticket) {
            document.getElementById('chatSubject').innerText = "Error: " + (data.error || "Datos no disponibles");
            return;
        }

        const t = data.ticket;
        // Llenar cabecera del chat
        document.getElementById('chatSubject').innerText = t.subject;
        document.getElementById('chatDept').innerText = t.department || 'Soporte';
        document.getElementById('chatStaff').innerText = t.staff_name || 'Nadie';
        if (document.getElementById('currentStatusText')) {
            document.getElementById('currentStatusText').innerText = t.status;
        }
        document.getElementById('chatDescription').innerText = t.message || 'Sin descripción';

        // Llenar burbujas de chat
        const container = document.getElementById('chatMessages');
        container.innerHTML = '';

        if (!data.messages || data.messages.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-400 mt-10 text-sm">No hay historial de respuestas.</div>';
        } else {
            data.messages.forEach(msg => {
                // CORRECCIÓN: Tu base de datos usa la columna 'admin' (texto) para identificar al soporte.
                // Si msg.admin tiene texto, es Staff. Si está vacío, es Cliente.
                const isAdmin = (msg.admin && msg.admin.trim() !== '');
                
                // El nombre a mostrar es el contenido de 'admin' o 'Cliente'
                const senderName = isAdmin ? msg.admin : 'Cliente';
                
                const bubbleHtml = `
                    <div class="flex flex-col ${isAdmin ? 'items-end' : 'items-start'} mb-4">
                        <div class="${isAdmin ? 'bg-blue-100 text-blue-900 rounded-br-none' : 'bg-white border border-gray-200 rounded-bl-none'} px-4 py-3 rounded-2xl max-w-[85%] shadow-sm relative group">
                            <p class="text-[10px] font-bold opacity-60 uppercase mb-1 flex justify-between gap-4">
                                <span>${senderName}</span>
                            </p>
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">${msg.message}</p>
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1 px-1">${msg.date}</span>
                    </div>
                `;
                container.innerHTML += bubbleHtml;
            });
            // Auto-scroll al fondo
            container.scrollTop = container.scrollHeight;
        }

    } catch (e) {
        console.error("Error chat:", e);
        document.getElementById('chatSubject').innerText = "Error de conexión";
    }
}

// --- MODAL ASIGNAR TÉCNICO (FIX PARA QUE NO SE CUELGUE) ---
window.openAssignModal = function (ticketId) {
    // Si no llega ID, lo tomamos del chat abierto actual
    if (!ticketId && window.activeTicketId) ticketId = window.activeTicketId;

    // Si sigue sin haber ID, intentamos leerlo del título
    if (!ticketId) {
        const title = document.getElementById('chatTicketId');
        if (title) ticketId = title.innerText.replace('#', '');
    }

    if (!ticketId || ticketId === '000') {
        alert("Primero abre un ticket para asignarlo.");
        return;
    }

    window.activeTicketId = ticketId;

    // Abrir modal y cargar lista
    const modal = document.getElementById('assignModal');
    if (modal) {
        modal.classList.remove('hidden');
        loadTechs();
    }
};

async function loadTechs() {
    // IMPORTANTE: Busca el ID 'techListContainer' que pusimos en el footer.php corregido
    const list = document.getElementById('techListContainer');
    if (!list) return;

    list.innerHTML = '<div class="p-4 text-center"><i class="fas fa-spinner fa-spin"></i> Cargando técnicos...</div>';

    try {
        const res = await fetch('api/admin/get_staff_list.php');
        const techs = await res.json();

        list.innerHTML = '';
        if (techs.length === 0) {
            list.innerHTML = '<div class="p-4 text-center text-red-400">No hay técnicos disponibles</div>';
            return;
        }

        techs.forEach(tech => {
            list.innerHTML += `
                <div onclick="assignTicketTo(${tech.id})" class="flex items-center gap-3 p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 transition">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                        ${tech.name.substring(0, 2).toUpperCase()}
                    </div>
                    <span class="text-sm font-bold text-gray-700">${tech.name}</span>
                </div>
            `;
        });

    } catch (e) {
        list.innerHTML = '<div class="p-4 text-center text-red-500">Error de conexión</div>';
    }
}

// --- ASIGNAR (ACCIÓN FINAL) ---
window.assignTicketTo = async function (staffId) {
    if (!confirm("¿Asignar ticket #" + window.activeTicketId + " a este técnico?")) return;

    // Aquí iría la llamada a assign_ticket.php (pendiente de crear si no existe)
    alert("Función de asignar lista para conectar.");
    document.getElementById('assignModal').classList.add('hidden');
};
// Función auxiliar para obtener el diseño de la prioridad
function getPriorityBadge(priority) {
    // Normalizamos a mayúsculas y quitamos espacios
    const p = (priority || 'MEDIA').toUpperCase().trim();

    let colorClass = 'bg-gray-100 text-gray-600 border-gray-200'; // Default
    let icon = '';

    if (p === 'ALTA' || p === 'URGENTE') {
        colorClass = 'bg-red-100 text-red-700 border-red-200 font-black animate-pulse'; // Rojo intenso
        icon = '<i class="fas fa-exclamation-circle mr-1"></i>';
    } else if (p === 'MEDIA') {
        colorClass = 'bg-yellow-100 text-yellow-800 border-yellow-200'; // Amarillo
        icon = '<i class="fas fa-minus mr-1 text-[10px]"></i>';
    } else if (p === 'BAJA') {
        colorClass = 'bg-green-100 text-green-700 border-green-200'; // Verde
        icon = '<i class="fas fa-arrow-down mr-1 text-[10px]"></i>';
    }

    return `<span class="${colorClass} px-2 py-1 rounded-md text-[10px] uppercase border shadow-sm flex items-center w-fit justify-center">${icon}${p}</span>`;
}

// ESTA ES LA FUNCIÓN PRINCIPAL QUE PINTA LA TABLA
// Asegúrate de reemplazar tu función actual con esta o integrar la lógica
async function loadTickets() {
    const container = document.getElementById('ticketsTableBody'); // Tu TBODY en el HTML
    if (!container) return;

    container.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-400">Cargando...</td></tr>';

    try {
        const res = await fetch('api/admin/get_tickets.php'); // Tu API de lectura
        const data = await res.json();

        container.innerHTML = '';

        if (!data || data.length === 0) {
            container.innerHTML = '<tr><td colspan="7" class="text-center py-4">No hay tickets recientes</td></tr>';
            return;
        }

        data.forEach(t => {
            // Usamos la función de colores aquí
            const priorityBadge = getPriorityBadge(t.priority);

            // Colores para el estado
            let statusColor = 'bg-gray-100 text-gray-600';
            if (t.status === 'ABIERTO') statusColor = 'bg-green-100 text-green-800';
            if (t.status === 'EN_PROCESO') statusColor = 'bg-blue-100 text-blue-800';
            if (t.status === 'CERRADO') statusColor = 'bg-black text-white';

            const row = `
                <tr class="hover:bg-gray-50 border-b transition">
                    <td class="p-3 text-xs font-bold text-gray-500">#${t.id}</td>
                    <td class="p-3 font-bold text-gray-800">${t.subject}</td>
                    <td class="p-3 text-sm text-blue-600 font-bold">${t.client_name || 'Cliente'}</td>
                    <td class="p-3 text-xs text-gray-500">${t.department || '-'}</td>
                    
                    <td class="p-3">${priorityBadge}</td>
                    
                    <td class="p-3">
                        <span class="${statusColor} px-2 py-1 rounded text-[10px] font-bold uppercase">${t.status}</span>
                    </td>
                    <td class="p-3">
                        <button onclick="openTicketChat(${t.id})" class="text-blue-600 hover:text-blue-800 font-bold text-xs underline">
                            VER CASO
                        </button>
                    </td>
                </tr>
            `;
            container.innerHTML += row;
        });

    } catch (e) {
        console.error(e);
        container.innerHTML = '<tr><td colspan="7" class="text-center text-red-500">Error al cargar</td></tr>';
    }
}