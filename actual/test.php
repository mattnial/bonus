<?php
// ARCHIVO: public_html/diagnostico.php
// Forzar visualización de errores al máximo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🩺 Diagnóstico de Conexión MySQL</h1>";

// TUS DATOS EXACTOS (tal cual dices que están bien)
$host = "localhost";
$db_name = "vilcanet_bonus";
$username = "vilcanet_bonus";
$password = "W8JNZXGAGgtfHKYnkPsr"; // <--- LA QUE DICES QUE ESTÁ BIEN

echo "<p>Intentando conectar a: <strong>$db_name</strong> con usuario <strong>$username</strong>...</p>";

try {
    // Intento básico con PDO
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2 style='color:green'>✅ ¡CONEXIÓN EXITOSA!</h2>";
    echo "<p>La base de datos responde correctamente.</p>";
    
    // Prueba de lectura de tablas
    echo "<h3>Verificando tablas críticas:</h3>";
    $tablas = ['clients', 'tickets', 'staff'];
    
    echo "<ul>";
    foreach($tablas as $tabla) {
        try {
            $conn->query("SELECT 1 FROM $tabla LIMIT 1");
            echo "<li style='color:green'>Tabla <strong>$tabla</strong>: OK</li>";
        } catch (Exception $e) {
            echo "<li style='color:red'>Tabla <strong>$tabla</strong>: NO EXISTE (Error: ".$e->getMessage().")</li>";
        }
    }
    echo "</ul>";

} catch(PDOException $e) {
    echo "<h2 style='color:red'>❌ ERROR FATAL DE CONEXIÓN</h2>";
    echo "<p>El servidor dice:</p>";
    echo "<pre style='background:#fdd; padding:10px; border:1px solid red;'>" . $e->getMessage() . "</pre>";
    
    echo "<h3>Posibles Causas Reales:</h3>";
    echo "<ul>";
    echo "<li><strong>Access denied:</strong> La clave está mal O el usuario no tiene permisos.</li>";
    echo "<li><strong>Unknown database:</strong> El nombre de la base de datos está mal escrito.</li>";
    echo "<li><strong>Connection refused:</strong> El servidor de base de datos está apagado o bloqueado.</li>";
    echo "</ul>";
}
?>