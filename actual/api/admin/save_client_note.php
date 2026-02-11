<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. Carga Config
if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

try {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->client_id)) throw new Exception("Falta ID Cliente");

    $database = new Database();
    // Usa tu conexión preferida
    $db = method_exists($database, 'getBonusDB') ? $database->getBonusDB() : $database->getConnection();

    // 2. Actualizar Nota
    $stmt = $db->prepare("UPDATE clients SET notes = ? WHERE id = ?");
    $stmt->execute([$data->note, $data->client_id]);

    ob_clean();
    echo json_encode(["success" => true, "message" => "Nota guardada"]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>