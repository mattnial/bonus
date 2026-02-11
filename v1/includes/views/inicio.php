<div class="p-6 animate-fade-in">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Hola, <?php echo $_SESSION['staff_name']; ?> 👋</h2>
            <p class="text-gray-500" id="dashboard-subtitle">Cargando tu entorno de trabajo...</p>
        </div>
        <div id="dashboard-actions"></div>
    </div>

    <div id="dashboard-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="col-span-full py-12 flex justify-center items-center opacity-50">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-500 font-medium">Iniciando Dashboard Inteligente...</span>
        </div>
    </div>

    <div id="main-dashboard-content"></div>
</div>


<script src="assets/js/modules/dashboard_loader.js?v=<?php echo time(); ?>"></script>
