<?php
// Aumentar el tiempo de ejecución por si el internet del servidor va lento
ini_set('max_execution_time', 300);

echo "<h1>Instalador Automático de PhpSpreadsheet</h1>";
echo "<pre>";

// 1. Verificar si podemos ejecutar comandos
if (!function_exists('shell_exec')) {
    die("❌ Error: La función shell_exec está desactivada en tu hosting. Debes usar la Terminal de DirectAdmin.");
}

// 2. Descargar Composer (el gestor)
echo "1. Descargando Composer...\n";
$download = shell_exec('php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"');
echo "   Descarga completada.\n";

// 3. Instalar Composer localmente
echo "2. Instalando Composer...\n";
$install = shell_exec('php composer-setup.php');
echo $install . "\n";

// 4. Ejecutar la instalación de la librería
echo "3. Instalando la librería de Excel (Esto puede tardar unos segundos)...\n";
// Usamos 'yes' para aceptar cualquier pregunta automática
$libreria = shell_exec('php composer.phar require phpoffice/phpspreadsheet 2>&1');
echo $libreria . "\n";

// 5. Limpieza
echo "4. Limpiando archivos temporales...\n";
shell_exec('rm composer-setup.php');
shell_exec('rm composer.phar');

echo "</pre>";
echo "<h2>✅ Si ves 'Writing lock file' o 'Generating autoload files', ¡YA ESTÁ INSTALADO!</h2>";
?>