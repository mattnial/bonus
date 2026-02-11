<?php
// ARCHIVO: api/admin/save_sanction.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Método no permitido");

    // 1. Validar Datos
    if (empty($_POST['staff_id']) || empty($_POST['type']) || empty($_POST['reason'])) {
        throw new Exception("Faltan datos obligatorios.");
    }

    // 2. Subir Evidencia (PDF/Foto)
    $evidenceUrl = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/rrhh/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'png', 'jpeg'];
        
        if (!in_array($ext, $allowed)) throw new Exception("Solo archivos PDF o Imágenes.");

        $fileName = 'sanction_' . $_POST['staff_id'] . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName)) {
            // URL pública
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $evidenceUrl = "$protocol://" . $_SERVER['HTTP_HOST'] . "/uploads/rrhh/" . $fileName;
        }
    }

    // 3. Guardar en BD
    $database = new Database();
    $db = $database->getCrmDB();

    $q = "INSERT INTO staff_sanctions (staff_id, created_by_staff_id, type, amount, reason, evidence_url, created_at) 
          VALUES (:sid, :admin, :type, :amount, :reason, :url, NOW())";
    
    $stmt = $db->prepare($q);
    $amount = !empty($_POST['amount']) ? $_POST['amount'] : 0.00;
    
    $stmt->bindParam(":sid", $_POST['staff_id']);
    $stmt->bindParam(":admin", $_POST['admin_id']);
    $stmt->bindParam(":type", $_POST['type']);
    $stmt->bindParam(":amount", $amount);
    $stmt->bindParam(":reason", $_POST['reason']);
    $stmt->bindParam(":url", $evidenceUrl);
    
    $stmt->execute();

    ob_clean();
    echo json_encode(["message" => "Sanción registrada correctamente"]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>