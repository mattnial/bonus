<?php
// ARCHIVO: api/admin/send_reply.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

try {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->ticket_id) || empty($data->message)) throw new Exception("Mensaje vacío");

    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // INSERTAR RESPUESTA (Como STAFF)
    // IMPORTANTE: Ponemos sender_id = 1 (o el ID del admin logueado si lo tuvieras en sesión)
    $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 1, ?, NOW())");
    $stmt->execute([$data->ticket_id, $data->message]);

    ob_clean();
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>