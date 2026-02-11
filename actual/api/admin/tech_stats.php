<?php
// ARCHIVO: api/admin/tech_stats.php
// EXCLUSIVO PARA: Panel Técnico
ob_start();
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

try {
    $user_id = $_SESSION['user_id'] ?? 0;
    
    if (!$user_id) {
        throw new Exception("No autorizado");
    }

    $database = new Database();
    $db = method_exists($database, 'getConnection') ? $database->getConnection() : $database->getCrmDB();

    // 1. Mis Pendientes (Asignados a mí, que NO estén cerrados ni resueltos)
    // Esto arreglará el "0" si tienes tickets en estado 'ABIERTO', 'EN_PROCESO', etc.
    $sqlPending = "SELECT COUNT(*) FROM tickets 
                   WHERE assigned_to_staff_id = :uid 
                   AND status != 'CERRADO' 
                   AND status != 'RESUELTO'";
    $stmt1 = $db->prepare($sqlPending);
    $stmt1->execute([':uid' => $user_id]);
    $my_tickets = $stmt1->fetchColumn();

    // 2. Mis Resueltos (Contador de productividad: Resueltos o Cerrados)
    $sqlResolved = "SELECT COUNT(*) FROM tickets 
                    WHERE assigned_to_staff_id = :uid 
                    AND (status = 'RESUELTO' OR status = 'CERRADO')";
    $stmt2 = $db->prepare($sqlResolved);
    $stmt2->execute([':uid' => $user_id]);
    $my_resolved = $stmt2->fetchColumn();

    // 3. Mi Inventario
    $my_inventory = 0;
    try {
        // Verifica si tienes tabla de inventario, si no, devuelve 0 sin error
        $stmt3 = $db->prepare("SELECT COUNT(*) FROM inventory WHERE assigned_to = :uid");
        $stmt3->execute([':uid' => $user_id]);
        $my_inventory = $stmt3->fetchColumn();
    } catch (Exception $e) { $my_inventory = 0; }

    ob_clean();
    echo json_encode([
        "my_tickets"   => $my_tickets,
        "my_resolved"  => $my_resolved,
        "my_inventory" => $my_inventory
    ]);

} catch (Exception $e) {
    ob_clean();
    // En caso de error devolvemos ceros para no romper el dashboard
    echo json_encode([
        "my_tickets" => 0, 
        "my_resolved" => 0, 
        "my_inventory" => 0
    ]);
}
?>