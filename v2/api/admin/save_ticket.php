<?php
// ARCHIVO: api/admin/save_ticket.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

try {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->client_id) || empty($data->subject)) {
        throw new Exception("Datos incompletos");
    }

    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // 1. OBTENER PRIORIDAD (Si no viene, por defecto MEDIA)
    $prioridad = !empty($data->priority) ? $data->priority : 'MEDIA';

    // 2. INSERTAR TICKET
    // Asegúrate de que tu tabla 'tickets' tenga la columna 'priority'
    $query = "INSERT INTO tickets 
              (client_id, created_by, department, priority, service_affected, subject, description, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'ABIERTO', NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        $data->client_id,
        $data->created_by,
        $data->department,
        $prioridad,              // <--- AQUÍ SE GUARDA LA PRIORIDAD
        $data->service_affected,
        $data->subject,
        $data->description
    ]);

    $ticketId = $db->lastInsertId();

    // 3. LOGICA DE GENERACIÓN DE ORDEN (CHAT)
    // Si es técnico, insertamos el mensaje con botón de descarga
    $tiposTecnicos = [
        'INSTALACION_FO', 'INSTALACION_RE', 'INSPECCION', 
        'SOPORTE_FO', 'SOPORTE_RE', 'MIGRACION', 'RETIRO', 
        'CAMBIO_DOM_FO', 'CAMBIO_DOM_RE', 'EVENTO'
    ];
    
    // Normalizamos asunto
    $subjectUpper = strtoupper($data->subject);
    $esTecnico = false;
    if($data->department === 'TECNICA' || $data->department === 'SOPORTE') $esTecnico = true;
    if(in_array($subjectUpper, $tiposTecnicos)) $esTecnico = true;

    if ($esTecnico) {
        $tipoOrden = strtolower($data->subject);
        $excelUrl = "api/admin/generar_orden.php?id=" . $ticketId . "&tipo=" . $tipoOrden;
        
        $msg = "📊 **ORDEN GENERADA**\n\n<a href='$excelUrl' target='_blank' class='inline-block bg-green-600 text-white font-bold px-4 py-2 rounded mt-2 hover:bg-green-700 no-underline shadow-md text-xs'><i class='fas fa-file-excel'></i> DESCARGAR ORDEN</a>";

        $stmtRep = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 0, ?, NOW())");
        $stmtRep->execute([$ticketId, $msg]);
    }

    ob_clean();
    echo json_encode(["success" => true, "ticket_id" => $ticketId]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>