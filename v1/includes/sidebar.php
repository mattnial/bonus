<aside id="sidebar" class="bg-[#0f172a] text-white w-72 h-[calc(100vh-64px)] fixed lg:static top-16 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-20 flex flex-col border-r border-slate-800/50 shadow-[4px_0_24px_rgba(0,0,0,0.2)]">
    
    <div class="px-6 py-8 border-b border-slate-800/50 bg-[#0f172a]">
        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-[0.2em] mb-1">PANEL PRINCIPAL</p>
        <h2 class="text-lg font-bold text-slate-200 tracking-tight">Navegación</h2>
    </div>

    <div id="dynamicMenuContainer" class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
        <div class="animate-pulse space-y-3">
            <div class="h-12 bg-slate-800/50 rounded-xl"></div>
            <div class="h-12 bg-slate-800/50 rounded-xl"></div>
            <div class="h-12 bg-slate-800/50 rounded-xl"></div>
        </div>
    </div>

    <div class="p-4 border-t border-slate-800/50 bg-[#0b1120]">
        <button onclick="openProfileModal()" class="flex items-center justify-center gap-3 w-full px-4 py-3 rounded-xl hover:bg-red-500/10 hover:text-red-400 text-slate-400 transition-all duration-300 group border border-transparent hover:border-red-500/20">
            <i class="fas fa-sign-out-alt group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-sm font-semibold">Cerrar Sesión</span>
        </button>
    </div>
</aside>

<div onclick="document.getElementById('sidebar').classList.add('-translate-x-full')" class="fixed inset-0 bg-black/60 z-10 lg:hidden hidden backdrop-blur-sm" id="sidebarOverlay"></div>