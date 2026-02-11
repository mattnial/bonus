<?php
// ARCHIVO: api/tickets/create.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if (file_exists('../../config/database.php')) {
    include_once '../../config/database.php';
} elseif (file_exists('../config/database.php')) {
    include_once '../config/database.php';
} else {
    echo json_encode(["success" => false, "message" => "Error: No se encuentra database.php"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection(); 

    // 1. RECIBIR DATOS
    $client_id = $_POST['client_id'] ?? null;
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $staff_id = $_POST['created_by'] ?? 0;
    
    // CORRECCIÓN 1: Recibir la prioridad del formulario (si no existe, usa MEDIA)
    $priority = $_POST['priority'] ?? 'MEDIA'; 

    // CORRECCIÓN 2: Determinar departamento automáticamente según el asunto
    $department = 'SOPORTE'; // Valor por defecto
    $service_affected = 'INTERNET';
    $status = 'ABIERTO';

    $subjectUpper = strtoupper($subject);
    $techKeywords = ['INSTALACION', 'SOPORTE', 'MIGRACION', 'RETIRO', 'CAMBIO_DOM', 'INSPECCION', 'EVENTO'];
    
    if (strpos($subjectUpper, 'FACTURACION') !== false) {
        $department = 'FACTURACION';
    } else {
        foreach ($techKeywords as $kw) {
            if (strpos($subjectUpper, $kw) !== false) {
                $department = 'TECNICA';
                break;
            }
        }
    }

    // 2. VALIDACIONES
    if (empty($client_id)) throw new Exception("Falta el ID del cliente");
    if (empty($subject)) throw new Exception("Falta el motivo del ticket");

    // ---------------------------------------------------------
    // PASO EXTRA: OBTENER CÉDULA DEL CLIENTE
    // ---------------------------------------------------------
    $stmtCli = $db->prepare("SELECT cedula FROM clients WHERE id = ?");
    $stmtCli->execute([$client_id]);
    $cliente = $stmtCli->fetch(PDO::FETCH_ASSOC);
    
    $client_cedula = $cliente ? $cliente['cedula'] : 'CONSUMIDOR_FINAL';
    $title = substr($subject, 0, 150); 

    // ---------------------------------------------------------
    // 3. INSERTAR EN BASE DE DATOS
    // ---------------------------------------------------------
    $query = "INSERT INTO tickets 
              (
                client_id, client_cedula, title, subject, description, 
                status, priority, department, service_affected, 
                created_by_staff_id, created_at
              ) 
              VALUES 
              (
                :client_id, :client_cedula, :title, :subject, :description, 
                :status, :priority, :department, :service, 
                :created_by_staff_id, NOW()
              )";

    $stmt = $db->prepare($query);

    // Limpieza básica
    $description = htmlspecialchars(strip_tags($description));
    $subject = htmlspecialchars(strip_tags($subject));

    // Bindeo de parámetros
    $stmt->bindParam(":client_id", $client_id);
    $stmt->bindParam(":client_cedula", $client_cedula);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":subject", $subject);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":priority", $priority); // Ahora sí envía ALTA/MEDIA/BAJA
    $stmt->bindParam(":department", $department); // Ahora envía TECNICA/FACTURACION...
    $stmt->bindParam(":service", $service_affected);
    $stmt->bindParam(":created_by_staff_id", $staff_id);

    if ($stmt->execute()) {
        $ticket_id = $db->lastInsertId();

        // ---------------------------------------------------------
        // CORRECCIÓN 3: GENERAR ORDEN AUTOMÁTICA EN EL CHAT
        // ---------------------------------------------------------
        if ($department === 'TECNICA') {
            $tipoOrden = strtolower($subject);
            // URL al generador de Excel (ajustada a ruta relativa web)
            $excelUrl = "api/admin/generar_orden.php?id=" . $ticket_id . "&tipo=" . $tipoOrden;
            
            $msg = "📊 **ORDEN GENERADA**\n\n<a href='$excelUrl' target='_blank' class='inline-block bg-green-600 text-white font-bold px-4 py-2 rounded mt-2 hover:bg-green-700 no-underline shadow-md text-xs'><i class='fas fa-file-excel'></i> DESCARGAR ORDEN</a>";

            // Insertamos la nota del SISTEMA (sender_id = 0)
            // Asegúrate que tu tabla sea 'ticket_replies' o 'ticket_chats' según tu BD
            // Asumo 'ticket_replies' basado en conversaciones anteriores
            $stmtRep = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 0, ?, NOW())");
            $stmtRep->execute([$ticket_id, $msg]);
        }

        echo json_encode([
            "success" => true,
            "message" => "Ticket creado correctamente",
            "id" => $ticket_id
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception("Error SQL: " . $errorInfo[2]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>