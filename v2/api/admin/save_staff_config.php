<?php
// ARCHIVO: api/admin/save_staff_config.php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // Recibir datos
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'TECNICO';
    
    // EL NUEVO CAMPO
    $dashboard = $_POST['assigned_dashboard'] ?? 'default';

    if (empty($name) || empty($email)) throw new Exception("Nombre y Email requeridos");

    if (!empty($id)) {
        // --- ACTUALIZAR USUARIO EXISTENTE ---
        $sql = "UPDATE staff SET name=?, email=?, role=?, assigned_dashboard=? WHERE id=?";
        $params = [$name, $email, $role, $dashboard, $id];
        
        // Si enviaron contraseña, la actualizamos también
        if (!empty($pass)) {
            $sql = "UPDATE staff SET name=?, email=?, role=?, assigned_dashboard=? WHERE id=?";
            $params = [$name, $email, $role, $dashboard, $id];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
    } else {
        // --- CREAR NUEVO USUARIO ---
        if (empty($pass)) throw new Exception("La contraseña es obligatoria para nuevos usuarios");
        
        $sql = "INSERT INTO staff (name, email, password_hash, role, assigned_dashboard, is_active) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role, $dashboard]);
    }

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>