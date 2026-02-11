 <?php

// ARCHIVO: api/admin/get_client_detail.php

ob_start(); // BUFFER ACTIVADO


header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 0); // Ocultar errores visuales


include_once '../config/database.php';


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;


try {

    if ($id <= 0) throw new Exception("ID inválido");


    $database = new Database();

    $db_bonus = $database->getBonusDB();

    $db_crm = $database->getCrmDB();


    // 1. Cliente (Usamos alias para evitar errores si faltan columnas)

    $query = "SELECT * FROM clients WHERE id = :id LIMIT 1";

    $stmt = $db_bonus->prepare($query);

    $stmt->bindParam(":id", $id);

    $stmt->execute();

    $client = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$client) throw new Exception("Cliente no encontrado");


    // 2. Tickets

    $tQuery = "SELECT t.id, t.subject, t.status, t.priority, t.department, t.created_at, s.name as staff_name

               FROM tickets t

               LEFT JOIN staff s ON t.assigned_to_staff_id = s.id

               WHERE t.client_id = :id

               ORDER BY t.created_at DESC";

   

    try {

        $stmtT = $db_crm->prepare($tQuery);

        $stmtT->bindParam(":id", $id);

        $stmtT->execute();

        $tickets = $stmtT->fetchAll(PDO::FETCH_ASSOC);

    } catch(Exception $ex) { $tickets = []; }


    // LIMPIEZA Y ENVÍO

    ob_clean();

    echo json_encode(["client" => $client, "tickets" => $tickets]);


} catch (Exception $e) {

    ob_clean();

    http_response_code(500);

    echo json_encode(["error" => $e->getMessage()]);

}

?> 