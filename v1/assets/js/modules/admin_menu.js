// ARCHIVO: assets/js/modules/admin_menu.js

// Al cargar la página, si estamos en admin, cargamos la lista de empleados
document.addEventListener('DOMContentLoaded', () => {
    // Solo cargar si existe el panel de configuración
    if (document.getElementById('menuUserSelect')) {
        loadUsersForConfig();
        loadAllMenuItems();
    }
});

async function loadUsersForConfig() {
    const select = document.getElementById('menuUserSelect');
    if (!select) return; // Evita errores si el elemento no existe

    try {
        // Usamos la ruta completa
        const res = await fetch(`${window.API_URL}/admin/get_staff_list.php`);

        if (!res.ok) throw new Error("Fallo al conectar con la lista de personal");

        const users = await res.json();

        select.innerHTML = '<option value="">Seleccione un empleado...</option>';
        users.forEach(u => {
            // Añadimos el rol entre paréntesis para identificarlo mejor
            select.innerHTML += `<option value="${u.id}">${u.name} (${u.role})</option>`;
        });

    } catch (e) {
        console.error("Error cargando personal en Configuración:", e);
        select.innerHTML = '<option value="">Error al cargar usuarios</option>';
    }
}

// 2. Cargar TODOS los botones disponibles (Checkboxes vacíos)
async function loadAllMenuItems() {
    const container = document.getElementById('menuCheckboxes');
    try {
        const res = await fetch(`${API_URL}/admin/menu_manager.php?action=list_all_items`);
        const items = await res.json();

        container.innerHTML = '';
        items.forEach(item => {
            container.innerHTML += `
                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="perm-check w-5 h-5 text-blue-600 rounded focus:ring-blue-500" value="${item.id}">
                    <div class="ml-3">
                        <span class="block text-sm font-bold text-gray-800"><i class="${item.icon} text-gray-400 w-6"></i> ${item.label}</span>
                    </div>
                </label>
            `;
        });
    } catch (e) { console.error("Error cargando items", e); }
}

// 3. Cuando selecciono un usuario, marcar sus casillas
async function loadUserPermissions(staffId) {
    // Primero desmarcar todo
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);

    if (!staffId) return;

    try {
        const res = await fetch(`${API_URL}/admin/menu_manager.php?action=get_user_permissions&staff_id=${staffId}`);
        const ids = await res.json(); // Array [1, 3, 5]

        ids.forEach(id => {
            const checkbox = document.querySelector(`.perm-check[value="${id}"]`);
            if (checkbox) checkbox.checked = true;
        });
    } catch (e) { console.error(e); }
}

// 4. Guardar Cambios
async function saveMenuConfig() {
    const staffId = document.getElementById('menuUserSelect').value;
    if (!staffId) return showToast("Seleccione un usuario primero", "warning");

    const selectedCheckboxes = document.querySelectorAll('.perm-check:checked');
    const ids = Array.from(selectedCheckboxes).map(cb => cb.value);

    try {
        const res = await fetch(`${API_URL}/admin/menu_manager.php?action=save_permissions`, {
            method: 'POST',
            body: JSON.stringify({ staff_id: staffId, menu_ids: ids })
        });
        const json = await res.json();

        if (json.success) {
            showToast("✅ Permisos actualizados correctamente", "success");
        } else {
            showToast("Error al guardar", "error");
        }
    } catch (e) { showToast("Error de conexión", "error"); }
}