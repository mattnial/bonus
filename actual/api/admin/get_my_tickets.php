<?php
// ARCHIVO: api/admin/get_my_tickets.php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include_once '../../config/database.php';
session_start();

if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $database = new Database();
    $db = method_exists($database, 'getConnection') ? $database->getConnection() : $database->getCrmDB();

    // Consulta con JOIN para traer el nombre del cliente
    // Ordenamos por: 1. Urgencia, 2. Más antiguos primero
    $query = "SELECT t.id, t.subject, t.priority, t.status, t.created_at, 
                     c.name as client_name, c.address 
              FROM tickets t
              LEFT JOIN clients c ON t.client_id = c.id
              WHERE t.assigned_to_staff_id = ? 
              AND t.status != 'CERRADO'
              ORDER BY 
                CASE WHEN t.priority = 'URGENTE' THEN 1 
                     WHEN t.priority = 'ALTA' THEN 2 
                     ELSE 3 END ASC,
                t.created_at ASC";

    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode($tickets);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>