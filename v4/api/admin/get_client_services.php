<?php
// ARCHIVO: api/admin/get_client_services.php
// OBJETIVO: Obtener solo datos técnicos (Plan, IP, Sector)
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

// 1. Configuración
if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    if ($id <= 0) throw new Exception("ID inválido");

    $database = new Database();
    // Usamos la base de datos BONUS donde están los planes
    $db = method_exists($database, 'getBonusDB') ? $database->getBonusDB() : $database->getConnection();

    // 2. CONSULTA ESPECIALIZADA
    // Solo traemos lo que necesitamos para la ficha técnica
    $query = "SELECT 
                c.ip_address,
                p.name as plan_name,
                s.name as sector_name
              FROM clients c
              LEFT JOIN plans p ON c.plan_id = p.id
              LEFT JOIN sectors s ON c.sector_id = s.id
              WHERE c.id = :id LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    ob_clean();
    
    if ($data) {
        echo json_encode($data);
    } else {
        // Si no hay datos, enviamos vacíos para no romper el JS
        echo json_encode(["plan_name" => null, "ip_address" => null, "sector_name" => null]);
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["error" => $e->getMessage()]);
}
?>