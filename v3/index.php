<?php
// v3/index.php
// 1. Configuración de Sesión
session_name('VILCANET_V3');
session_set_cookie_params(0, '/'); 
session_start();

// 2. Configuración BD
require_once 'config/database.php';

// 3. Router Simple
$module = $_GET['module'] ?? 'Dashboard'; 
$view   = $_GET['view']   ?? 'home';      

// 4. Autenticación (DESACTIVADA PHP TEMPORALMENTE - USAMOS JS/LOCALSTORAGE)
// if (!isset($_SESSION['staff_id'])) {
//    header('Location: login.html'); 
//    exit;
// }

// 4. Estructura HTML Base
include 'includes/header.php'; // Crearemos esto luego
?>

<div class="flex flex-1 overflow-hidden h-[calc(100vh-64px)]">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; // Crearemos esto luego ?>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6 relative w-full" id="mainContent">
        <?php
        $modulePath = "modules/$module/views/$view.php";
        
        if (file_exists($modulePath)) {
            include $modulePath;
        } else {
            echo "<div class='p-4 text-red-500'>Error: Módulo o vista no encontrada ($modulePath)</div>";
        }
        ?>
    </main>
</div>

<?php include 'includes/footer.php'; // Crearemos esto luego ?>
