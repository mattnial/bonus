<?php
// ARCHIVO: api/admin/get_staff_history.php
// MODO DEBUG SUPREMO
// Esto creará un archivo 'debug.txt' en la misma carpeta cada vez que se intente entrar
file_put_contents('debug_log.txt', "INTENTO DE ACCESO: " . date('Y-m-d H:i:s') . " - IP: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);

// ... aquí sigue tu código normal ...
ob_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    $database = new Database();
    $db = $database->getCrmDB();

    // Validar conexión
    if (!$db) throw new Exception("Error BD");

    // 1. Datos Empleado
    $qS = "SELECT id, name, email, role FROM staff WHERE id = :id";
    $stmt = $db->prepare($qS);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$staff) throw new Exception("Empleado no encontrado");

    // 2. Historial Sanciones (Try-Catch por si la tabla no tiene datos aún)
    $sanctions = [];
    try {
        $qSan = "SELECT * FROM staff_sanctions WHERE staff_id = :id ORDER BY created_at DESC";
        $stmtSan = $db->prepare($qSan);
        $stmtSan->bindParam(":id", $id);
        $stmtSan->execute();
        $sanctions = $stmtSan->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $ex) { /* Tabla vacía */ }

    // 3. LIMPIAR Y ENVIAR
    ob_clean();
    echo json_encode(["staff" => $staff, "sanctions" => $sanctions]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500); // Cambiamos a 500 para que el JS sepa que es error interno
    echo json_encode(["error" => "Server Error: " . $e->getMessage()]);
}
?>