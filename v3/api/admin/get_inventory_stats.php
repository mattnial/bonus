<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // Contar solo lo que está en BODEGA
    $sql = "SELECT type, COUNT(*) as count FROM inventory WHERE status = 'BODEGA' GROUP BY type";
    $stmt = $db->query($sql);
    $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Ej: ['ONT'=>50, 'ROUTER'=>10]
    
    // Definir Mínimos (Alertas)
    $minimos = ['ONT' => 10, 'ROUTER' => 5, 'MESH' => 3, 'ANTENA' => 5, 'MATERIAL' => 5];
    $alerts = [];

    foreach ($minimos as $type => $min) {
        $qty = $counts[$type] ?? 0;
        if ($qty <= $min) {
            $alerts[] = "⚠️ Quedan solo $qty $type (Mínimo $min)";
        }
    }

    echo json_encode(['counts' => $counts, 'alerts' => $alerts]);
} catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
?>