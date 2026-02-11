<aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-20"> <div class="p-6 flex items-center gap-3 border-b border-gray-700">
        <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center font-bold text-white">RH</div>
        <h1 class="text-xl font-bold tracking-tight">TALENTO H.</h1>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1">
        
        <a href="/inicio" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all">
            <i class="fas fa-home w-5 text-center"></i> <span>Inicio</span>
        </a>

        <a href="/rrhh" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-pink-900/30 text-white border-l-4 border-pink-500">
            <i class="fas fa-users w-5 text-center"></i> <span>Personal</span>
        </a>
        
        <a href="/asistencia" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all">
            <i class="fas fa-clock w-5 text-center"></i> <span>Asistencia</span>
        </a>

        <a href="/sanciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all">
            <i class="fas fa-gavel w-5 text-center"></i> <span>Sanciones/Memos</span>
        </a>

    </nav>

    <div class="p-4 border-t border-gray-700 bg-gray-800">
        <a href="/api/admin/logout.php" class="text-red-400 text-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>
</aside>