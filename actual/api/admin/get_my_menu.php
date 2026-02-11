<?php
// ARCHIVO: api/admin/get_my_menu.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

$staff_id = $_GET['id'] ?? 0;

try {
    $db = (new Database())->getConnection();
    
    $stmt = $db->prepare("SELECT role FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $role = strtoupper($stmt->fetchColumn());

    // SI ES ADMIN: Ve todo el catálogo
    if (in_array($role, ['SUPERADMIN', 'ADMIN', 'GERENCIA'])) {
        $sql = "SELECT * FROM app_menu_items ORDER BY sort_order ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
    } else {
        // SI ES CUALQUIER OTRO ROL: Ve lo que le asignaste manualmente
        // Esto permite que a un 'Ventas' le abras el menú 'Técnico' si quieres.
        $sql = "SELECT m.* FROM app_menu_items m
                JOIN staff_menu_access a ON m.id = a.menu_item_id
                WHERE a.staff_id = ?
                ORDER BY m.sort_order ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$staff_id]);
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { echo json_encode([]); }