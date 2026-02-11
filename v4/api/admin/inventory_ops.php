<?php
// ARCHIVO: api/admin/inventory_ops.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));
$action = $_GET['action'] ?? '';

try {
    $db = (new Database())->getConnection();
    
    // --- DESPACHAR (ASIGNAR A TÉCNICO) ---
    if ($action === 'dispatch') {
        if (empty($data->items) || empty($data->tech_id)) throw new Exception("Datos incompletos");

        foreach ($data->items as $item) {
            $id = $item->id;
            $qtyToMove = intval($item->qty); // Cuanto despachamos

            // 1. Obtener info actual
            $stmt = $db->prepare("SELECT * FROM inventory WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($current['quantity'] < $qtyToMove) {
                throw new Exception("No hay suficiente stock de {$current['model']}. Disponible: {$current['quantity']}");
            }

            // 2. Lógica de Descuento
            if ($current['type'] == 'MATERIAL' || $current['type'] == 'ANTENA') {
                // A. Restar de Bodega
                $newQty = $current['quantity'] - $qtyToMove;
                if ($newQty > 0) {
                    $db->prepare("UPDATE inventory SET quantity = ? WHERE id = ?")->execute([$newQty, $id]);
                } else {
                    // Si se acaba, marcamos como agotado/historial o lo borramos?
                    // Mejor lo dejamos en 0 o cambiamos estado. Aquí lo dejaremos en 0 BODEGA.
                    $db->prepare("UPDATE inventory SET quantity = 0 WHERE id = ?")->execute([$id]);
                }

                // B. Crear nuevo lote para el Técnico
                $newSerial = $current['serial_number'] . '-T' . $data->tech_id . '-' . time(); // ID único hijo
                $sqlInsert = "INSERT INTO inventory (type, brand, model, serial_number, status, quantity, assigned_staff_id, cost)
                              VALUES (?, ?, ?, ?, 'ASIGNADO_TECNICO', ?, ?, ?)";
                $db->prepare($sqlInsert)->execute([
                    $current['type'], $current['brand'], $current['model'], $newSerial, $qtyToMove, $data->tech_id, $current['cost']
                ]);
                $newId = $db->lastInsertId();

                // Log
                $db->prepare("INSERT INTO inventory_movements (inventory_id, admin_id, tech_id, type, notes) VALUES (?, ?, ?, 'ENTREGA_TECNICO', ?)")
                   ->execute([$id, $data->admin_id, $data->tech_id, "Despacho parcial: $qtyToMove unid."]);

            } else {
                // EQUIPOS (Se mueve la unidad entera)
                $db->prepare("UPDATE inventory SET status = 'ASIGNADO_TECNICO', assigned_staff_id = ? WHERE id = ?")
                   ->execute([$data->tech_id, $id]);
                
                $db->prepare("INSERT INTO inventory_movements (inventory_id, admin_id, tech_id, type, notes) VALUES (?, ?, ?, 'ENTREGA_TECNICO', ?)")
                   ->execute([$id, $data->admin_id, $data->tech_id, $data->note]);
            }
        }
        echo json_encode(["success" => true]);
    }
    
    // --- BAJA ---
    elseif ($action === 'delete') {
        $db->prepare("UPDATE inventory SET status = 'BAJA', quantity = 0 WHERE id = ?")->execute([$data->id]);
        echo json_encode(["success" => true]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>