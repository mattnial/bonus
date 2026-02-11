<?php
// api/admin/assign_task.php
header('Content-Type: application/json');

// 1. CARGAR LIBRERÍAS (Excel + Mailer)
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once '../config/database.php';

// RECIBIR DATOS (JSON)
$input = json_decode(file_get_contents('php://input'), true);
$ticket_id = $input['ticket_id'] ?? 0;
$tech_id   = $input['technician_id'] ?? 0; // ID del técnico en tabla staff
$tipo_orden = strtolower($input['order_type'] ?? 'inspeccion');

if (!$ticket_id || !$tech_id) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos (Ticket o Técnico)']);
    exit;
}

try {
    $db = (new Database())->getConnection();

    // ---------------------------------------------------------
    // PASO 1: OBTENER TODOS LOS DATOS (Cliente, Ticket, Técnico)
    // ---------------------------------------------------------
    
    // Datos del Ticket y Cliente
    $q1 = "SELECT t.*, c.name as client_name, c.cedula, c.phone, c.address, c.email, c.plan_name 
           FROM tickets t 
           JOIN clients c ON t.client_id = c.id 
           WHERE t.id = ?";
    $stmt = $db->prepare($q1);
    $stmt->execute([$ticket_id]);
    $ticketData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Datos del Técnico (Tabla staff)
    $q2 = "SELECT id, name, email, role FROM staff WHERE id = ?";
    $stmt = $db->prepare($q2);
    $stmt->execute([$tech_id]);
    $techData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticketData || !$techData) {
        throw new Exception("No se encontró el ticket o el técnico.");
    }

    // ---------------------------------------------------------
    // PASO 2: GENERAR EL EXCEL (WORK ORDER)
    // ---------------------------------------------------------
    
    // Configuración de archivos (Igual que antes)
    $config = [
        'inspeccion'      => ['file' => 'inspeccion.xlsx',      'celda_dir' => 'C14'],
        'instalacion_fo'  => ['file' => 'instalacion_fo.xlsx',  'celda_dir' => 'C15'],
        'instalacion_re'  => ['file' => 'instalacion_re.xlsx',  'celda_dir' => 'C15'],
        'migracion'       => ['file' => 'migracion.xlsx',       'celda_dir' => 'C14'],
        'retiro'          => ['file' => 'retiro.xlsx',          'celda_dir' => 'C14'],
        'evento'          => ['file' => 'evento.xlsx',          'celda_dir' => 'C14'],
        'soporte_fo'      => ['file' => 'soporte_fo.xlsx',      'celda_dir' => 'C15'],
        'soporte_re'      => ['file' => 'soporte_re.xlsx',      'celda_dir' => 'C15'],
        'cambio_dom_fo'   => ['file' => 'cambio_dom_fo.xlsx',   'celda_dir' => 'C15'],
        'cambio_dom_re'   => ['file' => 'cambio_dom_re.xlsx',   'celda_dir' => 'C15']
    ];

    if (!isset($config[$tipo_orden])) throw new Exception("Tipo de orden no válido.");

    $plantilla = 'templates/' . $config[$tipo_orden]['file'];
    
    if (!file_exists($plantilla)) throw new Exception("No existe la plantilla: $plantilla");

    $spreadsheet = IOFactory::load($plantilla);
    $sheet = $spreadsheet->getActiveSheet();

    // LLENAR DATOS
    $sheet->setCellValue('J3', $ticketData['id']); // N° Orden
    $sheet->setCellValue('J4', date('d/m/Y'));     // Fecha
    $sheet->setCellValue('C10', $ticketData['client_name']);
    $sheet->setCellValue('H10', $ticketData['cedula']);
    $sheet->setCellValue('C11', $ticketData['phone']);
    $sheet->setCellValue('C12', $ticketData['email']);
    
    // Dirección dinámica
    $sheet->setCellValue($config[$tipo_orden]['celda_dir'], $ticketData['address']);
    
    // Observaciones
    $sheet->setCellValue('C25', $ticketData['description']); 

    // **IMPORTANTE: ASIGNAR TÉCNICO EN EL EXCEL**
    // Busqué en tus CSV y la celda "Nombre Técnico" suele estar por la fila 33 o 35
    // Ajusta esta celda 'C33' si ves que cae en otro lado en tus archivos
    $sheet->setCellValue('C33', $techData['name']); 

    // Guardar temporalmente en el servidor
    $fileName = "Orden_{$tipo_orden}_Ticket{$ticket_id}.xlsx";
    $tempPath = sys_get_temp_dir() . '/' . $fileName;
    
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($tempPath);

    // ---------------------------------------------------------
    // PASO 3: ENVIAR EMAIL CON ADJUNTO
    // ---------------------------------------------------------
    $mail = new PHPMailer(true);
    
    // CONFIGURACIÓN SMTP (¡PON TUS DATOS AQUÍ!)
    $mail->isSMTP();
    $mail->Host       = 'mail.vilcanet.com.ec'; // Tu servidor de correo
    $mail->SMTPAuth   = true;
    $mail->Username   = 'vilcanet@vilcanet.com.ec'; // Tu usuario SMTP
    $mail->Password   = 'macaN2522@';    // Tu contraseña SMTP
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // O ENCRYPTION_STARTTLS
    $mail->Port       = 465; // O 587

    // Contenido
    $mail->setFrom('admin@vilcanet.com.ec', 'Vilcanet CRM');
    $mail->addAddress($techData['email'], $techData['name']); // Correo del técnico
    
    $mail->isHTML(true);
    $mail->Subject = "Nueva Orden Asignada: #$ticket_id ($tipo_orden)";
    $mail->Body    = "
        <h3>Hola, {$techData['name']}</h3>
        <p>Se te ha asignado un nuevo trabajo.</p>
        <ul>
            <li><strong>Cliente:</strong> {$ticketData['client_name']}</li>
            <li><strong>Dirección:</strong> {$ticketData['address']}</li>
            <li><strong>Tipo:</strong> " . strtoupper($tipo_orden) . "</li>
        </ul>
        <p>Adjunto encontrarás la Orden de Trabajo en Excel para rellenar.</p>
    ";

    // Adjuntar el Excel generado
    $mail->addAttachment($tempPath, $fileName);
    $mail->send();

    // ---------------------------------------------------------
    // PASO 4: ACTUALIZAR DB Y NOTIFICAR EN CRM
    // ---------------------------------------------------------

    // 1. Asignar el ticket
    $upd = "UPDATE tickets SET status = 'Asignado', assigned_to = ? WHERE id = ?";
    $stmt = $db->prepare($upd);
    $stmt->execute([$tech_id, $ticket_id]);

    // 2. Crear Notificación interna (Ajusta el nombre de tu tabla si es diferente)
    // Asumo una tabla 'notifications' con (user_id, message, is_read, created_at)
    $notif = "INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())";
    $stmt = $db->prepare($notif);
    $mensaje = "Nueva orden de $tipo_orden asignada. Ticket #$ticket_id";
    $stmt->execute([$tech_id, $mensaje]);

    // Limpiar archivo temporal
    @unlink($tempPath);

    echo json_encode(['success' => true, 'message' => 'Orden generada, enviada por correo y asignada correctamente.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>