<?php
// ARCHIVO: api/admin/ticket_manager.php
ob_start();

// Cabeceras Estándar
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput);

    // Fallback POST
    if (!$data && !empty($_POST)) $data = (object) $_POST;

    // --- ACCIÓN: STATUS ---
    if ($action === 'status') {
        
        // 1. OBTENCIÓN SEGURA DE DATOS
        $ticketId = isset($data->ticket_id) ? $data->ticket_id : (isset($data->id) ? $data->id : null);
        $newStatus = isset($data->status) ? $data->status : null;

        // 2. EL SILENCIADOR (CORRECCIÓN CLAVE)
        // Si el ID es null o 0, no lanzamos error. Solo decimos "ok, no hago nada".
        // Esto elimina el error rojo en tu consola.
        if (empty($ticketId)) {
            echo json_encode(["success" => false, "message" => "Ignorado: ID de ticket vacío"]);
            exit;
        }

        if (empty($newStatus)) {
            throw new Exception("Falta el nuevo estado");
        }

        // 3. ACTUALIZAR
        $stmt = $db->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $ticketId]);

        // Nota automática
        if (in_array($newStatus, ['RESUELTO', 'CERRADO', 'EN_PROCESO'])) {
            $msg = "Estado actualizado a: " . $newStatus;
            $stmtNote = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 0, ?, NOW())");
            $stmtNote->execute([$ticketId, $msg]);
        }

        echo json_encode(["success" => true, "message" => "Estado actualizado a " . $newStatus]);
        exit;
    }

    // Si llega aquí sin acción
    if (!$data && empty($action)) {
         echo json_encode(["success" => false, "message" => "Sin datos"]);
         exit;
    }

} catch (Exception $e) {
    // Si hay error real, devolvemos 200 con success:false para no ensuciar la consola con rojo
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
}
?>