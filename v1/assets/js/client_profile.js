// ARCHIVO: assets/js/client_profile.js

const urlParams = new URLSearchParams(window.location.search);
const CLIENT_ID = urlParams.get('id');

document.addEventListener('DOMContentLoaded', () => {
    if (!localStorage.getItem('vilcanet_staff')) {
        window.location.href = 'index.html';
        return;
    }

    if (!CLIENT_ID) {
        alert("Cliente no especificado");
        window.location.href = 'dashboard.html';
        return;
    }

    loadClientData();

    // Listener Formulario
    document.getElementById('createTicketForm').addEventListener('submit', handleCreateTicket);
});

async function loadClientData() {
    try {
        if (typeof CONFIG === 'undefined') throw new Error("Error config.js");

        const response = await fetch(`${API_URL}/admin/get_client_detail.php?id=${CLIENT_ID}`);
        const data = await response.json();

        if (data.error) throw new Error(data.error);

        const c = data.client;

        // Llenar UI
        document.getElementById('clientName').textContent = c.name;
        document.getElementById('clientCedula').textContent = c.cedula;
        document.getElementById('clientEmail').textContent = c.email || 'Sin correo';
        document.getElementById('clientPhone').textContent = c.phone || 'Sin celular';
        document.getElementById('clientAddress').textContent = c.address || 'Sin dirección';
        document.getElementById('serviceType').textContent = c.service_type || 'Internet';
        document.getElementById('clientPoints').textContent = c.points;
        document.getElementById('avatarInitial').textContent = c.name.charAt(0);

        // Link WhatsApp
        if (c.phone) {
            // Limpiar número (quitar 0 al inicio si tiene 10 dígitos para formato internacional 593)
            let phone = c.phone.replace(/\D/g, '');
            if (phone.startsWith('0') && phone.length === 10) phone = '593' + phone.substring(1);
            document.getElementById('wspLink').href = `https://wa.me/${phone}`;
        }

        // Estado y Badge
        const badge = document.getElementById('statusBadge');
        if (c.service_status === 'CORTADO') {
            badge.textContent = "CORTADO";
            badge.className = "bg-purple-600 px-3 py-1 rounded-full text-xs font-bold text-white uppercase";
        } else if (parseInt(c.current_debt_months) > 0) {
            badge.textContent = `EN MORA (${c.current_debt_months} MESES)`;
            badge.className = "bg-orange-500 px-3 py-1 rounded-full text-xs font-bold text-white uppercase";
        } else {
            badge.textContent = "ACTIVO / AL DÍA";
            badge.className = "bg-green-500 px-3 py-1 rounded-full text-xs font-bold text-white uppercase";
        }

        // Estado Deuda Texto
        const debtLabel = document.getElementById('debtStatus');
        if (parseInt(c.current_debt_months) > 0) {
            debtLabel.textContent = "Pendiente";
            debtLabel.classList.add('text-red-500');
        } else {
            debtLabel.textContent = "Al día";
            debtLabel.classList.add('text-green-500');
        }

        renderTickets(data.tickets);

    } catch (error) {
        console.error(error);
        alert("Error: " + error.message);
    }
}

function renderTickets(tickets) {
    const list = document.getElementById('ticketsList');
    document.getElementById('ticketCount').textContent = tickets.length;
    list.innerHTML = '';

    if (tickets.length === 0) {
        list.innerHTML = `<div class="p-8 text-center flex flex-col items-center">
            <div class="bg-gray-100 p-4 rounded-full mb-3"><i class="fas fa-clipboard-check text-gray-400 text-2xl"></i></div>
            <p class="text-gray-400 text-sm">Todo en orden, sin tickets recientes.</p>
        </div>`;
        return;
    }

    tickets.forEach(t => {
        let badgeColor = 'bg-gray-100 text-gray-600';
        if (t.status === 'ABIERTO') badgeColor = 'bg-green-100 text-green-700 border border-green-200';
        if (t.status === 'EN_PROCESO') badgeColor = 'bg-yellow-50 text-yellow-700 border border-yellow-200';
        if (t.status === 'PENDIENTE_CLIENTE') badgeColor = 'bg-blue-50 text-blue-700 border border-blue-200';
        if (t.status === 'CERRADO') badgeColor = 'bg-gray-100 text-gray-500';

        const date = new Date(t.created_at).toLocaleDateString("es-ES", { day: 'numeric', month: 'short' });
        const priorityIcon = t.priority === 'URGENTE' ? '<i class="fas fa-exclamation-circle text-red-500" title="Urgente"></i>' : '';

        list.innerHTML += `
            <div class="p-4 hover:bg-gray-50 transition cursor-pointer flex justify-between group">
                <div class="flex gap-3">
                    <div class="mt-1"><i class="fas fa-ticket-alt text-gray-300 group-hover:text-blue-400"></i></div>
                    <div>
                        <p class="text-sm font-bold text-gray-800">#${t.id} ${t.subject} ${priorityIcon}</p>
                        <p class="text-xs text-gray-500 mt-0.5">${t.department} • <span class="text-gray-400">Asignado a: ${t.staff_name || 'Nadie'}</span></p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide ${badgeColor}">${t.status.replace('_', ' ')}</span>
                    <p class="text-[10px] text-gray-400 mt-1">${date}</p>
                </div>
            </div>
        `;
    });
}

function openTicketModal() { document.getElementById('ticketModal').classList.remove('hidden'); }
function closeTicketModal() { document.getElementById('ticketModal').classList.add('hidden'); }

async function handleCreateTicket(e) {
    e.preventDefault();
    alert("Próximo paso: Conectar este formulario a la API save_ticket.php");
    // Aquí implementaremos el fetch POST para guardar
}