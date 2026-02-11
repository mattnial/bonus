<?php
// ARCHIVO: api/sync/get_pending_actions.php
// Tu PC local consultará esto. ¡Protégelo si puedes con una clave!
header("Content-Type: application/json");
include_once '../../api/config/database.php';

$action = $_GET['action'] ?? ''; // 'get' o 'confirm'
$db = (new Database())->getConnection();

if ($action === 'get') {
    // 1. Obtener lista de tareas pendientes
    $sql = "SELECT id, name, ip_address, cut_request FROM clients 
            WHERE cut_request IN ('pendiente_corte', 'pendiente_activacion')";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} elseif ($action === 'confirm') {
    // 2. El PC local confirma que ya hizo el trabajo
    $id = $_GET['id'];
    $type = $_GET['type']; // 'corte' o 'activacion'
    
    if ($type === 'corte') {
        $sql = "UPDATE clients SET cut_request = 'ejecutado', service_status = 'suspendido', cut_date = NOW() WHERE id = ?";
    } else {
        $sql = "UPDATE clients SET cut_request = 'no', service_status = 'activo', cut_date = NULL WHERE id = ?";
    }
    $db->prepare($sql)->execute([$id]);
    echo json_encode(['success'=>true]);
}
?>