<?php
// ARCHIVO: api/auth/update_profile.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Método no permitido");

    $database = new Database();
    $db = $database->getCrmDB();

    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Nueva contraseña (puede venir vacía)

    if (empty($id) || empty($name)) throw new Exception("Datos incompletos");

    // 1. Construir Query Dinámica
    $sql = "UPDATE staff SET name = :name, email = :email";
    
    // Si envió contraseña, la actualizamos
    if (!empty($password)) {
        $sql .= ", password_hash = :pass";
    }

    // 2. Manejar Avatar (Foto)
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $dir = '../../uploads/avatars/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $filename = "avatar_{$id}_" . time() . ".{$ext}";
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
            $avatarPath = $filename; // Guardamos solo el nombre del archivo
            $sql .= ", avatar_url = :avatar";
        }
    }

    $sql .= " WHERE id = :id";

    // 3. Ejecutar
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":id", $id);

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(":pass", $hash);
    }

    if ($avatarPath) {
        $stmt->bindParam(":avatar", $avatarPath);
    }

    if ($stmt->execute()) {
        // Devolver los datos actualizados para el LocalStorage
        ob_clean();
        echo json_encode([
            "message" => "Perfil actualizado",
            "avatar" => $avatarPath
        ]);
    } else {
        throw new Exception("Error al actualizar en BD");
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>