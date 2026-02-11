<?php
// ARCHIVO: api/admin/toggle_service.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

try {
    if (empty($data->client_id) || empty($data->action)) throw new Exception("Datos incompletos");

    $database = new Database();
    $db = $database->getBonusDB(); // Base Bonus
    $db_crm = $database->getCrmDB(); // Para registrar log (opcional)

    // Definir nuevo estado
    $newStatus = ($data->action === 'CORTAR') ? 'CORTADO' : 'ACTIVO';

    // 1. Actualizar Cliente
    $q = "UPDATE clients SET service_status = :status WHERE id = :id";
    $stmt = $db->prepare($q);
    $stmt->bindParam(":status", $newStatus);
    $stmt->bindParam(":id", $data->client_id);
    
    if($stmt->execute()) {
        
        // 2. (Opcional) Registrar en un log o ticket si quieres
        // ...

        ob_clean();
        echo json_encode(["message" => "Servicio actualizado a: " . $newStatus]);
    } else {
        throw new Exception("No se pudo actualizar");
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>