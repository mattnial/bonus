<?php
// ARCHIVO: api/admin/generate_document.php
// VERSIÓN CORREGIDA: Compatible con la tabla TICKETS real (created_by_staff_id, cedula, title)

header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 0); // Ocultar errores en producción, ver logs si falla
// VERSIÓN v3
include_once '../../config/database.php';

// ... (Resto del código)

    // 5. PROCESAR WORD
    // En v3, api/admin está a 2 niveles del root. 
    // root/assets/templates está en ../../assets/templates
    $templatePath = "../../assets/templates/" . $templateName;
    if (!file_exists($templatePath)) throw new Exception("Falta la plantilla: $templateName");

    $tempFile = tempnam(sys_get_temp_dir(), 'DocGen');
    copy($templatePath, $tempFile);

    $zip = new ZipArchive;
    if ($zip->open($tempFile) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (preg_match('/word\/(document|header\d+|footer\d+)\.xml/', $filename)) {
                $xml = $zip->getFromName($filename);
                foreach ($vars as $key => $val) {
                    $xml = str_replace($key, $val, $xml);
                }
                $zip->addFromString($filename, $xml);
            }
        }
        $zip->close();

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=\"{$doc_type}_{$client['cedula']}.docx\"");
        header("Content-Length: " . filesize($tempFile));
        readfile($tempFile);
        unlink($tempFile);
    } else {
        throw new Exception("Error al abrir la plantilla.");
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// --- FUNCIONES AUXILIARES ---

function getNextSequence($db, $type, $address, $manualBranch = '') {
    if (!empty($manualBranch)) {
        $branch = $manualBranch;
    } else {
        $branch = detectSucursalCode($address);
    }

    if ($branch == 'VILCABAMBA') $branch = 'VILCA';
    if ($branch == 'PALANDA') $branch = 'PALAN';

    $stmt = $db->prepare("SELECT * FROM document_sequences WHERE doc_type = ? AND branch = ? FOR UPDATE");
    $stmt->execute([$type, $branch]);
    $seq = $stmt->fetch(PDO::FETCH_ASSOC);

    $newVal = 1; 
    $prefix = substr($type, 0, 3).'-'; 

    if ($seq) {
        $newVal = $seq['current_value'] + 1;
        if(!empty($seq['prefix'])) $prefix = $seq['prefix'];
    } else {
        $db->prepare("INSERT INTO document_sequences (doc_type, branch, current_value, prefix) VALUES (?, ?, 1, ?)")->execute([$type, $branch, $prefix]);
    }
    
    $db->prepare("UPDATE document_sequences SET current_value = ? WHERE doc_type = ? AND branch = ?")->execute([$newVal, $type, $branch]);
    
    return $branch . "-" . $prefix . str_pad($newVal, 6, "0", STR_PAD_LEFT);
}

// --- FUNCIÓN CORREGIDA PARA TICKETS ---
function createAutomaticTicket($db, $clientId, $clientCedula, $type, $staffId, $code, $reason, $extraData) {
    // 1. Asunto y Título (Ambos requeridos)
    $asunto = "SOLICITUD DE " . str_replace('_', ' ', $type);
    
    // 2. Descripción
    $descripcion = "Ticket generado automáticamente tras la creación del documento $code.\n";
    if (!empty($reason)) $descripcion .= "Motivo: $reason\n";
    if (!empty($extraData['new_address'])) $descripcion .= "Nueva Dirección: " . $extraData['new_address'] . "\n";
    if (!empty($extraData['target_plan'])) $descripcion .= "Plan Nuevo: " . $extraData['target_plan'] . "\n";

    // 3. Insertar con TODOS los campos obligatorios de tu tabla
    // client_cedula, title, department, created_by_staff_id
    $sql = "INSERT INTO tickets (client_id, client_cedula, title, subject, description, department, status, priority, created_at, created_by_staff_id) 
            VALUES (?, ?, ?, ?, ?, 'VENTAS', 'PENDIENTE_CLIENTE', 'MEDIA', NOW(), ?)";
            
    $stmt = $db->prepare($sql);
    // Ejecutamos con los parámetros correctos
    $stmt->execute([
        $clientId, 
        $clientCedula, 
        $asunto,      // title
        $asunto,      // subject
        $descripcion, 
        $staffId      // created_by_staff_id (NO created_by)
    ]);
}

function getFechaEspanol() {
    $meses = ["","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    return date('d') . " de " . $meses[date('n')] . " del " . date('Y');
}

function detectSucursalCode($addr) {
    $addr = strtoupper($addr);
    if(strpos($addr, 'PALANDA')!==false) return "PALAN";
    if(strpos($addr, 'LOJA')!==false) return "LOJA";
    return "VILCA"; 
}
?>