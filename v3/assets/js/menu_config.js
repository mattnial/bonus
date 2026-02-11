/**
 * ARCHIVO: assets/js/menu_config.js
 * CONFIGURACIÓN MAESTRA DE MENÚS v3
 */

window.APP_MENU_CONFIG = [
    // --- CABECERA PRINCIPAL ---
    { type: 'header', label: 'Navegación Principal' },

    {
        id: 'home',
        type: 'simple',
        label: 'Dashboard',
        icon: 'fas fa-home',
        href: 'index.php?module=Dashboard&view=home'
    },

    // --- SECCIÓN COMERCIAL Y COBRANZA ---
    { type: 'header', label: 'Gestión Comercial' },
    {
        id: 'billing',
        type: 'dropdown',
        label: 'Cobranzas y Cortes',
        icon: 'fas fa-wallet',
        subitems: [
            { label: 'Cartera Vencida', href: 'index.php?module=Billing&view=list' },
            { label: 'Gestión de Cortes', href: 'index.php?module=Billing&view=cortes' },
            { label: 'Reactivaciones', href: 'index.php?module=Billing&view=reactivaciones' },
            { label: 'Convenios de Pago', href: 'index.php?module=Billing&view=convenios' }
        ]
    },

    // --- RESTORED FROM V1 ---
    {
        id: 'ventas',
        type: 'dropdown',
        label: 'Ventas y Contratos',
        icon: 'fas fa-file-signature',
        subitems: [
            { label: 'Nueva Venta', href: 'index.php?module=Sales&view=new' },
            { label: 'Historial Contratos', href: 'index.php?module=Sales&view=contracts' }
        ]
    },

    // --- RESTORED: DOCUMENTOS ---
    {
        id: 'docs',
        type: 'simple',
        label: 'Documentos',
        icon: 'fas fa-folder-open',
        href: 'index.php?module=Documents&view=list'
    },

    // --- SECCIÓN TÉCNICA ---
    { type: 'header', label: 'Departamento Técnico' },

    {
        id: 'tickets',
        type: 'simple',
        label: 'Soporte',
        icon: 'fas fa-tools',
        href: 'index.php?module=Tickets&view=list'
    },
    // --- RESTORED FROM V1 ---
    {
        id: 'installations',
        type: 'simple',
        label: 'Instalaciones (OT)',
        icon: 'fas fa-hard-hat',
        href: 'index.php?module=Installations&view=list'
    },

    // --- SECCIÓN ADMINISTRATIVA ---
    { type: 'header', label: 'Administración' },

    {
        id: 'rrhh',
        type: 'dropdown',
        label: 'Recursos Humanos',
        icon: 'fas fa-user-tie',
        subitems: [
            { label: 'Personal', href: 'index.php?module=RRHH&view=staff' },
            { label: 'Sanciones', href: 'index.php?module=RRHH&view=sanctions' }
        ]
    },
    {
        id: 'reports',
        type: 'simple',
        label: 'Reportes',
        icon: 'fas fa-chart-pie',
        href: 'index.php?module=Reports&view=dashboard'
    },
    {
        id: 'config',
        type: 'simple',
        label: 'Configuración',
        icon: 'fas fa-cogs',
        href: 'index.php?module=Config&view=general'
    }
];