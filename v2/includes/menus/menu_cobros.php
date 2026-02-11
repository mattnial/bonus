<aside class="w-64 bg-emerald-900 text-white flex flex-col shadow-xl z-20 h-screen sticky top-0">
    <div class="p-6 flex items-center gap-3 border-b border-emerald-800">
        <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center font-bold text-white shadow-lg">$</div>
        <h1 class="text-xl font-bold tracking-tight text-emerald-50">VILCANET</h1>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-6 overflow-y-auto">
        
        <div>
            <p class="px-3 text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 opacity-70">Operaciones</p>
            <div class="space-y-1">
                <a href="/inicio" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm">
                    <i class="fas fa-home w-5 text-center opacity-70"></i> <span>Dashboard Inicio</span>
                </a>

                <button onclick="toggleSubmenu('submenu-mora')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm text-left group">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-invoice-dollar w-5 text-center opacity-70 group-hover:text-emerald-300"></i> 
                        <span>Gestión de Mora</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] opacity-50 transition-transform duration-200" id="icon-submenu-mora"></i>
                </button>

                <div id="submenu-mora" class="hidden pl-4 space-y-1 bg-emerald-950/30 rounded-lg py-2 mt-1 border-l-2 border-emerald-600/30 ml-4 transition-all duration-300">
                    <a href="/cobranzas" onclick="switchDebtTab('pending')" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-emerald-100 hover:text-white hover:bg-emerald-800 transition-colors">
                        <i class="fas fa-circle text-[6px] text-red-400"></i> Pendientes de Gestión
                    </a>
                    <a href="/cobranzas" onclick="switchDebtTab('promises')" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-emerald-100 hover:text-white hover:bg-emerald-800 transition-colors">
                        <i class="fas fa-circle text-[6px] text-green-400"></i> Promesas Vigentes
                    </a>
                </div>
                <a href="/convenios" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm">
                    <i class="fas fa-handshake w-5 text-center opacity-70"></i> <span>Historial Convenios</span>
                </a>
            </div>
        </div>

        <div>
            <p class="px-3 text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 opacity-70">Control Técnico</p>
            <div class="space-y-1">
                <a href="/lista_corte" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-red-500/20 text-red-100 transition-all text-sm group">
                    <i class="fas fa-scissors w-5 text-center opacity-70 group-hover:text-red-400"></i> <span>Lista de Suspensión</span>
                </a>
                <a href="/reconexiones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm">
                    <i class="fas fa-plug w-5 text-center opacity-70"></i> <span>Por Reconectar</span>
                </a>
            </div>
        </div>

        <div>
            <p class="px-3 text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 opacity-70">Reportes</p>
            <div class="space-y-1">
                <a href="/reportes_cobros" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm">
                    <i class="fas fa-chart-line w-5 text-center opacity-70"></i> <span>Metricas</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="p-4 border-t border-emerald-800 bg-emerald-950">
        <div class="flex items-center gap-3 p-2 rounded-lg bg-white/5 border border-white/10">
            <img src="<?php echo $_SESSION['avatar'] ?? '/assets/img/default.png'; ?>" class="w-10 h-10 rounded-full border-2 border-emerald-500 object-cover shadow-inner">
            <div class="overflow-hidden">
                <p class="text-xs font-bold text-white truncate"><?php echo $_SESSION['name']; ?></p>
                <p class="text-[10px] text-emerald-400 font-medium">Agente de Cobros</p>
            </div>
        </div>
        <a href="/api/admin/logout.php" class="mt-4 w-full flex items-center justify-center gap-2 text-[10px] font-black uppercase tracking-widest text-red-400 hover:bg-red-500/10 hover:text-red-300 py-2 rounded-lg border border-red-500/20 transition-all">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</aside>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        submenu.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>