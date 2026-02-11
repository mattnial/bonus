<?php
// ARCHIVO: api/admin/export_reports.php
// IMPORTANTE: Sin ob_start() aquí al inicio para no bloquear la descarga, 
// pero usaremos limpieza antes de los headers.

include_once '../config/database.php';

// Limpiar cualquier buffer previo (errores, espacios)
if (ob_get_level()) ob_end_clean();

$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    $database = new Database();
    $db_crm = $database->getCrmDB();
    $db_bonus = $database->getBonusDB();

    // Configurar cabeceras para descarga CSV
    $filename = "reporte_" . $type . "_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // Crear puntero de salida
    $output = fopen('php://output', 'w');

    // Añadir BOM para que Excel reconozca tildes y ñ
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // --- REPORTE 1: TICKETS ---
    if ($type === 'tickets') {
        // Encabezados
        fputcsv($output, ['ID', 'Cliente', 'Asunto', 'Departamento', 'Prioridad', 'Estado', 'Asignado A', 'Fecha']);
        
        $sql = "SELECT t.id, t.client_id, t.subject, t.department, t.priority, t.status, s.name as staff, t.created_at 
                FROM tickets t 
                LEFT JOIN staff s ON t.assigned_to_staff_id = s.id 
                ORDER BY t.created_at DESC LIMIT 1000";
        $stmt = $db_crm->query($sql);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obtener nombre cliente (Optimización básica)
            $cName = 'Desconocido';
            if($row['client_id']) {
                $stmtC = $db_bonus->prepare("SELECT name FROM clients WHERE id = ?");
                $stmtC->execute([$row['client_id']]);
                $c = $stmtC->fetch();
                if($c) $cName = $c['name'];
            }

            fputcsv($output, [
                $row['id'], 
                $cName, 
                $row['subject'], 
                $row['department'], 
                $row['priority'], 
                $row['status'], 
                $row['staff'], 
                $row['created_at']
            ]);
        }
    }

    // --- REPORTE 2: DEUDORES (CARTERA) ---
    elseif ($type === 'debtors') {
        fputcsv($output, ['ID', 'Cliente', 'Cedula', 'Telefono', 'Meses Mora', 'Total Deuda', 'Estado Servicio']);
        
        $sql = "SELECT id, name, cedula, phone, current_debt_months, plan_price, service_status 
                FROM clients WHERE current_debt_months > 0 ORDER BY current_debt_months DESC";
        $stmt = $db_bonus->query($sql);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total = number_format($row['current_debt_months'] * $row['plan_price'], 2);
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['cedula'],
                $row['phone'],
                $row['current_debt_months'],
                '$' . $total,
                $row['service_status']
            ]);
        }
    }

    // --- REPORTE 3: RRHH (SANCIONES) ---
    elseif ($type === 'rrhh') {
        fputcsv($output, ['Fecha', 'Empleado', 'Tipo Sancion', 'Monto Multa', 'Motivo', 'Registrado Por']);
        
        $sql = "SELECT s.created_at, st.name as empleado, s.type, s.amount, s.reason, adm.name as admin 
                FROM staff_sanctions s
                JOIN staff st ON s.staff_id = st.id
                JOIN staff adm ON s.created_by_staff_id = adm.id
                ORDER BY s.created_at DESC";
        $stmt = $db_crm->query($sql);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['created_at'],
                $row['empleado'],
                $row['type'],
                '$' . $row['amount'],
                $row['reason'],
                $row['admin']
            ]);
        }
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    echo "Error generando reporte: " . $e->getMessage();
}
?>