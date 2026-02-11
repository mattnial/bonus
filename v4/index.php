<?php
session_start();
if (!isset($_SESSION['staff_id'])) { header('Location: login.html'); exit; }

include 'includes/header.php';
?>

<div class="flex flex-1 overflow-hidden h-[calc(100vh-64px)]">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto bg-gray-50 p-6 relative w-full" id="mainContent">
        
        <div id="view-home" class="view-section animate-fade-in">
            <?php include 'includes/views/inicio.php'; ?>
        </div>

        <div id="view-tickets" class="view-section hidden animate-fade-in">
            <?php include 'includes/views/tickets.php'; ?>
        </div>

        <div id="view-ventas" class="view-section hidden animate-fade-in">
            <?php include 'includes/views/ventas.php'; ?>
        </div>

        <div id="view-billing" class="view-section hidden animate-fade-in">
            <?php include 'includes/views/cobros.php'; ?>
        </div>

        <div id="view-rrhh" class="view-section hidden animate-fade-in">
            <?php include 'includes/views/rrhh.php'; ?>
        </div>

        <div id="view-reports" class="view-section hidden animate-fade-in">
            <?php include 'includes/views/reportes.php'; ?>
        </div>
        <div id="view-contratos" class="hidden">
            <?php include 'includes/views/contratos.php'; ?>
         </div>
        <div id="view-config" class="view-section hidden animate-fade-in">
            <?php include 'includes/views/configuracion.php'; ?>
        </div>

    </main>
</div>

<div id="profileModal" class="fixed inset-0 bg-black/60 hidden z-[200] flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-sm text-center animate-fade-in-up">
        <div class="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-4 overflow-hidden border-4 border-white shadow-lg">
            <img src="assets/img/default_admin.png" class="w-full h-full object-cover">
        </div>
        <h3 class="font-bold text-xl text-gray-800">Mi Cuenta</h3>
        <p class="text-sm text-gray-500 mb-6">Gestiona tu sesión</p>
        <button onclick="logout()" class="w-full bg-red-50 text-red-600 border border-red-100 py-3 rounded-xl font-bold hover:bg-red-600 hover:text-white transition mb-3">
            <i class="fas fa-power-off mr-2"></i> Cerrar Sesión
        </button>
        <button onclick="document.getElementById('profileModal').classList.add('hidden')" class="text-gray-400 text-sm font-bold hover:text-gray-600">Cancelar</button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>