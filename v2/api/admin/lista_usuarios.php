<?php
// ARCHIVO: api/admin/get_staff_list.php

// 1. INICIAR BUFFER (Atrapa cualquier error invisible)
ob_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. SILENCIAR ERRORES VISUALES
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getCrmDB();

    // Validar conexión
    if (!$db) throw new Exception("Error conexión BD");

    // Consulta segura
    // NOTA: Si sale error de "Unknown column is_active", ejecuta el SQL de abajo
    $query = "SELECT id, name, role FROM staff WHERE is_active = 1 ORDER BY name ASC";
    
    $stmt = $db->prepare($query);
    
    if (!$stmt->execute()) {
        throw new Exception("Error SQL: " . implode(" ", $stmt->errorInfo()));
    }
    
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. LIMPIAR Y ENVIAR
    ob_clean();
    echo json_encode($staff);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => "Server Error: " . $e->getMessage()]);
}
?>