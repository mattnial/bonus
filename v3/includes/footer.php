<!-- Modales Genéricos compartidos -->
<div id="confirmModal" class="fixed inset-0 bg-black/60 hidden z-[10001] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 text-center max-w-sm w-full animate-fade-in-up">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
            <i class="fas fa-question text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">¿Estás seguro?</h3>
        <p class="text-sm text-gray-500 mb-6" id="confirmMsg">Acción irreversible.</p>
        <div class="flex justify-center gap-3">
            <button id="btnConfirmNo" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">Cancelar</button>
            <button id="btnConfirmYes" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 transition">Confirmar</button>
        </div>
    </div>
</div>

<div id="toast-container" class="fixed top-5 left-5 z-[200005] flex flex-col gap-3 pointer-events-none"></div>

<script>
    const CURRENT_USER_ID = <?php echo $_SESSION['staff_id'] ?? 0; ?>;
    const CURRENT_ROLE = "<?php echo $_SESSION['role'] ?? 'GUEST'; ?>";
</script>

<script src="assets/js/config.js"></script>
<script src="assets/js/core.js"></script>
<script src="assets/js/menu_config.js?v=<?php echo time(); ?>"></script>

<!-- Scripts de Módulos (Se cargarán dinámicamente o aquí si son globales) -->
<!-- Por ahora, limpieza: no cargamos scripts viejos para evitar conflictos.
     Cada módulo debería cargar sus scripts si es necesario, o los migraremos poco a poco. -->

</body>
</html>
