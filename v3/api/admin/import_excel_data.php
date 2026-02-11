<?php
// ARCHIVO: api/admin/import_excel_data.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

$input = json_decode(file_get_contents("php://input"), true);
$response = ["success" => false, "message" => ""];

try {
    if (!$input) throw new Exception("No llegaron datos");

    $database = new Database();
    $db = $database->getCrmDB();
    $db_bonus = $database->getBonusDB();

    $filename = $input['filename'];
    $rows = $input['rows']; // Matriz de datos del Excel

    // 1. DETECTAR TIPO DE ORDEN
    $orderType = 'GENERICA';
    $nameUpper = strtoupper($filename);
    
    if (strpos($nameUpper, 'INSTALACION F.O') !== false) $orderType = 'INSTALACION_FO';
    elseif (strpos($nameUpper, 'INSTALACION R.E') !== false) $orderType = 'INSTALACION_RE';
    elseif (strpos($nameUpper, 'REPARACION') !== false) $orderType = 'REPARACION';
    elseif (strpos($nameUpper, 'MIGRACION') !== false) $orderType = 'MIGRACION';
    elseif (strpos($nameUpper, 'RETIRO') !== false) $orderType = 'RETIRO';
    elseif (strpos($nameUpper, 'DOMICILIO') !== false) $orderType = 'CAMBIO_DOMICILIO';
    elseif (strpos($nameUpper, 'INSPECCION') !== false) $orderType = 'INSPECCION';
    elseif (strpos($nameUpper, 'EVENTO') !== false) $orderType = 'EVENTO';

    // 2. BUSCAR DATOS CLAVE (Minería en la matriz)
    $extracted = ['cedula' => '', 'cliente' => '', 'tecnico' => ''];
    $foundCedula = false;

    foreach ($rows as $rowIndex => $row) {
        // Convertimos la fila en texto para buscar rápido
        $rowStr = implode(" ", array_map('strval', $row));
        $rowStrLower = strtolower($rowStr);

        // Búsqueda de Cédula (Buscamos "DNI/CI" y miramos celdas cercanas)
        if (!$foundCedula && (strpos($rowStrLower, 'dni') !== false || strpos($rowStrLower, 'ci') !== false)) {
            foreach ($row as $cellIndex => $cell) {
                $cellClean = preg_replace('/[^0-9]/', '', strval($cell)); // Solo números
                // Si tiene 10 dígitos y empieza con 11, 19, 09, etc. es probable cédula
                if (strlen($cellClean) == 10 || strlen($cellClean) == 13) {
                    $extracted['cedula'] = $cellClean;
                    $foundCedula = true;
                    break;
                }
            }
        }

        // Búsqueda de Técnico
        if (strpos($rowStrLower, 'nombre técnico') !== false || strpos($rowStrLower, 'tecnicos:') !== false) {
             // Usualmente el nombre está en la siguiente celda no vacía
             foreach ($row as $cell) {
                 if (strlen($cell) > 4 && stripos($cell, 'nombre') === false) {
                     $extracted['tecnico'] = $cell; 
                     break; 
                 }
             }
        }
    }

    if (empty($extracted['cedula'])) throw new Exception("No encontré la cédula en el Excel.");

    // 3. BUSCAR CLIENTE EN BD
    $stmt = $db_bonus->prepare("SELECT id, name FROM clients WHERE cedula LIKE ? LIMIT 1");
    $stmt->execute(["%" . $extracted['cedula'] . "%"]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) throw new Exception("Cliente con cédula {$extracted['cedula']} no registrado.");

    // 4. GUARDAR ORDEN EN BD
    // Buscar ID técnico (simple)
    $techId = 1; // Default
    if(!empty($extracted['tecnico'])) {
        $stmtT = $db->prepare("SELECT id FROM staff WHERE name LIKE ? LIMIT 1");
        $stmtT->execute(["%".explode(' ', trim($extracted['tecnico']))[0]."%"]);
        $t = $stmtT->fetch();
        if($t) $techId = $t['id'];
    }

    $sql = "INSERT INTO work_orders (client_id, order_type, status, tech_lead_id, created_at, scheduled_date) 
            VALUES (:cid, :type, 'FINALIZADA', :tech, NOW(), NOW())";
    
    $stmtIns = $db->prepare($sql);
    $stmtIns->execute([
        ':cid' => $client['id'],
        ':type' => $orderType,
        ':tech' => $techId
    ]);

    $response['success'] = true;
    $response['message'] = "Orden creada exitosamente.";
    $response['data'] = [
        "cliente" => $client['name'],
        "tipo" => $orderType
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>