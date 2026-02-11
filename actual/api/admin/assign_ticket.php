<?php
// ARCHIVO: api/admin/assign_ticket.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

try {
    // 1. Recibir datos JSON
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->ticket_id) || empty($data->staff_id)) {
        throw new Exception("Faltan datos (ID Ticket o ID Staff)");
    }

    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // 2. Obtener nombre del técnico (para la nota interna)
    $stmtStaff = $db->prepare("SELECT name FROM staff WHERE id = ? LIMIT 1");
    $stmtStaff->execute([$data->staff_id]);
    $staff = $stmtStaff->fetch(PDO::FETCH_ASSOC);
    $staffName = $staff ? $staff['name'] : 'Técnico';

    // 3. Actualizar el ticket (Asignar y cambiar estado a EN_PROCESO)
    $stmt = $db->prepare("UPDATE tickets SET assigned_to_staff_id = ?, status = 'EN_PROCESO' WHERE id = ?");
    $stmt->execute([$data->staff_id, $data->ticket_id]);

    // 4. Agregar nota automática en el chat
    // sender_id = 0 indica que es el Sistema el que escribe la nota
    $note = "Ticket asignado a: " . $staffName;
    $stmtNote = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 0, ?, NOW())");
    $stmtNote->execute([$data->ticket_id, $note]);

    ob_clean();
    echo json_encode(["success" => true, "message" => "Asignado a " . $staffName]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>