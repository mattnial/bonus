<?php
// ARCHIVO: api/auth/login.php

// 1. Configurar cookie para TODO el dominio (Vital para que inicio.php la lea)
session_set_cookie_params(0, '/'); 
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

try {
    if (empty($data->email) || empty($data->password)) {
        throw new Exception("Datos incompletos");
    }

    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    $q = "SELECT id, name, email, password_hash, role, avatar_url, is_active, assigned_dashboard FROM staff WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($q);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data->password, $user['password_hash'])) {
        
        if($user['is_active'] == 0) throw new Exception("Usuario inactivo");

        // --- CORRECCIÓN AQUÍ: Guardamos con TODOS los nombres posibles ---
        $_SESSION['user_id'] = $user['id'];      // Para los archivos nuevos
        $_SESSION['staff_id'] = $user['id'];     // Para index.php (LEGACY)
        $_SESSION['staff_name'] = $user['name']; // Para el saludo de inicio.php
        $_SESSION['role'] = $user['role'];
        $_SESSION['assigned_dashboard'] = $user['assigned_dashboard'];

        // Limpiar contraseña antes de enviar
        unset($user['password_hash']);

        echo json_encode([
            "success" => true,
            "user" => $user
        ]);

    } else {
        throw new Exception("Credenciales incorrectas");
    }

} catch (Exception $e) {
    http_response_code(200); 
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>