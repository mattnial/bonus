<?php
// ARCHIVO: api/admin/get_ticket_messages.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // 1. INFO TICKET + NOMBRE DEL TÉCNICO
    // Usamos 'message' como descripción inicial según tu estructura común
    $stmt = $db->prepare("SELECT t.*, s.name as staff_name 
                          FROM tickets t 
                          LEFT JOIN staff s ON t.assigned_to_staff_id = s.id 
                          WHERE t.id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) throw new Exception("Ticket no encontrado");

    // 2. MENSAJES (Corrección basada en tu XML: sender_type)
    // Ordenamos por ID para que salgan en orden cronológico
    $query = "SELECT r.*, s.name as staff_name 
              FROM ticket_replies r 
              LEFT JOIN staff s ON (r.sender_type = 'STAFF' AND r.sender_id = s.id)
              WHERE r.ticket_id = ? 
              ORDER BY r.id ASC";

    $stmtMsg = $db->prepare($query);
    $stmtMsg->execute([$ticket_id]);
    $messages = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode(["ticket" => $ticket, "messages" => $messages]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["error" => $e->getMessage()]);
}
?>