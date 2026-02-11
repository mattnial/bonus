<?php
// ARCHIVO: api/admin/save_inventory.php
header("Access-Control-Allow-Origin: *");
// Nota: Content-Type ya no es json, es automático por el FormData

include_once '../config/database.php';

try {
    $db = (new Database())->getConnection();

    // 1. Recibir Datos (vienen por $_POST ahora)
    $type = $_POST['type'];
    $brand = strtoupper($_POST['brand']);
    $model = strtoupper($_POST['model']);
    $location = $_POST['location']; // Nueva Bodega
    $qty = intval($_POST['quantity']);
    $cost = floatval($_POST['cost']);
    $admin_id = $_POST['admin_id'];
    
    // Lógica de Serie
    $serial = $_POST['serial'];
    if(in_array($type, ['ANTENA', 'MATERIAL', 'BOBINA'])) { 
        // Si es material, y no mandan serie, generar una
        if(empty($serial)) $serial = 'MAT-' . time() . rand(100,999);
    } else {
        // Si es equipo, serie obligatoria y única
        if(empty($serial)) throw new Exception("Serie obligatoria para equipos.");
        $stmt = $db->prepare("SELECT id FROM inventory WHERE serial_number = ? AND status != 'BAJA'");
        $stmt->execute([$serial]);
        if($stmt->fetch()) throw new Exception("Esta serie ya existe.");
    }

    // 2. Manejo de IMAGEN (Foto del Item)
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = "item_" . time() . "_" . rand(100,999) . "." . $ext;
        $target = "../../uploads/inventory/" . $filename;
        
        // Crear carpeta si no existe
        if (!file_exists('../../uploads/inventory/')) {
            mkdir('../../uploads/inventory/', 0777, true);
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = $filename;
        }
    }

    // 3. Insertar
    $sql = "INSERT INTO inventory (type, cost, brand, model, serial_number, mac_address, status, quantity, location, image_url, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'BODEGA', ?, ?, ?, NOW())";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        $type, $cost, $brand, $model, $serial, 
        $_POST['mac'] ?? '', 
        $qty, $location, $imagePath
    ]);

    // Log
    $id = $db->lastInsertId();
    $db->prepare("INSERT INTO inventory_movements (inventory_id, admin_id, type, notes) VALUES (?, ?, 'ENTRADA', ?)")
       ->execute([$id, $admin_id, "Ingreso a $location"]);

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>