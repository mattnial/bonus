<div class="p-6">
    <h2 class="text-2xl font-bold text-gray-800">Reactivaciones</h2>
    <div id="reactivaciones-view-container">
        Cargando módulo...
    </div>
</div>
<div class="p-6">
    <h2 class="text-2xl font-bold text-gray-800">Reactivaciones</h2>
    <div id="cortes-view-container">Cargando...</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i = setInterval(() => {
            if (typeof window.loadScript === 'function') {
                clearInterval(i);
                window.loadScript('assets/js/widgets/cortes.js').then(() => {
                     // Reutilizamos el widget de cortes que maneja ambas vistas
                    if(typeof window.renderCortesView === 'function') {
                        window.renderCortesView('reactivaciones');
                    }
                });
            }
        }, 100);
    });
</script>
