<?php
// api/admin/get_cut_candidates.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

try {
    $db = (new Database())->getConnection();
    // Simulating logic: Clients with debts > 2 months OR manual cut request
    // This is a basic stub based on standard requirements. Adjust query as needed.
    $sql = "SELECT id, name, total_debt, months_owed, cut_request 
            FROM clients 
            WHERE status = 'ACTIVO' 
            AND (months_owed >= 2 OR cut_request = 'pendiente_corte')
            LIMIT 50";
            
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
