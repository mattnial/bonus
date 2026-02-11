<?php
// ARCHIVO: api/admin/update_settings_single.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

// Validamos que vengan los datos necesarios
if(empty($data->doc_type) || empty($data->branch) || !isset($data->current_value)) {
    echo json_encode(["success" => false, "message" => "Faltan datos (Tipo, Sucursal o Valor)"]);
    exit;
}

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // Actualizamos o Insertamos si no existe (basado en la clave única doc_type + branch)
    $sql = "INSERT INTO document_sequences (doc_type, branch, current_value, prefix) 
            VALUES (:type, :branch, :val, :prefix) 
            ON DUPLICATE KEY UPDATE current_value = :val, prefix = :prefix";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':type'   => $data->doc_type,
        ':branch' => $data->branch,
        ':val'    => $data->current_value,
        ':prefix' => $data->prefix ?? '' 
    ]);

    echo json_encode(["success" => true, "message" => "Secuencia actualizada"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error DB: " . $e->getMessage()]);
}
?>