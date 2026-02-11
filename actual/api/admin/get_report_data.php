<?php
// ARCHIVO: api/admin/get_report_data.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    $database = new Database();
    $db_crm = $database->getCrmDB();
    $db_bonus = $database->getBonusDB();

    $data = [];

    // --- REPORTE 1: TICKETS ---
    if ($type === 'tickets') {
        $sql = "SELECT t.id, t.subject, t.department, t.priority, t.status, s.name as staff, t.created_at, t.client_id 
                FROM tickets t 
                LEFT JOIN staff s ON t.assigned_to_staff_id = s.id 
                ORDER BY t.created_at DESC LIMIT 500";
        $stmt = $db_crm->query($sql);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enriquecer con nombres de clientes
        foreach ($tickets as $row) {
            $cName = 'Desconocido';
            if($row['client_id']) {
                $stmtC = $db_bonus->prepare("SELECT name FROM clients WHERE id = ?");
                $stmtC->execute([$row['client_id']]);
                $c = $stmtC->fetch();
                if($c) $cName = $c['name'];
            }
            $row['client_name'] = $cName;
            $data[] = $row;
        }
    }

    // --- REPORTE 2: DEUDORES ---
    elseif ($type === 'debtors') {
        $sql = "SELECT name, cedula, phone, current_debt_months, plan_price, service_status 
                FROM clients WHERE current_debt_months > 0 ORDER BY current_debt_months DESC";
        $stmt = $db_bonus->query($sql);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['total_due'] = number_format($row['current_debt_months'] * $row['plan_price'], 2);
            $data[] = $row;
        }
    }

    // --- REPORTE 3: RRHH ---
    elseif ($type === 'rrhh') {
        $sql = "SELECT s.created_at, st.name as empleado, s.type, s.amount, s.reason 
                FROM staff_sanctions s
                JOIN staff st ON s.staff_id = st.id
                ORDER BY s.created_at DESC";
        $stmt = $db_crm->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    ob_clean();
    echo json_encode($data);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["error" => $e->getMessage()]);
}
?>