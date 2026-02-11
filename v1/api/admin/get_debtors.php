<?php
// ARCHIVO: public_html/api/admin/get_debtors.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../../api/config/database.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

try {
    $database = new Database();
    $db = $database->getConnection();
    $hoy = date('Y-m-d');

    // Agregamos una subconsulta para sumar los pagos (amount) de cada cliente
    $sql = "SELECT 
                c.id, c.name, c.cedula, c.phone, c.plan_name, c.cut_suspension_date,
                d.debt_months,
                -- 1. Calculamos la deuda bruta total (meses * precio)
                (d.debt_months * CASE 
                    WHEN c.plan_price > 0 THEN c.plan_price 
                    WHEN c.plan_name LIKE '%PLAN HOGAR VIL%' THEN 28.75 
                    WHEN c.plan_name LIKE '%PLAN DISCAPACIDAD%' THEN 16.10 
                    WHEN c.plan_name LIKE '%ORT-VILLA%' THEN 74.35 
                    WHEN c.plan_name LIKE '%PRO-HOME%' THEN 92.00 
                    ELSE 25.00 END) as gross_debt,
                -- 2. Sumamos todos los abonos realizados por este cliente específico
                (SELECT COALESCE(SUM(amount), 0) FROM client_interactions WHERE client_id = c.id AND type = 'YA_PAGO') as total_paid
            FROM clients_debt_status d
            JOIN clients c ON d.cedula = c.cedula
            WHERE d.debt_months > 0";

    if ($filter === 'pending') {
        $sql .= " AND (c.cut_suspension_date IS NULL OR c.cut_suspension_date < '$hoy')";
        $sql .= " ORDER BY d.debt_months DESC LIMIT 200"; 
    } else {
        $sql .= " AND (c.cut_suspension_date >= '$hoy')";
        $sql .= " ORDER BY c.cut_suspension_date ASC";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute();

    $clients = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // RESTA CRÍTICA: La deuda real es la Bruta menos lo Pagado
        $real_debt = (float)$row['gross_debt'] - (float)$row['total_paid'];

        // Si por algún error la deuda da negativa o cero (ya pagó todo pero debt_months no se actualizó),
        // podrías optar por no mostrarlo o mostrar 0.00
        if ($real_debt < 0) $real_debt = 0;

        // Última interacción (para mostrar la nota en la tabla)
        $noteStmt = $db->prepare("SELECT notes FROM client_interactions WHERE client_id = ? ORDER BY created_at DESC LIMIT 1");
        $noteStmt->execute([$row['id']]);
        $lastAction = $noteStmt->fetch(PDO::FETCH_ASSOC);

        $clients[] = [
            "id" => $row['id'],
            "name" => mb_convert_encoding($row['name'], 'UTF-8', 'ISO-8859-1'),
            "cedula" => $row['cedula'],
            "phone" => $row['phone'],
            "total_debt" => number_format($real_debt, 2, '.', ''), // Enviamos la deuda mermada
            "months_owed" => intval($row['debt_months']),
            "promise_date" => $row['cut_suspension_date'],
            "last_note" => $lastAction ? $lastAction['notes'] : 'Sin gestión'
        ];
    }
    echo json_encode($clients);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}