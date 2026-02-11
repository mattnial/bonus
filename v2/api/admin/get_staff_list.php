<?php
// ARCHIVO: api/admin/get_staff_list.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

// Recibimos la categoría del ticket (ej: INSTALACION_FO)
$category = isset($_GET['category']) ? strtoupper($_GET['category']) : '';

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // 1. OBTENER STAFF ACTIVO (Corrección: columna is_active)
    // Según tu XML: tabla 'staff', columna 'is_active' (1 = activo)
    $query = "SELECT id, name, email, role FROM staff WHERE is_active = 1 ORDER BY name ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $allStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. DEFINIR CATEGORÍAS TÉCNICAS
    $technicalTypes = [
        'INSPECCION', 'INSTALACION_FO', 'INSTALACION_RE', 
        'SOPORTE_FO', 'SOPORTE_RE', 'MIGRACION', 
        'RETIRO', 'CAMBIO_DOM_FO', 'CAMBIO_DOM_RE'
    ];

    // 3. FILTRADO
    $filtered = [];
    $isTechnicalJob = in_array($category, $technicalTypes);

    foreach($allStaff as $user) {
        // Convertimos a mayúsculas para coincidir con tu XML (TECNICO, VENTAS...)
        $role = isset($user['role']) ? strtoupper($user['role']) : '';
        
        // REGLA: Si es trabajo TÉCNICO
        if ($isTechnicalJob) {
            // Solo mostramos a los que tienen rol 'TECNICO'
            // (Añadimos 'GERENCIA' por si acaso necesiten supervisar)
            if ($role === 'TECNICO' || $role === 'GERENCIA') {
                $filtered[] = $user;
            }
        } 
        // REGLA: Si es ADMINISTRATIVO
        else {
            // Mostramos a TODOS
            $filtered[] = $user;
        }
    }

    // Si no hay filtro (ej: la categoría llegó vacía), mostramos todos
    if (empty($category)) {
        $filtered = $allStaff;
    }

    // Si después de filtrar no queda nadie, devolvemos array vacío
    // (El JS se encargará de mostrar el mensaje de error)
    
    ob_clean();
    echo json_encode($filtered);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([]);
}
?>