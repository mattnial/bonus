/**
 * ARCHIVO: modules/Sales/assets/contracts.js
 * Lógica para Generador de Documentos
 */

let searchTimeout;

// Función de búsqueda de cliente
function buscarClienteContrato(val) {
    const list = document.getElementById('searchResults');
    const spinner = document.getElementById('searchSpinner');

    if (val.length < 3) {
        list.classList.add('hidden');
        return;
    }

    spinner.classList.remove('hidden');
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(async () => {
        try {
            // Nota: Ajustamos la ruta para apuntar a la API correcta en V3
            // Asumimos que la API sigue en api/admin/search_clients.php relativo a la raíz
            const res = await fetch(`../v2/api/admin/search_clients.php?q=${val}`);

            const contentType = res.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Respuesta no válida del servidor");
            }

            const data = await res.json();

            list.innerHTML = '';

            if (data.length > 0) {
                list.classList.remove('hidden');
                data.forEach(c => {
                    const item = document.createElement('div');
                    item.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b flex justify-between items-center transition';
                    item.innerHTML = `
                        <div>
                            <p class="font-bold text-gray-800 text-sm">${c.name}</p>
                            <p class="text-xs text-gray-500">C.I: ${c.cedula}</p>
                        </div>
                        <div class="text-right">
                             <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-1 rounded font-bold">${c.plan_name || 'S/N'}</span>
                        </div>
                    `;
                    item.onclick = () => selectClient(c);
                    list.appendChild(item);
                });
            } else {
                list.classList.remove('hidden');
                list.innerHTML = '<div class="p-4 text-gray-500 text-sm text-center">No se encontraron clientes</div>';
            }
        } catch (error) {
            console.error("Error búsqueda:", error);
            list.classList.remove('hidden');
            list.innerHTML = '<div class="p-4 text-red-500 text-xs text-center">Error conectando con base de datos.</div>';
        } finally {
            spinner.classList.add('hidden');
        }
    }, 300);
}

// Seleccionar Cliente
function selectClient(c) {
    document.getElementById('selectedClientId').value = c.id;
    document.getElementById('lblClientName').textContent = c.name;
    document.getElementById('lblClientCedula').textContent = c.cedula;
    document.getElementById('lblClientPlan').textContent = c.plan_name || 'Sin Plan';

    document.getElementById('searchResults').classList.add('hidden');
    document.getElementById('docSearchInput').value = '';
    document.getElementById('selectedClientPanel').classList.remove('hidden');
    document.getElementById('docActions').classList.remove('opacity-40', 'pointer-events-none');
}

// Limpiar selección
function clearSelection() {
    document.getElementById('selectedClientId').value = '';
    document.getElementById('selectedClientPanel').classList.add('hidden');
    document.getElementById('docActions').classList.add('opacity-40', 'pointer-events-none');
    document.getElementById('docSearchInput').focus();
}

// Funciones del Modal
function openDocModal(type) {
    document.getElementById('docGeneratorModal').classList.remove('hidden');
    document.getElementById('formDocType').value = type;

    document.querySelectorAll('.dyn-field').forEach(el => el.classList.add('hidden'));

    if (type === 'CAMBIO_PLAN') document.getElementById('f-plan').classList.remove('hidden');
    if (type === 'REUBICACION') document.getElementById('f-reubicacion').classList.remove('hidden');
    if (type === 'CAMBIO_DOMICILIO') document.getElementById('f-domicilio').classList.remove('hidden');
    if (type === 'CAMBIO_TITULAR') document.getElementById('f-titular').classList.remove('hidden');
    if (['RETIRO', 'PAUSA', 'INSPECCION'].includes(type)) document.getElementById('f-motivo').classList.remove('hidden');
}

function closeDocModal() {
    document.getElementById('docGeneratorModal').classList.add('hidden');
}

// Inicialización de Event Listeners cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('docForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const type = document.getElementById('formDocType').value;
            const cid = document.getElementById('selectedClientId').value;
            // Usamos variables globales si existen, o los inputs ocultos
            const staffId = document.getElementById('currentStaffId').value;
            const staffName = document.getElementById('currentStaffName').value;
            const branch = document.getElementById('docBranchSelector').value;

            const formData = new FormData(this);

            let params = `client_id=${cid}&type=${type}&staff_id=${staffId}&staff_name=${staffName}&branch=${branch}`;

            params += `&target_plan=${encodeURIComponent(formData.get('target_plan') || '')}`;
            params += `&conn_type=${encodeURIComponent(formData.get('conn_type') || '')}`;
            params += `&move_type_int=${encodeURIComponent(formData.get('move_type_int') || '')}`;
            params += `&new_address_reu=${encodeURIComponent(formData.get('new_address_reu') || '')}`;
            params += `&new_address_dom=${encodeURIComponent(formData.get('new_address_dom') || '')}`;
            params += `&move_type_dom=${encodeURIComponent(formData.get('move_type_dom') || '')}`;
            params += `&new_name=${encodeURIComponent(formData.get('new_name') || '')}`;
            params += `&new_cedula=${encodeURIComponent(formData.get('new_cedula') || '')}`;
            params += `&new_address=${encodeURIComponent(formData.get('new_address') || '')}`;
            params += `&new_phone=${encodeURIComponent(formData.get('new_phone') || '')}`;
            params += `&new_email=${encodeURIComponent(formData.get('new_email') || '')}`;
            params += `&reason=${encodeURIComponent(formData.get('reason_general') || '')}`;
            params += `&date_start=${encodeURIComponent(formData.get('date_start') || '')}`;

            // Abrimos la API de v2 porque aún no migramos el backend
            window.open(`../v2/api/admin/generate_document.php?${params}`, '_blank');
            closeDocModal();
        });
    }
});
