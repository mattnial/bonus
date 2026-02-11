<?php
// ARCHIVO: api/admin/toggle_service.php
header("Content-Type: application/json");
include_once '../config/database.php';
$data = json_decode(file_get_contents("php://input"));

try {
    $db = (new Database())->getConnection();
    
    if ($data->action === 'cut') {
        // ORDEN DE CORTE
        $sql = "UPDATE clients SET cut_request = 'pendiente_corte', collection_stage = 4 WHERE id = ?";
        $db->prepare($sql)->execute([$data->client_id]);
        echo json_encode(['success'=>true, 'message'=>'En cola de corte...']);

    } elseif ($data->action === 'activate') {
        // ORDEN DE REACTIVACIÓN
        $sql = "UPDATE clients SET cut_request = 'pendiente_activacion' WHERE id = ?";
        $db->prepare($sql)->execute([$data->client_id]);
        
        // Guardamos la nota
        $note = "🔄 Solicitud de reactivación enviada al sistema local. Nota: " . ($data->note ?? '');
        $db->prepare("INSERT INTO client_notes (client_id, note, created_at) VALUES (?, ?, NOW())")->execute([$data->client_id, $note]);
        
        echo json_encode(['success'=>true, 'message'=>'En cola de activación...']);
    }
} catch (Exception $e) { echo json_encode(['success'=>false, 'message'=>$e->getMessage()]); }
?>