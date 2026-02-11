<?php
// ARCHIVO: api/admin/create_agreement.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

try {
    if (empty($data->client_id) || empty($data->deadline) || empty($data->staff_id)) {
        throw new Exception("Faltan datos obligatorios.");
    }

    $database = new Database();
    $db_crm = $database->getCrmDB();
    $db_bonus = $database->getBonusDB();

    // 1. Crear el Registro de Convenio (CRM)
    // Asegúrate de haber creado la tabla 'payment_agreements' con el SQL inicial
    $q = "INSERT INTO payment_agreements (client_id, created_by_staff_id, deadline_date, notes, status, created_at) 
          VALUES (:cid, :sid, :deadline, :notes, 'ACTIVO', NOW())";
    
    $stmt = $db_crm->prepare($q);
    $stmt->bindParam(":cid", $data->client_id);
    $stmt->bindParam(":sid", $data->staff_id);
    $stmt->bindParam(":deadline", $data->deadline);
    $stmt->bindParam(":notes", $data->notes);
    
    if(!$stmt->execute()) throw new Exception("Error al guardar convenio.");

    // 2. REACTIVAR SERVICIO (Bonus DB)
    // Esto es lo "mágico": levanta el corte automáticamente
    $qUpd = "UPDATE clients SET service_status = 'ACTIVO' WHERE id = :cid";
    $stmtUpd = $db_bonus->prepare($qUpd);
    $stmtUpd->bindParam(":cid", $data->client_id);
    $stmtUpd->execute();

    // 3. Crear un Ticket Automático de Registro (Opcional, para que quede historial)
    $subject = "Convenio de Pago Creado";
    $desc = "Se reactiva servicio. Compromiso de pago hasta: " . $data->deadline . ". Notas: " . $data->notes;
    
    $qTick = "INSERT INTO tickets (client_id, subject, department, priority, description, created_by_staff_id, status, created_at) 
              VALUES (:cid, :sub, 'COBROS', 'ALTA', :desc, :sid, 'RESUELTO', NOW())";
    
    $stmtT = $db_crm->prepare($qTick);
    $stmtT->bindParam(":cid", $data->client_id);
    $stmtT->bindParam(":sub", $subject);
    $stmtT->bindParam(":desc", $desc);
    $stmtT->bindParam(":sid", $data->staff_id);
    $stmtT->execute();

    ob_clean();
    echo json_encode(["message" => "Convenio creado y servicio reactivado."]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>