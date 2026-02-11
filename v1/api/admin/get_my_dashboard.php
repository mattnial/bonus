<?php
// ARCHIVO: api/admin/get_my_dashboard.php
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

try {
    if (!isset($_SESSION['user_id'])) throw new Exception("Sesión no iniciada");

    if (!file_exists('../config/database.php')) throw new Exception("Falta database.php");
    include_once '../config/database.php';
    
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    $user_id = $_SESSION['user_id'];

    // 1. OBTENEMOS DATOS (Incluyendo la nueva columna 'assigned_dashboard')
    $query = "SELECT * FROM staff WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) throw new Exception("Usuario no encontrado");

    // Limpieza de seguridad
    unset($user['password']);
    unset($user['password_hash']);
    unset($user['token']);

    // 2. LÓGICA DINÁMICA DE DASHBOARD
    // Leemos la columna de la DB. Si está vacía o es null, usamos 'default'.
    $dashboard_file = !empty($user['assigned_dashboard']) ? $user['assigned_dashboard'] : 'default';

    // Opcional: Validación extra de seguridad para que no carguen archivos raros
    // Solo permitimos caracteres alfanuméricos y guiones bajos
    $dashboard_file = preg_replace("/[^a-zA-Z0-9_]/", "", $dashboard_file);

    // 3. Respuesta Final
    $response = [
        "success" => true,
        "dashboard" => $dashboard_file, // Ej: "gerencia" -> cargará gerencia.js
        "user" => $user,
        "stats" => [
            "info" => "Panel: " . ucfirst($dashboard_file)
        ]
    ];

    ob_end_clean();
    echo json_encode($response);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>