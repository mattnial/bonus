<?php
header('Content-Type: application/json');
include_once '../config/database.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) { echo json_encode([]); exit; }

try {
    $database = new Database();
    $db = $database->getBonusDB();
    $sql = "SELECT id, name, cedula, service_status FROM clients WHERE (name LIKE ? OR cedula LIKE ?) LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute(["%$q%", "%$q%"]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { echo json_encode([]); }
?>