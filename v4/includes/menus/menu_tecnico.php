<aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-20">
    <div class="p-6 flex items-center gap-3 border-b border-gray-700">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white">ST</div>
        <h1 class="text-xl font-bold tracking-tight">SOPORTE</h1>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1">
        <a href="/inicio" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all">
            <i class="fas fa-home w-5 text-center"></i> <span>Inicio</span>
        </a>
        <a href="/tecnico" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-blue-900/50 text-white border-l-4 border-blue-500">
            <i class="fas fa-tools w-5 text-center"></i> <span>Mis Tickets</span>
        </a>
        <a href="/instalaciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all">
            <i class="fas fa-satellite-dish w-5 text-center"></i> <span>Instalaciones</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-700 bg-gray-800">
        <div class="flex items-center gap-3">
            <img src="<?php echo $_SESSION['avatar'] ?? '/assets/img/default.png'; ?>" class="w-9 h-9 rounded-full border-2 border-blue-500">
            <div>
                <p class="text-sm font-medium text-white"><?php echo $_SESSION['name']; ?></p>
                <p class="text-xs text-blue-300">Técnico</p>
            </div>
        </div>
        <a href="/api/admin/logout.php" class="mt-3 w-full flex items-center justify-center gap-2 text-xs text-red-400 hover:text-white py-1">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</aside>