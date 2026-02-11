<?php
// ARCHIVO: public_html/api/admin/save_cobranza.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if(empty($data->client_id) || empty($data->type)) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]); exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $cid = $data->client_id;
    $type = $data->type; 
    $metodo = $data->contact_method ?? 'SISTEMA';
    $monto = floatval($data->amount_paid ?? 0);
    $fecha = $data->promise_date ?? $data->next_promise ?? null;
    $nota = $data->notes;

    // --- CASO 1: CONVENIO NUEVO (Desde la primera pestaña) ---
    if ($type === 'CONVENIO_PAGO') {
        $notaFinal = "[$metodo] " . $nota;
        $sql = "INSERT INTO client_interactions (client_id, type, notes, promise_date, created_at) VALUES (?, ?, ?, ?, NOW())";
        $db->prepare($sql)->execute([$cid, $type, $notaFinal, $fecha]);
        
        // Protegemos al cliente con la fecha
        $db->prepare("UPDATE clients SET cut_suspension_date = ? WHERE id = ?")->execute([$fecha, $cid]);
    }

    // --- CASO 2: INFORMAR PAGO (Total o Parcial) ---
    elseif ($type === 'YA_PAGO') {
        $esTotal = $data->is_total ?? true;
        $notaFinal = "PAGO RECIBIDO (".$data->method."): $".$monto;
        
        if(!$esTotal) {
            $notaFinal .= " | PAGO PARCIAL. Restan ".$data->installments." pagos. Próxima promesa: ".$fecha;
        }

        // Guardamos la interacción con el MONTO real para las estadísticas
        $sql = "INSERT INTO client_interactions (client_id, type, notes, amount, created_at) VALUES (?, 'YA_PAGO', ?, ?, NOW())";
        $db->prepare($sql)->execute([$cid, $notaFinal, $monto]);

        // Si es pago TOTAL, limpiamos la deuda
        if ($esTotal) {
            $stmtC = $db->prepare("SELECT cedula FROM clients WHERE id = ?");
            $stmtC->execute([$cid]);
            $cedula = $stmtC->fetchColumn();

            $db->prepare("UPDATE clients_debt_status SET debt_months = 0 WHERE cedula = ?")->execute([$cedula]);
            $db->prepare("UPDATE clients SET cut_suspension_date = NULL WHERE id = ?")->execute([$cid]);
        } else {
            // Si es PARCIAL, actualizamos la fecha para el siguiente abono
            $db->prepare("UPDATE clients SET cut_suspension_date = ? WHERE id = ?")->execute([$fecha, $cid]);
        }
    }

    // --- CASO 3: INCUMPLIMIENTO ---
    elseif ($type === 'BROKEN_PROMISE') {
        $notaFinal = "🚨 CONVENIO NO CUMPLIDO. Cliente no realizó el pago acordado. PROCEDER AL CORTE.";
        $db->prepare("INSERT INTO client_interactions (client_id, type, notes, created_at) VALUES (?, 'BROKEN_PROMISE', ?, NOW())")->execute([$cid, $notaFinal]);
        
        // Quitamos protección para que aparezca en lista de corte
        $db->prepare("UPDATE clients SET cut_suspension_date = NULL WHERE id = ?")->execute([$cid]);
    }

    echo json_encode(["success" => true]);

} catch (Exception $e) { 
    echo json_encode(["success" => false, "message" => $e->getMessage()]); 
}