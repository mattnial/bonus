<aside class="w-64 bg-blue-900 text-white flex flex-col shadow-xl transition-all duration-300" id="sidebar">
    <div class="h-16 flex items-center justify-center border-b border-blue-800">
        <h2 class="text-2xl font-bold tracking-wider">VILCANET</h2>
    </div>

    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        
        <a href="?route=inicio" 
           class="flex items-center px-4 py-2.5 <?php echo $vista=='inicio' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition mb-2">
            <i class="fas fa-home w-6"></i> <span class="font-medium">Inicio</span>
        </a>

        <p class="px-4 text-[10px] font-bold text-blue-300 uppercase mt-2 mb-1">Técnica</p>
        
        <a href="?route=tickets" 
           class="flex items-center px-4 py-2 <?php echo $vista=='tickets' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-ticket-alt w-6"></i> <span>Tickets Globales</span>
        </a>
        
        <a href="?route=instalaciones" 
           class="flex items-center px-4 py-2 <?php echo $vista=='instalaciones' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-tools w-6"></i> <span>Instalaciones (OT)</span>
        </a>

        <p class="px-4 text-[10px] font-bold text-blue-300 uppercase mt-4 mb-1">Administración</p>

        <a href="?route=contratos" 
           class="flex items-center px-4 py-2 <?php echo $vista=='contratos' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-file-signature w-6"></i> <span>Ventas y Contratos</span>
        </a>

        <a href="?route=cobros" 
           class="flex items-center px-4 py-2 <?php echo $vista=='cobros' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-hand-holding-usd w-6"></i> <span>Cobranzas</span>
        </a>

        <a href="?route=convenios" 
           class="flex items-center px-4 py-2 <?php echo $vista=='convenios' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-handshake w-6"></i> <span>Convenios de Pago</span>
        </a>

        <a href="?route=rrhh" 
           class="flex items-center px-4 py-2 <?php echo $vista=='rrhh' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-users-cog w-6"></i> <span>Talento Humano</span>
        </a>

        <p class="px-4 text-[10px] font-bold text-blue-300 uppercase mt-4 mb-1">Sistema</p>

        <a href="?route=reportes" 
           class="flex items-center px-4 py-2 <?php echo $vista=='reportes' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-file-download w-6"></i> <span>Reportes</span>
        </a>

        <a href="?route=configuracion" 
           class="flex items-center px-4 py-2 <?php echo $vista=='configuracion' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?> rounded-lg transition">
            <i class="fas fa-cogs w-6"></i> <span>Configuración / Importar</span>
        </a>

    </nav>

    <div class="p-4 border-t border-blue-800">
        <a href="api/admin/logout.php" onclick="localStorage.removeItem('vilcanet_staff');" class="flex items-center text-gray-300 hover:text-white w-full transition-colors px-2">
            <i class="fas fa-sign-out-alt w-6"></i> <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>