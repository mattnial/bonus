<?php
// ARCHIVO: api/admin/get_clients_dropdown.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getBonusDB(); // Usamos la base de clientes (Bonus)

    // Seleccionamos solo ID, Nombre y Cédula para que sea rápido (ligero)
    // Limitamos a clientes ACTIVO o SUSPENDIDO (ignoramos retirados para no llenar la lista de basura)
    $query = "SELECT id, name, cedula FROM clients 
              WHERE service_status IN ('ACTIVO', 'SUSPENDIDO') 
              ORDER BY name ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clients);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>