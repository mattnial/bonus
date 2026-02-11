<?php
// ARCHIVO: api/admin/get_tech_stats.php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include_once '../../config/database.php';
session_start();

// Si no hay sesión o no hay ID, error
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$myId = $_SESSION['user_id'];

try {
    $database = new Database();
    $db = method_exists($database, 'getConnection') ? $database->getConnection() : $database->getCrmDB();

    // 1. MIS URGENCIAS (Tickets míos que son ALTA o URGENTE y no están cerrados)
    $sqlUrgent = "SELECT COUNT(*) FROM tickets 
                  WHERE assigned_to_staff_id = ? 
                  AND status != 'CERRADO' 
                  AND priority IN ('ALTA', 'URGENTE')";
    $stmt1 = $db->prepare($sqlUrgent);
    $stmt1->execute([$myId]);
    $myUrgent = $stmt1->fetchColumn();

    // 2. MIS PENDIENTES (Total asignados que sigo trabajando)
    $sqlOpen = "SELECT COUNT(*) FROM tickets 
                WHERE assigned_to_staff_id = ? 
                AND status IN ('ABIERTO', 'EN_PROCESO')";
    $stmt2 = $db->prepare($sqlOpen);
    $stmt2->execute([$myId]);
    $myOpen = $stmt2->fetchColumn();

    // 3. RESUELTOS (Total históricos cerrados por mí)
    // Nota: Si quieres solo los de "HOY", habría que filtrar por fecha, 
    // pero por ahora dejémoslo como "Total Resueltos" para motivar.
    $sqlSolved = "SELECT COUNT(*) FROM tickets 
                  WHERE assigned_to_staff_id = ? 
                  AND status = 'CERRADO'";
    $stmt3 = $db->prepare($sqlSolved);
    $stmt3->execute([$myId]);
    $mySolved = $stmt3->fetchColumn();

    $response = [
        "urgent" => $myUrgent,
        "open"   => $myOpen,
        "solved" => $mySolved
    ];

    ob_clean();
    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>