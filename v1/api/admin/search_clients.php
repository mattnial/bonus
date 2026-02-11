<?php
// ARCHIVO: api/admin/search_clients.php
// VERSIÓN MEJORADA: Ordena por Urgencia > Mora > Apellido

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

$q = $_GET['q'] ?? '';

try {
    $db = (new Database())->getConnection();

    // 1. CONSULTA INTELIGENTE
    // Usamos LEFT JOIN para traer datos de mora y tickets sin perder clientes que no tienen problemas.
    // La clave está en el ORDER BY con CASE.
    
    $sql = "
        SELECT 
            c.id, 
            c.name, 
            c.cedula, 
            c.phone, 
            c.email, 
            c.address,
            c.plan_name, 
            c.service_status,
            
            -- Datos de Mora (si existen en la tabla que llena el cron)
            COALESCE(d.debt_months, 0) as debt_months,
            
            -- Conteo de Tickets Urgentes Abiertos
            (SELECT COUNT(*) FROM tickets t WHERE t.client_id = c.id AND t.status = 'ABIERTO' AND t.priority = 'URGENTE') as urgent_tickets,
            
            -- Conteo de Tickets Normales Abiertos
            (SELECT COUNT(*) FROM tickets t WHERE t.client_id = c.id AND t.status = 'ABIERTO') as open_tickets

        FROM clients c
        LEFT JOIN clients_debt_status d ON c.id = d.client_id
        WHERE 
            (c.name LIKE :q OR c.cedula LIKE :q)
        
        ORDER BY 
            -- 1. PRIMERO: Los que tienen tickets URGENTES
            (SELECT COUNT(*) FROM tickets t WHERE t.client_id = c.id AND t.status = 'ABIERTO' AND t.priority = 'URGENTE') DESC,
            
            -- 2. SEGUNDO: Los que tienen Mora Alta (> 2 meses)
            d.debt_months DESC,
            
            -- 3. TERCERO: Los que tienen cualquier ticket abierto
            (SELECT COUNT(*) FROM tickets t WHERE t.client_id = c.id AND t.status = 'ABIERTO') DESC,
            
            -- 4. FINALMENTE: Alfabético
            c.name ASC
        
        LIMIT 20
    ";

    $stmt = $db->prepare($sql);
    $term = "%$q%";
    $stmt->bindParam(':q', $term);
    $stmt->execute();
    
    $results = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // --- PROCESAMIENTO DE DATOS PARA EL FRONTEND ---
        
        // 1. Iniciales
        $words = explode(" ", $row['name']);
        $initials = "";
        foreach ($words as $w) { if(!empty($w)) $initials .= strtoupper($w[0]); }
        $initials = substr($initials, 0, 2);

        // 2. Determinar Color y Estado Visual (Lógica de Prioridad)
        $color = 'blue';       // Default (Normal)
        $badge_color = 'bg-blue-600';
        $status_text = $row['service_status'];

        // A. Prioridad Máxima: Ticket Urgente
        if ($row['urgent_tickets'] > 0) {
            $color = 'red';
            $status_text = "SOPORTE URGENTE";
            $badge_color = 'bg-red-600 animate-pulse';
        }
        // B. Prioridad Media: Mora Alta
        else if ($row['debt_months'] >= 2) {
            $color = 'orange'; // Usaremos 'orange' en el JS para detectar mora
            $status_text = "MORA ({$row['debt_months']} Meses)";
            $badge_color = 'bg-orange-600';
        }
        // C. Prioridad Baja: Cortado
        else if ($row['service_status'] === 'CORTADO') {
            $color = 'purple';
            $badge_color = 'bg-gray-600';
        }
        // D. Ticket Normal
        else if ($row['open_tickets'] > 0) {
            $color = 'yellow';
            $status_text = "TICKET ABIERTO";
            $badge_color = 'bg-yellow-600';
        }

        // Agregar al resultado
        $row['initials'] = $initials;
        $row['color'] = $color; // Esto es lo que lee el JS para pintar el borde
        $row['badge_color'] = $badge_color;
        $row['status_text'] = $status_text;
        
        // Flags útiles para JS
        $row['has_urgent_ticket'] = ($row['urgent_tickets'] > 0);
        $row['has_debt'] = ($row['debt_months'] >= 3);

        $results[] = $row;
    }

    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>