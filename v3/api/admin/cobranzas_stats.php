<?php
// ARCHIVO: public_html/api/admin/cobranzas_stats.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../../api/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $inicioMes = date('Y-m-01 00:00:00');

    $priceLogic = "CASE 
                WHEN c.plan_price > 0 THEN c.plan_price 
                WHEN c.plan_name LIKE '%PLAN HOGAR VIL%' THEN 28.75 
                WHEN c.plan_name LIKE '%PLAN DISCAPACIDAD%' THEN 16.10 
                WHEN c.plan_name LIKE '%ORT-VILLA%' THEN 74.35 
                WHEN c.plan_name LIKE '%PRO-HOME%' THEN 92.00 
                ELSE 25.00 END";

    // 1. DEUDA TOTAL ACTUAL (MERMADA)
    // Calculamos la deuda de los que deben meses y restamos lo que esos mismos deudores han abonado.
    $sqlDebt = "SELECT 
                (
                  (SELECT SUM(d.debt_months * $priceLogic) FROM clients_debt_status d JOIN clients c ON d.cedula = c.cedula WHERE d.debt_months > 0)
                  - 
                  (SELECT COALESCE(SUM(i.amount), 0) FROM client_interactions i JOIN clients_debt_status d2 ON i.client_id = (SELECT id FROM clients WHERE cedula = d2.cedula LIMIT 1) WHERE d2.debt_months > 0 AND i.type = 'YA_PAGO')
                ) as total_money";
    
    $stmt1 = $db->prepare($sqlDebt);
    $stmt1->execute();
    $totalDebt = $stmt1->fetch(PDO::FETCH_ASSOC)['total_money'] ?? 0;

    // 2. DEUDA RECUPERADA (DINERO REAL ENTRADO ESTE MES)
    $sqlRec = "SELECT SUM(amount) as recovered FROM client_interactions WHERE type = 'YA_PAGO' AND created_at >= :inicioMes";
    
    $stmt2 = $db->prepare($sqlRec);
    $stmt2->execute([':inicioMes' => $inicioMes]);
    $recovered = $stmt2->fetch(PDO::FETCH_ASSOC)['recovered'] ?? 0;

    // 3. CONVENIOS ACTIVOS
    $hoy = date('Y-m-d');
    $stmt3 = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE cut_suspension_date >= :hoy");
    $stmt3->execute([':hoy' => $hoy]);
    $agreements = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        "total_debt" => number_format((float)$totalDebt, 2, '.', ''),
        "recovered_debt" => number_format((float)$recovered, 2, '.', ''),
        "agreements_today" => (int)$agreements
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}