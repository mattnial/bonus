<?php
// ARCHIVO: public_html/api/admin/get_client_history.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';

$clientId = isset($_GET['client_id']) ? $_GET['client_id'] : null;

if(!$clientId) {
    echo json_encode([]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Consultamos el historial ordenado por fecha descendente
    $sql = "SELECT type, notes, promise_date, created_at 
            FROM client_interactions 
            WHERE client_id = :cid 
            ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':cid' => $clientId]);
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($history);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}