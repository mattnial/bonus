<?php
// ARCHIVO: api/admin/menu_manager.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

try {
    $db = (new Database())->getCrmDB();
    $action = $_GET['action'] ?? '';

    if ($action === 'list_all_items') {
        // CAMBIO: Aseguramos que use app_menu_items
        $query = "SELECT * FROM app_menu_items ORDER BY sort_order ASC"; 
        $stmt = $db->prepare($query);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
    } elseif ($action === 'get_user_permissions') {
        // Traer solo los IDs de lo que el usuario YA tiene asignado
        $staff_id = $_GET['staff_id'] ?? 0;
        $stmt = $db->prepare("SELECT menu_item_id FROM staff_menu_access WHERE staff_id = ?");
        $stmt->execute([$staff_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));

    } elseif ($action === 'save_permissions') {
        // Guardar la nueva selección
        $data = json_decode(file_get_contents("php://input"), true);
        $staff_id = $data['staff_id'];
        $menu_ids = $data['menu_ids'];

        $db->beginTransaction();
        // 1. Borrar lo anterior
        $db->prepare("DELETE FROM staff_menu_access WHERE staff_id = ?")->execute([$staff_id]);
        // 2. Insertar lo nuevo
        $ins = $db->prepare("INSERT INTO staff_menu_access (staff_id, menu_item_id) VALUES (?, ?)");
        foreach ($menu_ids as $mid) { $ins->execute([$staff_id, $mid]); }
        $db->commit();
        
        echo json_encode(["success" => true]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}