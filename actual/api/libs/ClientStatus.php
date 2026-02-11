<?php
// ARCHIVO: public_html/api/libs/ClientStatus.php

function determineClientStatus($client, $db) {
    // 1. OBTENER DATOS BÁSICOS
    // Usamos isset para evitar errores si el campo viene vacío
    $id = $client['id'];
    $status = isset($client['service_status']) ? $client['service_status'] : 'ACTIVO';
    $debt = isset($client['current_debt_months']) ? intval($client['current_debt_months']) : 0;
    
    // 2. CONSULTAR TICKETS Y CONVENIOS
    // Verificamos si existen las tablas antes de consultar para evitar crash
    $totalTickets = 0;
    $urgentTickets = 0;
    $hasAgreement = false;

    try {
        // Tickets
        $stmtT = $db->prepare("SELECT COUNT(*) as total, 
                               SUM(CASE WHEN priority = 'URGENTE' THEN 1 ELSE 0 END) as urgents 
                               FROM tickets WHERE client_id = ? AND status != 'CERRADO'");
        $stmtT->execute([$id]);
        $ticketData = $stmtT->fetch(PDO::FETCH_ASSOC);
        if($ticketData) {
            $totalTickets = intval($ticketData['total']);
            $urgentTickets = intval($ticketData['urgents']);
        }

        // Convenios
        $stmtC = $db->prepare("SELECT COUNT(*) FROM payment_agreements 
                               WHERE client_id = ? AND status = 'ACTIVO' AND promise_date >= CURDATE()");
        $stmtC->execute([$id]);
        $hasAgreement = $stmtC->fetchColumn() > 0;
        
    } catch (Exception $e) {
        // Si fallan las consultas (tablas no existen), asumimos todo en 0 para no romper el sistema
    }

    // 3. LA LÓGICA DEL SEMÁFORO (JERARQUÍA)

    // 🟣 MORADO: Corte (Gana a todo)
    if ($status === 'CORTADO' || $status === 'SUSPENDIDO') {
        return ['color' => 'purple', 'text' => 'CORTADO', 'hex' => '#9333ea', 'bg' => 'bg-purple-100', 'txt' => 'text-purple-700'];
    }

    // 🔴 ROJO: Urgente
    if ($urgentTickets > 0) {
        return ['color' => 'red', 'text' => 'URGENTE', 'hex' => '#dc2626', 'bg' => 'bg-red-100', 'txt' => 'text-red-700'];
    }

    // 🟠 NARANJA: Riesgo Corte (Deuda >= 2 y sin convenio)
    if ($debt >= 2 && !$hasAgreement) {
        return ['color' => 'orange', 'text' => "RIESGO ($debt MESES)", 'hex' => '#ea580c', 'bg' => 'bg-orange-100', 'txt' => 'text-orange-700'];
    }

    // 🔘 GRIS: Mora Leve
    if ($debt > 0) {
        return ['color' => 'gray', 'text' => 'PENDIENTE', 'hex' => '#6b7280', 'bg' => 'bg-gray-100', 'txt' => 'text-gray-700'];
    }

    // 🔵 AZUL: Saturación (Muchos tickets)
    if ($totalTickets > 1) {
        return ['color' => 'blue', 'text' => "$totalTickets CASOS", 'hex' => '#2563eb', 'bg' => 'bg-blue-100', 'txt' => 'text-blue-700'];
    }

    // 🟡 AMARILLO: En atención
    if ($totalTickets === 1) {
        return ['color' => 'yellow', 'text' => 'EN ATENCIÓN', 'hex' => '#ca8a04', 'bg' => 'bg-yellow-100', 'txt' => 'text-yellow-700'];
    }

    // 🟢 VERDE: Todo OK
    return ['color' => 'green', 'text' => 'ACTIVO', 'hex' => '#16a34a', 'bg' => 'bg-green-100', 'txt' => 'text-green-700'];
}
?>