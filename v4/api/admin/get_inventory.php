<?php
// ARCHIVO: api/admin/get_inventory.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();
    // Intenta conectar a la de Bonus si es donde está el inventario, sino usa la por defecto
    if(method_exists($database, 'getBonusDB')) {
        $db = $database->getBonusDB();
    }

    $assigned_to = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';

    // --- CORRECCIÓN CRÍTICA ---
    // Según tu XML, la columna se llama 'assigned_staff_id'
    $colName = 'assigned_staff_id'; 

    $sql = "SELECT i.* FROM inventory i 
            WHERE 1=1";

    if ($assigned_to) {
        $sql .= " AND i.$colName = :uid";
    }

    // Ordenar por nombre/modelo
    $sql .= " ORDER BY i.model ASC, i.brand ASC";

    $stmt = $db->prepare($sql);
    
    if ($assigned_to) {
        $stmt->bindParam(":uid", $assigned_to);
    }

    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>