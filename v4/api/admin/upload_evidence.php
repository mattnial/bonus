<?php
// ARCHIVO: api/admin/upload_evidence.php
// OBJETIVO: Subir archivo y guardar la ruta pura en la BD.
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

if (file_exists('../../config/database.php')) include_once '../../config/database.php';
elseif (file_exists('../config/database.php')) include_once '../config/database.php';

try {
    if (empty($_FILES['file']) || empty($_POST['ticket_id'])) throw new Exception("Datos incompletos");

    $ticket_id = intval($_POST['ticket_id']);
    
    // 1. Configurar y crear directorio
    // Asegúrate que esta ruta sea correcta relativa a donde está este archivo.
    $uploadDir = '../../uploads/tickets/'; 
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de subidas");
        }
    }

    // 2. Sanear nombre de archivo
    $originalName = basename($_FILES['file']['name']);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    // Nombre único: timestamp + aleatorio + extension
    $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;

    // Validar extensiones (seguridad)
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'zip'];
    if (!in_array($fileExtension, $allowed)) {
        throw new Exception("Tipo de archivo no permitido");
    }

    // 3. Mover archivo
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        throw new Exception("Error al mover el archivo subido");
    }

    // 4. Guardar en BD
    // Importante: Guardamos un prefijo "FILE:" + la ruta relativa para que JS lo detecte.
    // Ejemplo guardado: FILE:uploads/tickets/123456_archivo.jpg
    $filePathForDb = "FILE:uploads/tickets/" . $fileName;

    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // Insertamos como STAFF (sender_id 1 por defecto, ajusta si tienes sesiones)
    $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_id, message, created_at) VALUES (?, 'STAFF', 1, ?, NOW())");
    $stmt->execute([$ticket_id, $filePathForDb]);

    ob_clean();
    echo json_encode(["success" => true, "file" => $filePathForDb]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>