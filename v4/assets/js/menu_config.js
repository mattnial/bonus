/**
 * ARCHIVO: assets/js/menu_config.js
 * CONFIGURACIÓN MAESTRA DE MENÚS
 */

window.APP_MENU_CONFIG = [
    // --- CABECERA PRINCIPAL ---
    { type: 'header', label: 'Navegación Principal' },

    {
        id: 'home',
        type: 'simple',
        label: 'Dashboard',
        icon: 'fas fa-home',
        // IMPORTANTE: Esto llama a la función que oculta los widgets y muestra las gráficas
        action: "loadModule('dashboard');"
    },

    // --- SECCIÓN COMERCIAL Y COBRANZA (NUEVOS WIDGETS) ---
    { type: 'header', label: 'Gestión Comercial' },
    {
        id: 'billing', // <--- IMPORTANTE: Este ID debe coincidir con lo que devuelva tu base de datos en permisos
        type: 'dropdown',
        label: 'Cobranzas y Cortes',
        icon: 'fas fa-wallet',
        subitems: [
            // El truco es usar loadModule() en lugar de switchView()
            {
                id: 'contratosd',
                type: 'simple',
                label: 'Ventasas',
                icon: 'fas fa-briefcase',
                view: 'contratos' // Mantenemos compatibilidad con tu sistema antiguo
            },
            { label: 'Gestión de Cortes', view: 'includes/views/contratos.php' },
            { label: 'Reactivaciones', action: "loadModule('reactivaciones')" }
        ]
    },

    // --- NUEVO MÓDULO: COMUNICACIÓN (A futuro) ---
    {
        id: 'messaging',
        type: 'dropdown',
        label: 'Comunicación',
        icon: 'fas fa-bullhorn',
        subitems: [
            // Usamos loadModule pasando el tipo de vista que queremos
            { label: '📢 Enviar Aviso Masivo', view: 'cartera' }, // Por defecto abre masivos
            { label: '✉️ Selección Manual', action: "loadModule('messaging');" }, // (Ajustaremos messaging.js luego)
            { label: '📜 Historial de Envíos', action: "loadModule('messaging');" }
        ]
    },

    {
        id: 'ventas',
        type: 'simple',
        label: 'Ventas',
        icon: 'fas fa-briefcase',
        view: 'ventas' // Mantenemos compatibilidad con tu sistema antiguo
    },

    // --- SECCIÓN TÉCNICA ---
    { type: 'header', label: 'Departamento Técnico' },

    {
        id: 'tickets',
        type: 'simple',
        label: 'Soporte',
        icon: 'fas fa-tools',
        view: 'tickets' // Mantenemos compatibilidad
    },

    {
        id: 'contratos',
        type: 'simple',
        label: 'Documentos',
        icon: 'fas fa-folder-open',
        view: 'contratos'
    },

    // NOTA: Este item parece redundante con el nuevo menú de "Gestión de Cortes",
    // pero lo dejo por si tienes funciones viejas que aún usas.
    {
        id: 'lista_corte',
        type: 'simple',
        label: 'Cortes y Reconexiones (Antiguo)',
        icon: 'fas fa-network-wired',
        view: 'billing'
    },

    // --- SECCIÓN ADMINISTRATIVA ---
    { type: 'header', label: 'Administración' },

    {
        id: 'rrhh',
        type: 'dropdown',
        label: 'Recursos Humanos',
        icon: 'fas fa-user-tie',
        subitems: [
            { label: 'Personal', action: "switchView('rrhh');" },
            { label: 'Sanciones', action: "switchView('rrhh');" }
        ]
    },
    {
        id: 'reports',
        type: 'simple',
        label: 'Reportes',
        icon: 'fas fa-chart-pie',
        view: 'reports'
    },
    {
        id: 'config',
        type: 'simple',
        label: 'Configuracion',
        icon: 'fas fa-cogs',
        view: 'config' // Esto abrirá includes/views/configuracion.php
    }
];