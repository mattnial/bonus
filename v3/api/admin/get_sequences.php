<?php
// ARCHIVO: api/admin/get_sequences.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // Ordenamos por Tipo y luego por Sucursal para que se vea ordenado en la tabla
    $query = "SELECT * FROM document_sequences ORDER BY doc_type ASC, branch ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);

} catch (Exception $e) {
    echo json_encode([]);
}
?>