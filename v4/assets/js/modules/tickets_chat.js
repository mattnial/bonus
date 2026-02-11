// ARCHIVO: assets/js/modules/tickets_chat.js
console.log("✅ tickets_chat.js cargado (Versión Toasts & Dark Mode).");

window.CURRENT_TICKET_ID_CHAT = null;
window.CURRENT_TICKET_CATEGORY = '';

// ==============================================================
// 1. CHAT
// ==============================================================
window.openTicketChat = function (ticketId) {
    if (!ticketId) return;
    window.CURRENT_TICKET_ID_CHAT = ticketId;

    const modal = document.getElementById('ticketDetailModal');
    if (modal) modal.classList.remove('hidden');

    safeSetText('chatSubject', "Cargando...");
    loadChatMessages(ticketId);
};

async function loadChatMessages(ticketId) {
    try {
        const res = await fetch(`api/admin/get_ticket_messages.php?id=${ticketId}`);
        const data = await res.json();
        if (!data.ticket) return;

        const t = data.ticket;
        window.CURRENT_TICKET_CATEGORY = t.department || '';

        // TEXTOS Y DATOS BÁSICOS
        safeSetText('chatTicketId', `#${t.id}`);
        safeSetText('chatSubject', t.subject);
        safeSetText('chatDept', t.department || 'General');

        // Staff
        const staffEl = document.getElementById('chatStaff');
        if (staffEl) {
            staffEl.innerText = t.staff_name || 'Sin asignar';
            staffEl.className = !t.staff_name ? "text-red-400 font-bold italic" : "text-blue-600 font-bold";
        }

        // Estado y Selector
        safeSetText('currentStatusText', t.status || '...');
        const select = document.getElementById('ticketStatusSelect');
        if (select) {
            select.value = t.status;
            if (typeof getStatusColorClass === 'function') select.className = getStatusColorClass(t.status);
        }

        safeSetText('chatDescription', t.description || t.message || '...');

        // =========================================================
        // LOGICA DE VISIBILIDAD DEL BOTÓN "GENERAR ORDEN"
        // =========================================================
        const btnOrder = document.getElementById('btnGenerateOrder');
        if (btnOrder) {
            // Lista de tipos permitidos (Códigos Técnicos)
            const tiposTecnicos = [
                'INSTALACION_FO', 'INSTALACION_RE',
                'INSPECCION',
                'SOPORTE_FO', 'SOPORTE_RE',
                'MIGRACION',
                'RETIRO',
                'CAMBIO_DOM_FO', 'CAMBIO_DOM_RE',
                'EVENTO'
            ];

            // Convertimos el asunto del ticket a mayúsculas para comparar
            const asuntoTicket = (t.subject || '').toUpperCase().trim();

            // Verificamos: ¿El asunto está en la lista O el departamento es explícitamente TECNICA?
            // (A veces el asunto es texto libre, así que doble chequeo)
            if (tiposTecnicos.includes(asuntoTicket)) {
                btnOrder.classList.remove('hidden'); // MOSTRAR
                btnOrder.classList.add('flex');      // Asegurar display flex
            } else {
                btnOrder.classList.add('hidden');    // OCULTAR
                btnOrder.classList.remove('flex');
            }
        }
        // =========================================================

        // MENSAJES (Resto del código igual...)
        const container = document.getElementById('chatMessages');
        container.innerHTML = '';
        if (data.messages) {
            data.messages.forEach(msg => {
                const isStaff = (msg.sender_type === 'STAFF');
                const senderName = isStaff ? (msg.staff_name || 'Agente') : 'Cliente';

                let content = msg.message;
                if (msg.message && msg.message.startsWith('FILE:')) {
                    const url = msg.message.substring(5);
                    if (url.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                        content = `<a href="${url}" target="_blank"><img src="${url}" class="max-w-xs max-h-48 rounded border mt-2 bg-gray-50 object-contain hover:opacity-90 transition"></a>`;
                    } else {
                        content = `<a href="${url}" target="_blank" class="flex items-center gap-2 text-blue-600 bg-blue-50 p-2 rounded mt-1 hover:bg-blue-100 transition"><i class="fas fa-paperclip"></i> Ver Archivo</a>`;
                    }
                }

                container.innerHTML += `
                    <div class="flex flex-col ${isStaff ? 'items-end' : 'items-start'} mb-4 animate-fade-in-up">
                        <div class="${isStaff ? 'bg-blue-100 text-blue-900 rounded-br-none' : 'bg-white border border-gray-200 rounded-bl-none'} px-4 py-3 rounded-2xl max-w-[85%] shadow-sm relative group">
                            <p class="text-[10px] font-bold opacity-50 uppercase mb-1 flex justify-between gap-4">
                                <span>${senderName}</span>
                            </p>
                            <div class="text-sm whitespace-pre-wrap">${content}</div>
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1 px-1">${msg.created_at || ''}</span>
                    </div>`;
            });
            container.scrollTop = container.scrollHeight;
        }
    } catch (e) { console.error("Error chat:", e); }
}

function safeSetText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

// Envío de Respuesta (con Toast)
window.sendReply = async function (isInternal) {
    const input = document.getElementById('replyInput');
    if (!input.value.trim()) return;

    // Bloquear input
    input.disabled = true;

    try {
        await fetch('api/admin/send_reply.php', {
            method: 'POST',
            body: JSON.stringify({ ticket_id: window.CURRENT_TICKET_ID_CHAT, message: input.value, is_internal: isInternal })
        });
        input.value = '';
        loadChatMessages(window.CURRENT_TICKET_ID_CHAT);
    } catch (e) {
        if (typeof showToast === 'function') showToast("Error al enviar mensaje", "error");
        else alert("Error envío");
    } finally {
        input.disabled = false;
        input.focus();
    }
};

// Subida de Evidencia (con Toast)
window.uploadEvidence = async function (input) {
    if (input.files.length === 0) return;

    const formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('ticket_id', window.CURRENT_TICKET_ID_CHAT);

    // Notificación de inicio
    if (typeof showToast === 'function') showToast("Subiendo archivo...", "info");

    try {
        await fetch('api/admin/upload_evidence.php', { method: 'POST', body: formData });

        if (typeof showToast === 'function') showToast("✅ Evidencia subida", "success");

        loadChatMessages(window.CURRENT_TICKET_ID_CHAT);
    } catch (e) {
        if (typeof showToast === 'function') showToast("Error al subir archivo", "error");
    } finally {
        input.value = ''; // Reset
    }
};


// ==============================================================
// 2. ASIGNAR TÉCNICO
// ==============================================================

window.openAssignModal = function (ticketId) {
    if (!ticketId && window.CURRENT_TICKET_ID_CHAT) ticketId = window.CURRENT_TICKET_ID_CHAT;

    if (!ticketId) {
        if (typeof showToast === 'function') showToast("Primero abre un ticket", "warning");
        else alert("Primero abre un ticket.");
        return;
    }

    window.CURRENT_TICKET_ID_CHAT = ticketId;
    const modal = document.getElementById('assignModal');
    if (modal) {
        modal.classList.remove('hidden');
        loadTechsForAssign(window.CURRENT_TICKET_CATEGORY);
    }
};

async function loadTechsForAssign(category) {
    const list = document.getElementById('techListContainer');
    if (!list) return;

    list.innerHTML = '<p class="text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';

    try {
        const res = await fetch(`api/admin/get_staff_list.php?category=${encodeURIComponent(category)}`);
        const staff = await res.json();

        list.innerHTML = '';

        if (!staff || staff.length === 0) {
            list.innerHTML = '<div class="p-4 text-center"><p class="text-red-500 font-bold mb-1">Sin personal</p></div>';
            return;
        }

        if (category) {
            list.innerHTML += `<div class="bg-blue-50 p-2 text-[10px] text-blue-800 font-bold uppercase text-center border-b border-blue-100 sticky top-0 z-10">Filtro: ${category}</div>`;
        }

        staff.forEach(s => {
            const roleName = s.role || 'ADMIN';
            list.innerHTML += `
                <button type="button" 
                        onclick="window.tryAssignTicketDirect(${s.id}, this)" 
                        class="w-full text-left p-3 hover:bg-blue-50 border-b border-gray-100 flex items-center gap-3 transition group">
                    
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-110 transition">
                        ${s.name.substring(0, 2).toUpperCase()}
                    </div>
                    
                    <div class="flex flex-col flex-1">
                        <span class="font-bold text-gray-700 text-sm">${s.name}</span>
                        <span class="text-[10px] text-gray-400 uppercase font-mono tracking-wide">${roleName}</span>
                    </div>
                    
                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-600"></i>
                </button>
            `;
        });

    } catch (e) {
        console.error(e);
        list.innerHTML = '<p class="text-center text-red-500 py-4">Error de conexión</p>';
    }
}

// FUNCIÓN DIRECTA DE ASIGNACIÓN (CON TOAST)
window.tryAssignTicketDirect = async function (staffId, btnElement) {
    if (!window.CURRENT_TICKET_ID_CHAT) return;

    // Feedback visual en el botón
    const originalContent = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = `<div class="flex items-center gap-3"><i class="fas fa-spinner fa-spin text-blue-600"></i><span class="font-bold text-blue-600">Asignando...</span></div>`;

    try {
        const payload = { ticket_id: window.CURRENT_TICKET_ID_CHAT, staff_id: staffId };

        const res = await fetch('api/admin/assign_ticket.php', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            // ÉXITO
            document.getElementById('assignModal').classList.add('hidden');

            // ALERT BONITA (TOAST)
            if (typeof showToast === 'function') {
                showToast("✅ Técnico asignado correctamente", "success");
            } else {
                alert("Asignado correctamente");
            }

            loadChatMessages(window.CURRENT_TICKET_ID_CHAT);
        } else {
            // ERROR
            if (typeof showToast === 'function') showToast("Error: " + (data.error || 'Desconocido'), "error");
            btnElement.disabled = false;
            btnElement.innerHTML = originalContent;
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast("Error de conexión", "error");
        btnElement.disabled = false;
        btnElement.innerHTML = originalContent;
    }
};

// ==============================================================
// 3. CAMBIAR ESTADO (CORREGIDO PARA DARK HEADER + TOAST)
// ==============================================================

// Función para obtener clases que combinen con el Header Oscuro (Transparencia + Texto Color)
function getStatusColorClass(status) {
    // Base "Glass" para el header oscuro
    const base = "appearance-none w-44 bg-white/10 hover:bg-white/20 text-xs font-bold border rounded-lg pl-4 pr-10 py-2 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all backdrop-blur-sm tracking-wide shadow-sm ";

    switch (status) {
        case 'ABIERTO': return base + "text-green-400 border-green-500/50";
        case 'EN_PROCESO': return base + "text-yellow-400 border-yellow-500/50";
        case 'PENDIENTE': return base + "text-blue-300 border-blue-500/50";
        case 'RESUELTO': return base + "text-purple-300 border-purple-500/50";
        case 'CERRADO': return base + "text-gray-400 border-gray-600";
        default: return base + "text-white border-white/10";
    }
}

window.changeTicketStatus = async function () {
    const selectElement = document.getElementById('ticketStatusSelect');
    if (!selectElement) return;

    const newStatus = selectElement.value;
    const ticketId = window.CURRENT_TICKET_ID_CHAT;

    if (!ticketId) return;

    // Feedback visual: Opacidad mientras carga
    const prevClass = selectElement.className;
    selectElement.style.opacity = "0.5";
    selectElement.disabled = true;

    try {
        const res = await fetch('api/admin/ticket_manager.php?action=status', {
            method: 'POST',
            body: JSON.stringify({
                ticket_id: ticketId,
                status: newStatus
            })
        });

        const data = await res.json();

        if (data.success) {
            // ÉXITO: TOAST + ACTUALIZAR UI
            if (typeof showToast === 'function') {
                showToast(`✅ Estado actualizado a: ${newStatus}`, "success");
            }

            // Actualizar color del botón
            selectElement.className = getStatusColorClass(newStatus);

            safeSetText('currentStatusText', newStatus);
            loadChatMessages(ticketId);
        } else {
            if (typeof showToast === 'function') showToast("No se pudo cambiar el estado", "error");
            selectElement.className = prevClass; // Revertir
        }

    } catch (e) {
        if (typeof showToast === 'function') showToast("Error de conexión", "error");
    } finally {
        selectElement.disabled = false;
        selectElement.style.opacity = "1";
    }
};
// ==============================================================
// 4. GENERAR ORDEN EN EL CHAT
// ==============================================================
window.generateOrderChat = async function () {
    if (!window.CURRENT_TICKET_ID_CHAT) return;

    // Confirmación suave
    if (!confirm("¿Deseas generar la Orden de Trabajo en el chat? \n(Se usará el 'Asunto' del ticket para elegir la plantilla)")) {
        return;
    }

    if (typeof showToast === 'function') showToast("Generando documento...", "info");

    try {
        const res = await fetch('api/admin/trigger_order_message.php', {
            method: 'POST',
            body: JSON.stringify({ ticket_id: window.CURRENT_TICKET_ID_CHAT })
        });

        const data = await res.json();

        if (data.success) {
            if (typeof showToast === 'function') showToast("✅ Orden creada en el chat", "success");

            // Recargamos el chat para ver el botón nuevo
            loadChatMessages(window.CURRENT_TICKET_ID_CHAT);
        } else {
            alert("Error: " + data.error);
        }
    } catch (e) {
        console.error(e);
        alert("Error de conexión al generar orden");
    }
};
// Función para Guardar Ticket (En tickets.js o dashboard.js)
window.saveTicket = async function () {
    // 1. Obtener valores
    const clientId = document.getElementById('tClientSearch').dataset.id; // Asumiendo que guardas el ID aquí
    const subject = document.getElementById('tSubject').value;
    const priority = document.getElementById('tPriority').value; // NUEVO CAMPO
    const description = document.getElementById('tDescription').value;

    // 2. Validaciones
    if (!clientId) return showToast("⚠️ Debes seleccionar un cliente", "warning");
    if (!subject) return showToast("⚠️ Selecciona el motivo del ticket", "warning");
    if (!priority) return showToast("⚠️ Selecciona la prioridad", "warning");

    // Botón cargando...
    const btn = document.querySelector("button[onclick='saveTicket()']");
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        // 3. Determinar Departamento automáticamente según el asunto
        let department = 'SOPORTE'; // Por defecto
        const technicalSubjects = ['INSTALACION', 'SOPORTE', 'MIGRACION', 'RETIRO', 'CAMBIO_DOM', 'INSPECCION', 'EVENTO'];

        // Si el asunto contiene alguna palabra técnica, asignamos departamento TECNICA
        if (technicalSubjects.some(t => subject.includes(t))) {
            department = 'TECNICA';
        } else if (subject === 'FACTURACION') {
            department = 'FACTURACION';
        } else if (subject === 'CAMBIO_CLAVE') {
            department = 'SOPORTE';
        }

        // 4. Enviar datos
        const res = await fetch('api/admin/save_ticket.php', {
            method: 'POST',
            body: JSON.stringify({
                client_id: clientId,
                created_by: 1, // O el ID del admin logueado
                department: department,
                priority: priority, // ENVIAMOS LA PRIORIDAD
                service_affected: 'Internet', // Puedes hacerlo dinámico si quieres
                subject: subject,
                description: description
            })
        });

        const data = await res.json();

        if (data.success) {
            // 5. ÉXITO: USAMOS LA ALERTA BONITA (TOAST)
            showToast("✅ Ticket creado correctamente", "success");

            // Cerrar modal y limpiar
            document.getElementById('createTicketModal').classList.add('hidden');
            document.getElementById('tDescription').value = '';
            // Recargar tabla de tickets
            if (typeof loadTickets === 'function') loadTickets();
        } else {
            showToast("❌ Error: " + data.error, "error");
        }

    } catch (e) {
        console.error(e);
        showToast("Error de conexión", "error");
    } finally {
        // Restaurar botón
        btn.disabled = false;
        btn.innerText = originalText;
    }
};