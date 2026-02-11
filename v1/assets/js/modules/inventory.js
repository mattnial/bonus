let html5QrCode = null;

// PESTAÑAS INTERNAS
function switchFinanceTab(tab) {
    document.querySelectorAll('.finance-view').forEach(el => el.classList.add('hidden'));
    document.getElementById('view-' + tab).classList.remove('hidden');
    const btnDebt = document.getElementById('tab-debt');
    const btnStock = document.getElementById('tab-stock');

    if (tab === 'stock') {
        btnStock.className = "px-6 py-2 rounded-md text-sm font-bold bg-blue-100 text-blue-700 transition";
        btnDebt.className = "px-6 py-2 rounded-md text-sm font-bold text-gray-500 hover:bg-gray-50 transition";
        loadInventory();
        loadInventoryStats();
    } else {
        btnDebt.className = "px-6 py-2 rounded-md text-sm font-bold bg-blue-100 text-blue-700 transition";
        btnStock.className = "px-6 py-2 rounded-md text-sm font-bold text-gray-500 hover:bg-gray-50 transition";
        loadDebtors();
    }
}

// CARGAR INVENTARIO
async function loadInventory() {
    const tbody = document.getElementById('stockTable');
    if (!tbody) return;
    const q = document.getElementById('stockSearch').value || '';
    const status = document.getElementById('filterState').value || '';

    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500"></i> Cargando...</td></tr>';

    try {
        const res = await fetch(`${API_URL}/admin/get_inventory.php?q=${q}&status=${status}`);
        const data = await res.json();
        if (data.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-400">Sin datos.</td></tr>'; return; }

        tbody.innerHTML = '';
        data.forEach(item => {
            const img = item.image_url ? `uploads/inventory/${item.image_url}` : 'assets/img/logo.png';
            let btn = item.status === 'BODEGA'
                ? `<button onclick="openFastAssign(${item.id}, '${item.model}', '${item.type}', ${item.quantity})" class="bg-orange-100 text-orange-700 px-3 py-1 rounded text-xs font-bold border border-orange-200">ASIGNAR</button>`
                : `<span class="text-gray-400 text-xs italic">En uso</span>`;

            tbody.innerHTML += `
                <tr class="hover:bg-gray-50 border-b" data-type="${item.type}" data-qty="${item.quantity}" data-model="${item.model}">
                    <td class="px-4 py-3 text-center"><input type="checkbox" class="stock-check w-4 h-4 cursor-pointer" value="${item.id}"></td>
                    <td class="px-4 py-3"><img src="${img}" class="w-10 h-10 object-cover rounded border bg-white" onclick="window.open('${img}')"></td>
                    <td class="px-4 py-3"><p class="font-bold text-sm text-gray-800">${item.brand} ${item.model}</p><p class="text-xs font-mono text-gray-500">${item.serial_number}</p></td>
                    <td class="px-4 py-3 text-xs uppercase">${item.location || 'Bodega'}</td>
                    <td class="px-4 py-3 text-center"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">${item.quantity}</span></td>
                    <td class="px-4 py-3 text-center">${btn}</td>
                </tr>`;
        });
        loadInventoryStats();
    } catch (e) { console.error(e); }
}

async function loadInventoryStats() {
    const container = document.getElementById('stockSummary');
    if (!container) return;
    try {
        const res = await fetch(`${API_URL}/admin/get_inventory_stats.php`);
        const data = await res.json();
        container.innerHTML = '';
        ['ONT', 'ROUTER', 'BOBINA', 'MATERIAL'].forEach(t => {
            const count = data.counts[t] || 0;
            container.innerHTML += `<div class="bg-white p-2 rounded shadow-sm border text-center"><p class="text-[10px] uppercase font-bold text-gray-500">${t}</p><p class="text-xl font-bold">${count}</p></div>`;
        });
        const alertBox = document.getElementById('stockAlerts');
        if (alertBox && data.alerts.length > 0) {
            alertBox.classList.remove('hidden');
            document.getElementById('stockAlertText').innerHTML = data.alerts.join(' &nbsp; | &nbsp; ');
        } else if (alertBox) { alertBox.classList.add('hidden'); }
    } catch (e) { }
}

// MODALES HELPERS
function openStockModal() { document.getElementById('stockModal').classList.remove('hidden'); toggleSerialInput(); }
function closeStockModal() { document.getElementById('stockModal').classList.add('hidden'); try { if (html5QrCode) html5QrCode.stop(); } catch (e) { } }
function toggleSelectAll(s) { document.querySelectorAll('.stock-check').forEach(c => c.checked = s.checked); }

function openHistoryModal() {
    document.getElementById('historyModal').classList.remove('hidden');
    const tbody = document.getElementById('historyTableBody');
    tbody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando...</td></tr>';
    fetch(`${API_URL}/admin/get_inventory_history.php`).then(r => r.json()).then(data => {
        tbody.innerHTML = '';
        data.forEach(m => {
            tbody.innerHTML += `<tr class="border-b"><td class="px-4 py-2 text-xs">${m.created_at}</td><td class="px-4 py-2 text-sm font-bold">${m.item_type}</td><td class="px-4 py-2 text-xs">${m.type}</td><td class="px-4 py-2 text-xs">${m.notes}</td></tr>`;
        });
    });
}

// DESPACHO
function openDispatchModal() {
    const selected = document.querySelectorAll('.stock-check:checked');
    if (selected.length === 0) return showToast("Selecciona items", "warning");
    document.getElementById('countDispatch').innerText = selected.length;
    document.getElementById('dispatchModal').classList.remove('hidden');
    const select = document.getElementById('dispatchTech');
    if (select.options.length <= 0) {
        fetch(`${API_URL}/admin/get_staff_list.php`).then(r => r.json()).then(d => {
            select.innerHTML = ''; d.forEach(s => { if (s.role === 'TECNICO' || s.role === 'ADMIN') select.innerHTML += `<option value="${s.id}">${s.name}</option>`; });
        });
    }
}

async function processDispatch() {
    const techId = document.getElementById('dispatchTech').value;
    const note = document.getElementById('dispatchNote').value;
    const checkboxes = document.querySelectorAll('.stock-check:checked');
    if (!techId) return showToast("Selecciona técnico", "warning");

    let itemsToDispatch = [];
    for (const cb of checkboxes) {
        const row = cb.closest('tr');
        const type = row.dataset.type;
        const maxQty = parseInt(row.dataset.qty);
        let qty = 1;
        if (['BOBINA', 'CABLE_RED', 'MATERIAL', 'CONECTORES'].includes(type)) {
            const userQty = prompt(`¿Cuántos ${row.dataset.model}? (Máx: ${maxQty})`, maxQty);
            if (!userQty) return;
            qty = parseInt(userQty);
        }
        itemsToDispatch.push({ id: cb.value, qty: qty });
    }

    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));
    try {
        const res = await fetch(`${API_URL}/admin/inventory_ops.php?action=dispatch`, {
            method: 'POST', body: JSON.stringify({ tech_id: techId, items: itemsToDispatch, note: note, admin_id: user.id })
        });
        if (res.ok) {
            showToast("Despacho exitoso", "success");
            document.getElementById('dispatchModal').classList.add('hidden');
            loadInventory();
        }
    } catch (e) { showToast("Error", "error"); }
}

// GUARDADO
async function handleStockSubmit(e) {
    e.preventDefault();
    const formData = new FormData();
    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));

    formData.append('type', document.getElementById('invType').value);
    formData.append('location', document.getElementById('invLocation').value);
    formData.append('cost', document.getElementById('invCost').value);
    formData.append('brand', document.getElementById('invBrand').value);
    formData.append('model', document.getElementById('invModel').value);
    formData.append('admin_id', user.id);
    if (document.getElementById('invPhoto').files[0]) formData.append('image', document.getElementById('invPhoto').files[0]);

    const isEquip = ['ONT', 'ROUTER', 'MESH', 'RADIO'].includes(document.getElementById('invType').value);
    if (isEquip) {
        formData.append('quantity', 1);
        formData.append('serial', document.getElementById('invSerial').value);
        formData.append('mac', document.getElementById('invMac').value);
    } else {
        formData.append('quantity', document.getElementById('invQty').value);
        formData.append('serial', '');
    }

    try {
        await fetch(`${API_URL}/admin/save_inventory.php`, { method: 'POST', body: formData });
        showToast("Guardado", "success");
        closeStockModal();
        loadInventory();
    } catch (err) { showToast(err.message, "error"); }
}

function toggleSerialInput() {
    const type = document.getElementById('invType').value;
    const isEquip = ['ONT', 'ROUTER', 'MESH', 'RADIO'].includes(type);
    if (document.getElementById('sectionEquipment')) {
        if (isEquip) {
            document.getElementById('sectionEquipment').classList.remove('hidden');
            document.getElementById('sectionMaterial').classList.add('hidden');
            document.getElementById('invQty').value = 1;
        } else {
            document.getElementById('sectionEquipment').classList.add('hidden');
            document.getElementById('sectionMaterial').classList.remove('hidden');
        }
    }
}

// ESCÁNER
function startScanner() {
    document.getElementById('scannerContainer').classList.remove('hidden');
    if (html5QrCode) { try { html5QrCode.stop().then(() => html5QrCode.clear()); } catch (e) { } }
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({ facingMode: "environment" }, { fps: 10 }, (d) => {
        document.getElementById('invSerial').value = d;
        stopScanner();
        showToast("Código: " + d, "success");
    });
}
function stopScanner() { document.getElementById('scannerContainer').classList.add('hidden'); try { if (html5QrCode) html5QrCode.stop(); } catch (e) { } }
function previewStockImg(input) {
    if (input.files[0]) {
        const r = new FileReader();
        r.onload = (e) => {
            document.getElementById('imgPreviewBox').classList.add('hidden');
            document.getElementById('imgPreviewReal').src = e.target.result;
            document.getElementById('imgPreviewReal').classList.remove('hidden');
        };
        r.readAsDataURL(input.files[0]);
    }
}

// ASIGNACIÓN RÁPIDA (ACTA)
function openFastAssign(id, name, type, maxQty) {
    document.getElementById('assignItemId').value = id;
    document.getElementById('assignItemName').textContent = name;
    document.getElementById('fastAssignModal').classList.remove('hidden');

    const select = document.getElementById('assignTech');
    if (select.options.length <= 0) {
        fetch(`${API_URL}/admin/get_staff_list.php`).then(r => r.json()).then(d => {
            select.innerHTML = ''; d.forEach(s => select.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        });
    }
    const divQty = document.getElementById('assignQtyDiv');
    if (['BOBINA', 'CABLE_RED', 'MATERIAL'].includes(type)) {
        divQty.classList.remove('hidden');
        document.getElementById('assignMax').textContent = maxQty;
        document.getElementById('assignQty').max = maxQty;
    } else { divQty.classList.add('hidden'); document.getElementById('assignQty').value = 1; }
}

async function processFastAssign() {
    const itemId = document.getElementById('assignItemId').value;
    const techName = document.getElementById('assignTech').options[document.getElementById('assignTech').selectedIndex].text;
    const techId = document.getElementById('assignTech').value;
    const qty = document.getElementById('assignQty').value;
    const itemName = document.getElementById('assignItemName').textContent;
    const user = JSON.parse(localStorage.getItem('vilcanet_staff'));

    if (!await askConfirm(`¿Confirmar entrega?`)) return;

    try {
        const res = await fetch(`${API_URL}/admin/inventory_ops.php?action=dispatch`, {
            method: 'POST', body: JSON.stringify({ tech_id: techId, items: [{ id: itemId, qty: qty }], note: "Entrega con Acta", admin_id: user.id })
        });
        if (res.ok) {
            document.getElementById('fastAssignModal').classList.add('hidden');
            showToast("Asignado", "success");
            loadInventory();

            const w = window.open('', '_blank', 'width=800,height=900');
            w.document.write(`<html><body><h2 style="text-align:center">ACTA DE ENTREGA</h2><p><strong>Fecha:</strong> ${new Date().toLocaleDateString()}</p><p>Se entrega a: <strong>${techName}</strong></p><table border="1" style="width:100%;border-collapse:collapse;margin:20px 0"><tr><th style="padding:10px">Cant.</th><th style="padding:10px">Descripción</th></tr><tr><td style="padding:10px">${qty}</td><td style="padding:10px">${itemName}</td></tr></table><br><br>_____________________<br>Firma Recibido</body></html>`);
            w.document.close();
            w.print();
        }
    } catch (e) { showToast("Error", "error"); }
}

// DEUDORES (Parte de Cobranzas)
async function loadDebtors() {
    const t = document.getElementById('debtorsTable');
    if (!t) return;
    t.innerHTML = '<tr><td colspan="5" class="text-center py-8">Cargando...</td></tr>';
    try {
        const res = await fetch(`${API_URL}/admin/get_debtors.php`);
        const data = await res.json();
        t.innerHTML = '';
        data.forEach(d => {
            let btn = d.service_status === 'CORTADO'
                ? `<button onclick="toggleService(${d.id},'ACTIVAR','${d.name}')" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">ACTIVAR</button>`
                : `<button onclick="toggleService(${d.id},'CORTAR','${d.name}')" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">CORTAR</button>`;
            t.innerHTML += `<tr class="border-b hover:bg-gray-50"><td class="p-4 font-bold text-gray-700">${d.name}</td><td class="p-4 text-center font-bold text-red-600">${d.current_debt_months} Meses</td><td class="p-4 text-center text-gray-800">$${d.total_due}</td><td class="p-4 text-center">${d.service_status}</td><td class="p-4 text-center">${btn}</td></tr>`;
        });
    } catch (e) { }
}

async function toggleService(clientId, action, clientName) {
    if (!await askConfirm(`${action} servicio a ${clientName}?`)) return;
    try {
        await fetch(`${API_URL}/admin/toggle_service.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ client_id: clientId, action: action })
        });
        showToast("Servicio actualizado", "success");
        loadDebtors();
    } catch (e) { showToast("Error", "error"); }
}