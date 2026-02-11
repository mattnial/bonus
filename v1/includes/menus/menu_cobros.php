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
                <a href="/cobranzas" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-emerald-800/50 text-white border-l-4 border-emerald-400 text-sm font-bold shadow-sm">
                    <i class="fas fa-file-invoice-dollar w-5 text-center"></i> <span>Gestión de Mora</span>
                </a>
                <a href="/convenios" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm">
                    <i class="fas fa-handshake w-5 text-center opacity-70"></i> <span>Promesas de Pago</span>
                </a>
            </div>
        </div>

        <div>
            <p class="px-3 text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 opacity-70">Control de Servicio</p>
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
                    <i class="fas fa-chart-line w-5 text-center opacity-70"></i> <span>Cartera Recuperada</span>
                </a>
                <a href="/historial_global" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all text-sm">
                    <i class="fas fa-history w-5 text-center opacity-70"></i> <span>Log de Gestiones</span>
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
        </div>
    </div>
</aside>