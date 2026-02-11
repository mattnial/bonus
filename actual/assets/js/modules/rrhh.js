async function loadRRHH() {
    const grid = document.getElementById('rrhhGrid');
    if (!grid) return;
    grid.innerHTML = 'Cargando...';
    try {
        const res = await fetch(`${API_URL}/admin/get_staff_list.php`);
        const staff = await res.json();
        grid.innerHTML = '';
        staff.forEach(s => {
            grid.innerHTML += `<div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer" onclick="openRRHHModal(${s.id})"><div class="w-12 h-12 bg-blue-100 rounded-full mx-auto flex items-center justify-center font-bold text-blue-600 mb-2">${s.name.charAt(0)}</div><h3 class="font-bold">${s.name}</h3><span class="text-xs text-gray-500 uppercase">${s.role}</span></div>`;
        });
    } catch (e) { }
}

async function openRRHHModal(id) {
    CURRENT_STAFF_ID = id;
    document.getElementById('rrhhModal').classList.remove('hidden');
    document.getElementById('sanctionsList').innerHTML = 'Cargando...';
    const res = await fetch(`${API_URL}/admin/get_staff_history.php?id=${id}`);
    const data = await res.json();
    document.getElementById('rrhhName').textContent = data.staff.name;
    const list = document.getElementById('sanctionsList');
    list.innerHTML = '';
    if (data.sanctions.length === 0) list.innerHTML = '<p class="text-center text-gray-400">Limpio ✨</p>';
    data.sanctions.forEach(s => {
        let color = s.type === 'MULTA' ? 'bg-red-100 text-red-700' : 'bg-gray-100';
        list.innerHTML += `<div class="p-2 border-l-4 border-red-400 bg-white mb-2 shadow-sm flex justify-between"><div><span class="text-[10px] px-1 rounded ${color}">${s.type}</span> <span class="text-sm">${s.reason}</span></div><span class="text-red-600 font-bold">-$${s.amount}</span></div>`;
    });
}

function toggleAmount(type) {
    const input = document.getElementById('sancAmount');
    if (type === 'MULTA') input.classList.remove('hidden'); else input.classList.add('hidden');
}

async function handleSanctionSubmit(e) {
    e.preventDefault();
    if (!await askConfirm("¿Registrar sanción?")) return;
    const formData = new FormData();
    formData.append('staff_id', CURRENT_STAFF_ID);
    formData.append('admin_id', JSON.parse(localStorage.getItem('vilcanet_staff')).id);
    formData.append('type', document.getElementById('sancType').value);
    formData.append('reason', document.getElementById('sancReason').value);
    formData.append('amount', document.getElementById('sancAmount').value);

    await fetch(`${API_URL}/admin/save_sanction.php`, { method: 'POST', body: formData });
    showToast("Sanción registrada", "success");
    openRRHHModal(CURRENT_STAFF_ID);
}