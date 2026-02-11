<div class="p-6">
    <h2 class="text-2xl font-bold text-gray-800">Cortes y Suspensiones</h2>
    <div id="cortes-view-container">
        Cargando módulo de cortes...
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i = setInterval(() => {
            if (typeof window.loadScript === 'function') {
                clearInterval(i);
                window.loadScript('assets/js/widgets/cortes.js').then(() => {
                    if(typeof window.renderCortesView === 'function') {
                        window.renderCortesView('cortes_pendientes');
                    }
                });
            }
        }, 100);
    });
</script>
