<?php
// ARCHIVO: api/admin/get_all_tickets.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();
    
    // Necesitamos conexión a la DB de Clientes (Bonus) para sacar los nombres
    $dbBonus = method_exists($database, 'getBonusDB') ? $database->getBonusDB() : $database->getConnection();

    // Filtros
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $assigned_to = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';

    $sql = "SELECT t.*, s.name as staff_name 
            FROM tickets t 
            LEFT JOIN staff s ON t.assigned_to_staff_id = s.id 
            WHERE 1=1";

    if ($status) {
        $sql .= " AND t.status = :status";
    } else {
        $sql .= " AND t.status != 'CERRADO'";
    }

    if ($assigned_to) {
        $sql .= " AND t.assigned_to_staff_id = :assigned_to";
    }

    $sql .= " ORDER BY t.created_at DESC LIMIT 50";

    $stmt = $db->prepare($sql);
    
    if ($status) $stmt->bindParam(":status", $status);
    if ($assigned_to) $stmt->bindParam(":assigned_to", $assigned_to);

    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- AQUÍ ESTÁ LA MAGIA QUE FALTABA ---
    // Recolectamos todos los IDs de clientes de la lista de tickets
    if (count($tickets) > 0) {
        $clientIds = [];
        foreach ($tickets as $t) {
            if (!empty($t['client_id'])) {
                $clientIds[] = $t['client_id'];
            }
        }
        
        // Si hay clientes, buscamos sus nombres en la otra base de datos
        $clientNames = [];
        $clientAddresses = []; // También recuperamos direcciones por si acaso
        
        if (!empty($clientIds)) {
            $clientIds = array_unique($clientIds);
            $idsStr = implode(',', array_map('intval', $clientIds));
            
            // Consultamos la DB de Bonus
            $qC = "SELECT id, name, address FROM clients WHERE id IN ($idsStr)";
            $stmtC = $dbBonus->query($qC);
            
            while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                $clientNames[$row['id']] = $row['name'];
                $clientAddresses[$row['id']] = $row['address'];
            }
        }

        // Pegamos los nombres en la lista de tickets
        foreach ($tickets as &$ticket) {
            $cid = $ticket['client_id'];
            // Asignamos nombre o un texto por defecto
            $ticket['client_name'] = isset($clientNames[$cid]) ? $clientNames[$cid] : 'Cliente Desconocido';
            // Asignamos dirección si el ticket no la tiene guardada
            if (empty($ticket['client_address']) && isset($clientAddresses[$cid])) {
                $ticket['client_address'] = $clientAddresses[$cid];
            }
        }
    }
    // ---------------------------------------

    ob_clean();
    echo json_encode($tickets);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["error" => $e->getMessage()]);
}
?>