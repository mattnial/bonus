<div class="p-6">
    <h2 class="text-2xl font-bold text-gray-800">Convenios y Promesas de Pago</h2>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-6">
        <table class="w-full text-sm text-left">
            <thead class="bg-green-50 text-green-700 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Cliente</th>
                    <th class="px-6 py-3 text-center">Fecha Promesa</th>
                    <th class="px-6 py-3 font-bold text-center">Deuda</th>
                    <th class="px-6 py-3">Contacto</th>
                    <th class="px-6 py-3">Última Nota</th>
                    <th class="px-6 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody id="debtors-list" class="divide-y divide-gray-200">
                <tr><td colspan="6" class="text-center py-8 text-gray-400">Cargando promesas...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const i = setInterval(() => {
            if (typeof window.loadScript === 'function') {
                clearInterval(i);
                window.loadScript('assets/js/widgets/cobranzas.js').then(() => {
                    if(typeof window.initCobranzas === 'function') {
                        window.initCobranzas('promises');
                    }
                });
            }
        }, 100);
    });
</script>
