// ARCHIVO: assets/js/modules/clients.js

// INICIAR BUSCADOR AL CARGAR
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('clientSearchInput');
    // Si existe el input, configuramos el evento y cargamos iniciales
    if (search) {
        search.addEventListener('input', debounce((e) => handleSearch(e.target.value), 500));
        // Cargar lista inicial vacía (trae los recientes/urgentes por defecto del PHP)
        handleSearch('');
    }
});

async function handleSearch(query) {
    const container = document.getElementById('clientsGrid');
    if (!container) return;

    // LIMPIEZA: Quitamos espacios extra
    const cleanQuery = query.trim();

    // EL TRUCO PARA EL FIREWALL:
    // Si está vacío, enviamos '%' para que el servidor no bloquee por "petición vacía".
    // Si tiene texto, enviamos el texto.
    const searchTerm = cleanQuery === '' ? '%' : cleanQuery;

    // UX: Solo mostrar "Buscando" si realmente hay una búsqueda activa
    if (cleanQuery.length > 0) {
        container.innerHTML = '<p class="col-span-full text-center py-8 text-gray-400"><i class="fas fa-circle-notch fa-spin"></i> Buscando...</p>';
    }

    try {
        // Usamos searchTerm (que tiene '%' o el nombre), nunca vacío.
        const res = await fetch(`${API_URL}/admin/search_clients.php?q=${encodeURIComponent(searchTerm)}`);

        // Verificamos si la respuesta es OK antes de intentar leer JSON
        if (!res.ok) {
            throw new Error(`Error servidor: ${res.status}`);
        }

        const clients = await res.json();

        // Si el PHP devolvió un error lógico (ej: base de datos)
        if (clients.error) {
            console.error("Error backend:", clients.error);
            return;
        }

        renderClients(clients);

    } catch (error) {
        console.error("Error en búsqueda:", error);
        // Mensaje de error discreto
        if (cleanQuery.length > 0) {
            container.innerHTML = '<p class="col-span-full text-center text-red-400 text-xs">Error de conexión al buscar</p>';
        }
    }
}

function renderClients(clients) {
    const container = document.getElementById('clientsGrid');
    if (!container) return;
    container.innerHTML = '';

    if (!clients || clients.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-8 text-gray-400 border-2 border-dashed rounded-lg">No se encontraron clientes.</div>';
        return;
    }

    clients.forEach(c => {
        // --- RECUPERANDO TU LÓGICA DE COLORES ORIGINAL ---

        // 1. Estilo por defecto (Verde/Activo)
        let colorClass = 'bg-green-50 border-green-200 text-green-700';
        let dotClass = 'bg-green-500';

        // 2. Mapeo exacto de los colores que envía tu PHP
        if (c.color === 'yellow') {
            colorClass = 'bg-yellow-50 border-yellow-200 text-yellow-700';
            dotClass = 'bg-yellow-500';
        }
        if (c.color === 'blue') {
            colorClass = 'bg-blue-50 border-blue-200 text-blue-700';
            dotClass = 'bg-blue-500';
        }
        if (c.color === 'red') {
            // Rojo para urgencias (Mora alta o Tickets)
            colorClass = 'bg-red-50 border-red-200 text-red-700';
            dotClass = 'bg-red-600 animate-ping'; // ¡El punto parpadea!
        }
        if (c.color === 'purple') {
            // Morado/Gris para Cortados
            colorClass = 'bg-purple-50 border-purple-200 text-purple-700 grayscale opacity-75';
            dotClass = 'bg-purple-600';
        }
        if (c.color === 'orange') {
            colorClass = 'bg-orange-50 border-orange-200 text-orange-700';
            dotClass = 'bg-orange-500';
        }

        // 3. Renderizado de la Tarjeta (Diseño Original Restaurado)
        container.innerHTML += `
            <div onclick="openClientModalGlobal(${c.id})" 
                 class="${colorClass} border p-4 rounded-xl text-center shadow-sm hover:shadow-md hover:-translate-y-1 transition-all cursor-pointer relative group">
                
                <div class="absolute top-3 right-3 h-3 w-3 ${dotClass} rounded-full border border-white shadow-sm"></div>
                
                <div class="font-bold text-xl mb-3 bg-white w-12 h-12 mx-auto rounded-full flex items-center justify-center shadow-sm border border-current opacity-90">
                    ${c.initials}
                </div>
                
                <p class="font-bold text-sm truncate w-full leading-tight mb-1">${c.name}</p>
                <p class="text-[10px] opacity-75 font-bold uppercase tracking-wider border-t border-current/20 pt-1 mt-2">
                    ${c.status_text}
                </p>
            </div>
        `;
    });
}
// EN: assets/js/modules/tickets.js


// --- MODAL GLOBAL DE CLIENTE (Funcionalidad Completa) ---
// EN: assets/js/modules/clients.js

window.openClientModalGlobal = async function (clientId) {
    const modal = document.getElementById('globalClientModal');
    if (!modal) return;

    modal.classList.remove('hidden');

    // 1. Limpieza inicial (Loading state)
    safeSetText('modalClientName', 'Cargando...');
    safeSetText('modalPlan', 'Verificando...'); // Feedback visual
    safeSetText('modalIp', '...');

    if (document.getElementById('currentGlobalClientId')) {
        document.getElementById('currentGlobalClientId').value = clientId;
    }

    try {
        // --- LLAMADA 1: DATOS BÁSICOS Y TICKETS ---
        const resBasic = await fetch(`api/admin/get_client_detail.php?id=${clientId}`);
        const dataBasic = await resBasic.json();

        if (dataBasic.client) {
            const c = dataBasic.client;
            // Llenar básicos
            safeSetText('modalClientName', c.name);
            safeSetText('modalClientCedula', c.cedula);
            safeSetText('modalPhone', c.phone);
            safeSetText('modalEmail', c.email);
            safeSetText('modalAddress', c.address);

            // Notas
            const notesArea = document.getElementById('modalNotes');
            if (notesArea) notesArea.value = c.notes || '';

            // Avatar y Estado
            const avatar = document.getElementById('modalAvatar');
            if (avatar) {
                avatar.innerText = c.initials || '--';
                avatar.className = `w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold text-white border-2 border-white shadow-lg ${c.service_status === 'ACTIVO' ? 'bg-blue-600' : 'bg-gray-500'}`;
            }

            // Tickets
            renderModalTickets(dataBasic.tickets);
        }

        // --- LLAMADA 2: DATOS TÉCNICOS (PLAN, IP, SECTOR) ---
        // Esto se ejecuta aparte, manteniendo el código limpio
        fetch(`api/admin/get_client_services.php?id=${clientId}`)
            .then(r => r.json())
            .then(tech => {
                // Aquí rellenamos lo que pediste específicamente
                safeSetText('modalPlan', tech.plan_name || 'Sin Plan');
                safeSetText('modalIp', tech.ip_address || '--');
                safeSetText('modalSector', tech.sector_name || '--');
            })
            .catch(err => {
                console.error("Error cargando plan:", err);
                safeSetText('modalPlan', 'Error carga');
            });

    } catch (e) {
        console.error(e);
        safeSetText('modalClientName', 'Error de conexión');
    }
};

// Función auxiliar para renderizar tickets (para no amontonar código arriba)
function renderModalTickets(tickets) {
    const list = document.getElementById('modalTicketsList');
    if (!list) return;
    list.innerHTML = '';

    if (!tickets || tickets.length === 0) {
        list.innerHTML = '<div class="p-4 text-center text-gray-400 text-sm">Sin historial</div>';
        return;
    }

    tickets.forEach(t => {
        let stColor = t.status === 'ABIERTO' ? 'text-green-600 font-bold' : 'text-gray-500';
        list.innerHTML += `
            <div class="p-3 border-b hover:bg-gray-50 flex justify-between items-center group cursor-pointer" onclick="window.openTicketChat(${t.id})">
                <div>
                    <div class="text-sm text-gray-700"><span class="font-mono text-xs text-gray-400">#${t.id}</span> <b>${t.subject}</b></div>
                    <div class="text-[10px] text-gray-400">${t.created_at} • ${t.department}</div>
                </div>
                <span class="text-xs ${stColor}">${t.status}</span>
            </div>`;
    });
}

// --- GESTIÓN CLIENTES (CREAR/GUARDAR) ---
function openNewClientModal() {
    document.getElementById('clientFormModal').classList.remove('hidden');
    document.getElementById('saveClientForm').reset();
    document.getElementById('cliId').value = '';
}

async function handleSaveClient(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    const data = {
        id: document.getElementById('cliId').value,
        name: document.getElementById('cliName').value,
        cedula: document.getElementById('cliCedula').value,
        phone: document.getElementById('cliPhone').value,
        email: document.getElementById('cliEmail').value,
        address: document.getElementById('cliAddress').value,
        plan_details: document.getElementById('cliPlan').value,
        coordinates: document.getElementById('cliCoords').value,
        has_internet: document.getElementById('checkInternet').checked,
        has_tv: document.getElementById('checkTV').checked
    };

    try {
        await fetch(`${API_URL}/admin/save_client.php`, { method: 'POST', body: JSON.stringify(data) });
        showToast("Cliente guardado", "success");
        document.getElementById('clientFormModal').classList.add('hidden');
        const search = document.getElementById('clientSearchInput');
        if (search) search.dispatchEvent(new Event('input'));
    } catch (err) { showToast("Error: " + err.message, "error"); }
    finally { btn.disabled = false; btn.innerHTML = originalText; }
}

function cleanCoords() {
    const input = document.getElementById('cliCoords');
    const match = input.value.match(/(-?\d+\.\d+),\s*(-?\d+\.\d+)/);
    if (match) { input.value = `${match[1]}, ${match[2]}`; showToast("GPS OK", "success"); }
    else showToast("No se detectaron coordenadas", "warning");
}

// CONVENIOS
function openAgreementModal() {
    document.getElementById('agreementModal').classList.remove('hidden');
    const tomorrow = new Date(); tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('agDate').valueAsDate = tomorrow;
}

async function createAgreement() {
    const date = document.getElementById('agDate').value;
    const notes = document.getElementById('agNotes').value;
    const staff = JSON.parse(localStorage.getItem('vilcanet_staff'));
    if (!date) return showToast("Falta fecha", "warning");
    try {
        await fetch(`${API_URL}/admin/create_agreement.php`, { method: 'POST', body: JSON.stringify({ client_id: CURRENT_CLIENT_ID, staff_id: staff.id, deadline: date, notes: notes }) });
        showToast("Convenio creado", "success");
        document.getElementById('agreementModal').classList.add('hidden');
        openClientModalGlobal(CURRENT_CLIENT_ID);
    } catch (e) { showToast("Error", "error"); }
}
// ARCHIVO: assets/js/modules/clients.js

// Función auxiliar segura
function safeSetText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text || '--';
}

window.openClientModalGlobal = async function (clientId) {
    const modal = document.getElementById('globalClientModal');
    if (!modal) return console.error("Falta modal globalClientModal");

    modal.classList.remove('hidden');
    safeSetText('modalClientName', 'Cargando...');

    // Guardar ID
    if (document.getElementById('currentGlobalClientId')) {
        document.getElementById('currentGlobalClientId').value = clientId;
    }

    try {
        const res = await fetch(`api/admin/get_client_detail.php?id=${clientId}`);
        const data = await res.json();

        // Evitar errores si viene vacío
        if (data.error || !data.client) {
            safeSetText('modalClientName', 'Error: No encontrado');
            return;
        }

        const c = data.client;

        // LLENAR DATOS
        safeSetText('modalClientName', c.name);
        safeSetText('modalClientCedula', c.cedula);
        safeSetText('modalPhone', c.phone);
        safeSetText('modalEmail', c.email);
        safeSetText('modalAddress', c.address);

        // --- AQUÍ MOSTRAMOS EL PLAN GRANDE ---
        safeSetText('modalPlan', c.plan_name || c.plan || 'Sin Plan Asignado');
        safeSetText('modalIp', c.ip_address || c.ip);
        safeSetText('modalSector', c.sector_name || c.sector);

        // --- AQUÍ CARGAMOS LA NOTA ---
        const notesArea = document.getElementById('modalNotes');
        if (notesArea) notesArea.value = c.notes || ''; // Cargamos la nota de la BD

        // ESTADO
        const badge = document.getElementById('modalStatusBadge');
        if (badge) {
            badge.innerText = c.service_status || 'DESCONOCIDO';
            badge.className = "px-2 py-0.5 rounded text-[10px] font-bold text-white uppercase " +
                (c.service_status === 'ACTIVO' ? 'bg-green-600' : 'bg-red-600');
        }

        // AVATAR
        const avatar = document.getElementById('modalAvatar');
        if (avatar) {
            avatar.innerText = c.initials || c.name.substring(0, 2).toUpperCase();
            avatar.className = `w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold text-white border-2 border-white shadow-lg ${c.service_status === 'ACTIVO' ? 'bg-blue-600' : 'bg-gray-500'}`;
        }

        // HISTORIAL TICKETS
        const list = document.getElementById('modalTicketsList');
        if (list && data.tickets) {
            list.innerHTML = '';
            if (data.tickets.length === 0) {
                list.innerHTML = '<div class="p-4 text-center text-gray-400 text-sm">Sin historial</div>';
            } else {
                data.tickets.forEach(t => {
                    let stColor = t.status === 'ABIERTO' ? 'text-green-600 font-bold' : 'text-gray-500';
                    list.innerHTML += `
                        <div class="p-3 border-b hover:bg-gray-50 flex justify-between items-center group cursor-pointer" onclick="window.openTicketChat(${t.id})">
                            <div>
                                <div class="text-sm text-gray-700"><span class="font-mono text-xs text-gray-400">#${t.id}</span> <b>${t.subject}</b></div>
                                <div class="text-[10px] text-gray-400">${t.created_at} • ${t.department}</div>
                            </div>
                            <span class="text-xs ${stColor}">${t.status}</span>
                        </div>`;
                });
            }
        }

    } catch (e) {
        console.error(e);
        safeSetText('modalClientName', 'Error de conexión');
    }
};

// --- NUEVA FUNCIÓN: GUARDAR NOTA ---
window.saveClientNote = async function () {
    const clientId = document.getElementById('currentGlobalClientId').value;
    const note = document.getElementById('modalNotes').value;
    const btn = document.querySelector('button[onclick="saveClientNote()"]');

    if (!clientId) return alert("Error: No hay cliente seleccionado");

    // Efecto visual de guardando
    const originalText = btn.innerText;
    btn.innerText = "...";
    btn.disabled = true;

    try {
        const res = await fetch(`api/admin/save_client_note.php`, {
            method: 'POST',
            body: JSON.stringify({ client_id: clientId, note: note })
        });
        const data = await res.json();

        if (data.success) {
            btn.innerText = "OK";
            setTimeout(() => { btn.innerText = "GUARDAR"; btn.disabled = false; }, 1500);
        } else {
            alert("Error al guardar: " + data.error);
            btn.innerText = "REINTENTAR";
            btn.disabled = false;
        }
    } catch (e) {
        console.error(e);
        alert("Error de red");
        btn.innerText = "ERROR";
        btn.disabled = false;
    }
};
// --- FUNCIÓN BLINDADA PARA ABRIR MODAL DE ASIGNACIÓN ---
window.openAssignModal = function (ticketId) {
    // 1. Intentamos usar el ID que pasan por parámetro
    if (ticketId) window.activeTicketId = ticketId;

    // 2. Si no hay parámetro, intentamos leerlo del título del chat abierto
    if (!window.activeTicketId) {
        const chatTitle = document.getElementById('chatTicketId');
        // Si el título dice algo diferente al default "#000", asumimos que es el ID real
        if (chatTitle && chatTitle.innerText !== '#000' && chatTitle.innerText.includes('#')) {
            window.activeTicketId = chatTitle.innerText.replace('#', '');
        }
    }

    // 3. Si aún así no tenemos ID, es un error.
    if (!window.activeTicketId || window.activeTicketId === '000') {
        alert("⚠️ Error: No se ha detectado el ID del ticket. Cierra y vuelve a abrir el ticket.");
        return;
    }

    // 4. Abrir el modal
    const modal = document.getElementById('assignModal');
    if (modal) {
        modal.classList.remove('hidden');
        loadTechs(); // Cargar lista de técnicos
    } else {
        console.error("No se encuentra el modal 'assignModal' en el HTML");
    }
};