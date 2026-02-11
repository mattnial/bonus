<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vilcanet CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        /* Ajustes para el Scrollbar del Sidebar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 h-screen overflow-hidden flex flex-col">

<header class="bg-white border-b h-16 flex justify-between items-center px-6 shadow-sm z-30 shrink-0">
    
    <div class="flex items-center gap-4">
        <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="lg:hidden text-gray-500 hover:text-blue-600">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <img src="assets/img/logo.png" class="h-8">
        <span class="font-bold text-gray-700 hidden md:block tracking-tight text-lg">VILCANET</span>
    </div>

    <div class="flex items-center gap-6">
        <div class="relative cursor-pointer group" onclick="toggleNotifications()">
            <i class="fas fa-bell text-gray-400 group-hover:text-blue-600 transition text-lg"></i>
            <span id="notif-badge" class="hidden absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">!</span>
            
            <div id="notifDropdown" class="hidden absolute right-0 top-10 w-72 bg-white border shadow-xl rounded-xl p-2 z-50 animate-fade-in-up">
                <p class="text-xs text-gray-400 p-3 text-center">No hay notificaciones nuevas</p>
            </div>
        </div>

        <div class="flex items-center gap-3 cursor-pointer pl-4 border-l" onclick="openProfileModal()">
            <div class="text-right hidden md:block leading-tight">
                <p class="text-sm font-bold text-gray-800" id="userName">Cargando...</p>
                <p class="text-[10px] text-gray-500 font-bold uppercase" id="userRole">...</p>
            </div>
            <img id="userAvatarImg" src="assets/img/default_admin.png" class="w-10 h-10 rounded-full border-2 border-gray-100 object-cover shadow-sm">
        </div>
    </div>
</header>