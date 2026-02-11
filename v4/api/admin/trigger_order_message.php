<?php
// ARCHIVO: api/admin/trigger_order_message.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

try {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->ticket_id)) {
        throw new Exception("Falta ID del ticket");
    }

    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // 1. OBTENER EL TIPO DE TICKET (ASUNTO)
    $stmt = $db->prepare("SELECT subject FROM tickets WHERE id = ?");
    $stmt->execute([$data->ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) throw new Exception("Ticket no encontrado");

    // 2. MAPEO: Convertir el "Asunto del Ticket" al "Código de tu generador Excel"
    // Ajusta las claves de la izquierda según lo que tengas en tu base de datos (subjects)
    // Ajusta los valores de la derecha según tu archivo 'generar_orden.php'
    $mapaTipos = [
        'INSTALACION_FO' => 'instalacion_fo',
        'INSTALACION_RE' => 'instalacion_re',
        'MIGRACION'      => 'migracion',
        'RETIRO'         => 'retiro',
        'EVENTO'         => 'evento',
        'SOPORTE_FO'     => 'soporte_fo',
        'SOPORTE_RE'     => 'soporte_re',
        'CAMBIO_DOM_FO'  => 'cambio_dom_fo',
        'CAMBIO_DOM_RE'  => 'cambio_dom_re',
        'INSPECCION'     => 'inspeccion'
    ];

    // Limpiamos el asunto (mayúsculas y sin espacios extra) para buscarlo en el mapa
    $subjectKey = strtoupper(trim($ticket['subject']));
    
    // Si no encuentra el tipo exacto, usa 'inspeccion' por defecto
    $tipoOrden = isset($mapaTipos[$subjectKey]) ? $mapaTipos[$subjectKey] : 'inspeccion';

    // 3. CREAR EL MENSAJE CON EL ENLACE
    $excelUrl = "api/admin/generar_orden.php?id=" . $data->ticket_id . "&tipo=" . $tipoOrden;
    
    $msg = "📄 **ORDEN DE TRABAJO CREADA**\n\nTipo: " . $ticket['subject'] . "\n\n<a href='$excelUrl' target='_blank' class='inline-flex items-center gap-2 bg-green-600 text-white font-bold px-4 py-2 rounded-lg mt-2 hover:bg-green-700 transition shadow-md no-underline text-sm'><i class='fas fa-file-excel'></i> DESCARGAR ORDEN .XLSX</a>";

    // 4. INSERTAR EN EL CHAT (Como mensaje del sistema)
    $stmtRep = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 0, ?, NOW())");
    $stmtRep->execute([$data->ticket_id, $msg]);

    ob_clean();
    echo json_encode(["success" => true, "message" => "Orden generada en el chat"]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>