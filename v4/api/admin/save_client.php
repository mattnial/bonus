<?php
// ARCHIVO: api/admin/save_client.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

try {
    $database = new Database();
    $db = $database->getBonusDB(); // Usamos la base de clientes (Bonus)

    if (empty($data->name) || empty($data->cedula)) {
        throw new Exception("Nombre y Cédula son obligatorios.");
    }

    // Detectar si es Nuevo (INSERT) o Edición (UPDATE)
    $id = isset($data->id) ? intval($data->id) : 0;

    if ($id > 0) {
        // --- ACTUALIZAR ---
        $sql = "UPDATE clients SET 
                    name = :name, 
                    email = :email, 
                    phone = :phone, 
                    address = :addr,
                    coordinates = :coords,
                    has_internet = :has_int,
                    has_tv = :has_tv,
                    plan_details = :plan
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $id);
    } else {
        // --- CREAR NUEVO ---
        // Verificar duplicado
        $check = $db->prepare("SELECT id FROM clients WHERE cedula = ?");
        $check->execute([$data->cedula]);
        if($check->fetch()) throw new Exception("Ya existe un cliente con esa cédula.");

        $sql = "INSERT INTO clients 
                (name, cedula, email, phone, address, coordinates, has_internet, has_tv, plan_details, service_status, created_at)
                VALUES 
                (:name, :cedula, :email, :phone, :addr, :coords, :has_int, :has_tv, :plan, 'ACTIVO', NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":cedula", $data->cedula);
    }

    // Asignar valores comunes
    $has_int = isset($data->has_internet) ? 1 : 0;
    $has_tv = isset($data->has_tv) ? 1 : 0;

    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":phone", $data->phone);
    $stmt->bindParam(":addr", $data->address);
    $stmt->bindParam(":coords", $data->coordinates);
    $stmt->bindParam(":has_int", $has_int);
    $stmt->bindParam(":has_tv", $has_tv);
    $stmt->bindParam(":plan", $data->plan_details);

    if ($stmt->execute()) {
        $lastId = ($id > 0) ? $id : $db->lastInsertId();
        echo json_encode(["message" => "Cliente guardado exitosamente", "id" => $lastId]);
    } else {
        throw new Exception("Error al guardar en la base de datos.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>