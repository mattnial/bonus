<?php
// ARCHIVO: api/admin/config_sequences.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

$database = new Database();
$db = $database->getCrmDB();

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    // 1. LISTAR TODAS LAS SECUENCIAS
    if ($action === 'list') {
        $stmt = $db->query("SELECT * FROM document_sequences ORDER BY doc_type, branch");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    // 2. GUARDAR/ACTUALIZAR UNA SECUENCIA
    elseif ($action === 'save') {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->doc_type) || empty($data->branch)) throw new Exception("Datos incompletos");

        $sql = "INSERT INTO document_sequences (doc_type, branch, current_value, prefix) 
                VALUES (:type, :branch, :val, :pre)
                ON DUPLICATE KEY UPDATE current_value = :val2, prefix = :pre2";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':type' => $data->doc_type,
            ':branch' => $data->branch,
            ':val' => $data->current_value,
            ':pre' => $data->prefix,
            ':val2' => $data->current_value,
            ':pre2' => $data->prefix
        ]);
        
        echo json_encode(["success" => true, "message" => "Secuencia actualizada"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>