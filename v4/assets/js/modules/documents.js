/**
 * documents.js
 * Módulo para Generador de Documentos
 * Versión corregida para evitar errores de redeclaración y eventos duplicados.
 */

// 1. Usamos var y un nombre único para evitar colisiones
var docSearchTimer = null;

// BUSCAR CLIENTE
function buscarClienteContrato(val) {
    const list = document.getElementById('searchResults');
    const spinner = document.getElementById('searchSpinner');

    if (val.length < 3) {
        if (list) list.classList.add('hidden');
        return;
    }

    if (spinner) spinner.classList.remove('hidden');

    // Usamos la variable global segura
    clearTimeout(docSearchTimer);

    docSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`api/admin/search_clients.php?q=${val}`);
            if (!res.ok) throw new Error("Error en red");

            const data = await res.json();

            if (list) {
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
            }
        } catch (error) {
            console.error("Error búsqueda:", error);
            if (list) list.classList.add('hidden');
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }, 300);
}

// SELECCIONAR CLIENTE
function selectClient(c) {
    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
    const setTxt = (id, t) => { const el = document.getElementById(id); if (el) el.textContent = t; };

    setVal('selectedClientId', c.id);
    setTxt('lblClientName', c.name);
    setTxt('lblClientCedula', c.cedula);
    setTxt('lblClientPlan', c.plan_name || 'Sin Plan');

    document.getElementById('searchResults')?.classList.add('hidden');
    setVal('docSearchInput', '');
    document.getElementById('selectedClientPanel')?.classList.remove('hidden');
    document.getElementById('docActions')?.classList.remove('opacity-40', 'pointer-events-none');
}

// LIMPIAR
function clearSelection() {
    document.getElementById('selectedClientId').value = '';
    document.getElementById('selectedClientPanel')?.classList.add('hidden');
    document.getElementById('docActions')?.classList.add('opacity-40', 'pointer-events-none');
    document.getElementById('docSearchInput')?.focus();
}

// MODAL
function openDocModal(type) {
    const modal = document.getElementById('docGeneratorModal');
    if (!modal) return;

    modal.classList.remove('hidden');
    document.getElementById('formDocType').value = type;

    document.querySelectorAll('.dyn-field').forEach(el => el.classList.add('hidden'));

    if (type === 'CAMBIO_PLAN') document.getElementById('f-plan')?.classList.remove('hidden');
    if (type === 'REUBICACION') document.getElementById('f-reubicacion')?.classList.remove('hidden');
    if (type === 'CAMBIO_DOMICILIO') document.getElementById('f-domicilio')?.classList.remove('hidden');
    if (type === 'CAMBIO_TITULAR') document.getElementById('f-titular')?.classList.remove('hidden');
    if (['RETIRO', 'PAUSA', 'INSPECCION'].includes(type)) document.getElementById('f-motivo')?.classList.remove('hidden');
}

function closeDocModal() {
    document.getElementById('docGeneratorModal')?.classList.add('hidden');
}

// AUTO-INICIALIZACIÓN SEGURA (IIFE)
(function () {
    const docForm = document.getElementById('docForm');

    // Verificamos si ya existe el listener para no duplicarlo
    if (docForm && !docForm.getAttribute('data-init')) {
        docForm.setAttribute('data-init', 'true'); // Marcamos como listo

        docForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const params = new URLSearchParams();

            // Agregamos IDs manualmente
            params.append('client_id', document.getElementById('selectedClientId').value);
            params.append('staff_id', document.getElementById('currentStaffId').value);
            params.append('staff_name', document.getElementById('currentStaffName').value);
            params.append('type', document.getElementById('formDocType').value);
            params.append('branch', document.getElementById('docBranchSelector').value);

            // Agregamos el resto del form
            for (const [k, v] of formData.entries()) {
                if (!params.has(k)) params.append(k, v);
            }

            window.open(`api/admin/generate_document.php?${params.toString()}`, '_blank');
            closeDocModal();
        });
        console.log("✅ Módulo Documentos cargado correctamente");
    }
})();