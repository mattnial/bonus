<?php
// ARCHIVO: api/admin/generate_document.php
// VERSIÓN CORREGIDA: Compatible con la tabla TICKETS real (created_by_staff_id, cedula, title)

header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 0); // Ocultar errores en producción, ver logs si falla
include_once '../config/database.php';

// 1. RECIBIR DATOS
$client_id  = isset($_REQUEST['client_id']) ? intval($_REQUEST['client_id']) : 0;
$doc_type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$staff_id   = isset($_REQUEST['staff_id']) ? intval($_REQUEST['staff_id']) : 1;
$staff_name = isset($_REQUEST['staff_name']) ? strtoupper($_REQUEST['staff_name']) : 'ADMINISTRACION';

// SUCURSAL MANUAL
$manual_branch = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : ''; 

// Variables de Formulario (Limpieza básica)
$new_name    = isset($_REQUEST['new_name']) ? strtoupper($_REQUEST['new_name']) : "_________________";
$new_cedula  = isset($_REQUEST['new_cedula']) ? $_REQUEST['new_cedula'] : "_________________";
$new_address = isset($_REQUEST['new_address']) ? strtoupper($_REQUEST['new_address']) : "_________________";
$new_phone   = isset($_REQUEST['new_phone']) ? $_REQUEST['new_phone'] : "_________________";
$new_email   = isset($_REQUEST['new_email']) ? $_REQUEST['new_email'] : "_________________";
$reason      = isset($_REQUEST['reason']) ? $_REQUEST['reason'] : ""; 
$equipo_serie = isset($_REQUEST['serie']) ? $_REQUEST['serie'] : "SN-XXXXXXXX";
$start_date  = isset($_REQUEST['date_start']) ? $_REQUEST['date_start'] : date('d/m/Y');
$target_plan = isset($_REQUEST['target_plan']) ? $_REQUEST['target_plan'] : "";
$conn_type   = isset($_REQUEST['conn_type']) ? $_REQUEST['conn_type'] : "FIBRA";

// Variables Reubicación
$move_type_int = isset($_REQUEST['move_type_int']) ? $_REQUEST['move_type_int'] : "";
$new_address_reu = isset($_REQUEST['new_address_reu']) ? strtoupper($_REQUEST['new_address_reu']) : "";
$move_type_dom = isset($_REQUEST['move_type_dom']) ? $_REQUEST['move_type_dom'] : "";
$new_address_dom = isset($_REQUEST['new_address_dom']) ? strtoupper($_REQUEST['new_address_dom']) : "";

if ($client_id <= 0 || empty($doc_type)) die("Error: Faltan datos (ID o Tipo).");

try {
    $database = new Database();
    // Intentar obtener conexión, manejando diferentes versiones de la clase Database
    $db_bonus = method_exists($database, 'getBonusDB') ? $database->getBonusDB() : $database->getConnection();
    $db_crm   = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // Obtener Cliente
    $stmt = $db_bonus->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) throw new Exception("Cliente no encontrado en la base de datos.");

    // 2. GENERAR SECUENCIA
    $codigo_documento = getNextSequence($db_crm, $doc_type, $client['address'], $manual_branch);
    
    // 3. CREAR TICKET AUTOMÁTICO (CORREGIDO)
    $extraData = [
        'new_address' => ($doc_type == 'CAMBIO_DOMICILIO') ? $new_address_dom : $new_address,
        'target_plan' => $target_plan,
        'conn_type'   => $conn_type
    ];
    
    // Pasamos la Cédula del cliente que es obligatoria en tu tabla tickets
    createAutomaticTicket($db_crm, $client_id, $client['cedula'], $doc_type, $staff_id, $codigo_documento, $reason, $extraData);

    // 4. DICCIONARIO DE VARIABLES (WORD)
    $sucursal_code = !empty($manual_branch) ? $manual_branch : detectSucursalCode($client['address']);
    $sucursal_print = "VILCABAMBA"; // Default
    if ($sucursal_code == 'LOJA') $sucursal_print = "LOJA";
    if ($sucursal_code == 'PALAN') $sucursal_print = "PALANDA";

    $vars = [
        '{{SECUENCIA}}'      => $codigo_documento, 
        '{{FECHA_LARGA}}'    => getFechaEspanol(),
        '{{FECHA_HOY}}'      => date('d/m/Y'),
        '{{ANIO}}'           => date('Y'),
        '{{SUCURSAL}}'       => $sucursal_print,
        '{{NOMBRE_CLIENTE}}' => strtoupper($client['name']),
        '{{CEDULA}}'         => $client['cedula'],
        '{{DIRECCION}}'      => $client['address'],
        '{{CELULAR}}'        => $client['phone'],
        '{{EMAIL}}'          => $client['email'],
        '{{PLAN}}'           => strtoupper($client['service_plan'] ?? ''),
        
        '{{USUARIO}}'        => $staff_name,
        '{{USUARIO_SISTEMA}}'=> $staff_name,
        
        '{{NOMBRE_CLIENTE_NUEVO}}' => $new_name,
        '{{CEDULA_NUEVO}}'         => $new_cedula,
        '{{DIRECCION_NUEVO}}'      => $new_address,
        '{{CELULAR_NUEVO}}'        => $new_phone,
        '{{EMAIL_NUEVO}}'          => $new_email,
        '{{NUEVA_DIRECCION}}'      => $new_address_dom . " " . $new_address_reu,
        '{{FECHA_INICIO}}'         => $start_date,
        '{{MOTIVO}}'               => $reason,
        '{{SERIE}}'                => $equipo_serie,
        '{{EQUIPO}}'               => 'DECO M4',
    ];

    $templateName = "";

    // SELECCIÓN DE PLANTILLA
    switch ($doc_type) {
        case 'REUBICACION':
            $templateName = "CAMBIO DE LUGAR DE ONT O CABLE.docx";
            $vars['{{X_INTERNA}}'] = ($move_type_int == 'INTERNA') ? 'X' : ' ';
            $vars['{{X_EXTERNA}}'] = ($move_type_int == 'EXTERNA') ? 'X' : ' ';
            $vars['{{X_OTROS}}']   = ($move_type_int == 'OTRO') ? 'X' : ' ';
            break;

        case 'CAMBIO_DOMICILIO':
            $templateName = "CAMBIO DE DOMICILIO.docx";
            $vars['{{X_EXTERNA}}'] = "X";
            break;

        case 'CAMBIO_PLAN':
            $templateName = "CAMBIO DE ANCHO DE BANDA.docx";
            $planes = ['STANDAR', 'ESENCIAL', 'FAMILIAR', 'PLUS', 'PRO'];
            foreach($planes as $p) $vars["{{X_{$p}}}"] = " "; 
            foreach($planes as $p) if(strpos($target_plan, $p) !== false) $vars["{{X_{$p}}}"] = "X";
            $vars['{{X_RADIO}}'] = ($conn_type == 'RADIO') ? 'X' : ' ';
            $vars['{{X_FO}}']    = ($conn_type == 'FIBRA') ? 'X' : ' ';
            break;

        case 'CAMBIO_TITULAR': $templateName = "CAMBIO DE TITULAR VILCABAMBA.docx"; break;
        
        case 'PAUSA':
            $templateName = "SOLICITUD DE PAUSA DE SERVICIO DE INTERNET.docx";
            $type = strtoupper($client['client_type'] ?? 'RESIDENCIAL');
            $vars['{{X_RES}}']  = ($type=='RESIDENCIAL') ? 'X' : ' '; 
            $vars['{{X_CORP}}'] = ($type=='CORPORATIVO') ? 'X' : ' ';
            $vars['{{X_CYB}}']  = ' '; 
            break;

        case 'RETIRO':
            $templateName = "FINALIZACION DE CONTRATO.docx";
            $motivos = ['X_CAMBIO','X_CAMBIO2','X_ELEVADO','X_PESIMO','X_PESIMO2','X_FALLECIO','X_OTRO'];
            foreach($motivos as $m) $vars["{{{$m}}}"] = " ";
            $vars['{{X_OTRO}}'] = "X"; 
            break;

        case 'INSPECCION':
            $templateName = "SOLICITUD DE INSPECCION.docx";
            $vars['{{X_RES}}'] = 'X';
            break;

        case 'REACTIVACION': $templateName = "SOLICITUD DE REACTIVACION DE SERVICIO DE INTERNET.docx"; $vars['{{X_RES}}']='X'; break;
        case 'CERTIFICADO':  $templateName = "CERTIFICADO DE NO ADEUDO VILCANET.docx"; break;
        case 'SOPORTE':      $templateName = "SOLICITUD DE SOPORTE TECNICO.docx"; break;
        case 'CONTRATO':     $templateName = "FORMATO CONTRATO DE INTERNET 2024.docx"; break;

        default: throw new Exception("Tipo de documento desconocido: $doc_type");
    }

    // 5. PROCESAR WORD
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