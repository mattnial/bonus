<?php
// ARCHIVO: public_html/api/admin/auth_staff.php
session_start(); // <--- ¡ESTA LÍNEA ES VITAL! SIN ESTO, EL LOGIN NO FUNCIONA.

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0); 

include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    try {
        $database = new Database();
        $db = $database->getCrmDB();

        // Buscamos al usuario
        $query = "SELECT id, name, email, password_hash, role, avatar_url, is_active FROM staff WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $data->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row['is_active'] == 0){
                http_response_code(401);
                echo json_encode(["message" => "Cuenta desactivada."]);
                exit();
            }

            if(password_verify($data->password, $row['password_hash'])) {
                
                // --- AQUÍ ESTÁ LA MAGIA QUE FALTABA ---
                // Guardamos los datos en la memoria del servidor (SESIÓN)
                $_SESSION['staff_id'] = $row['id'];
                $_SESSION['name']     = $row['name'];
                $_SESSION['email']    = $row['email'];
                $_SESSION['role']     = $row['role']; 
                $_SESSION['avatar']   = $row['avatar_url'];
                // --------------------------------------

                unset($row['password_hash']); // No enviamos la clave hash por seguridad
                
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Bienvenido " . $row['name'],
                    "user" => $row
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Contraseña incorrecta."]);
            }
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Usuario no encontrado."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error servidor: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Datos incompletos."]);
}
?>