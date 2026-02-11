<?php
// ARCHIVO: admin/api/auth/login_dummy.php
// ¡SOLO PARA PRUEBAS! SIMULA EL LOGIN DEL ADMIN (ID 1)
session_start();
$_SESSION['admin_id'] = 1; 
$_SESSION['role'] = 'gerencia';
$_SESSION['name'] = 'Super Admin';

echo "Sesión de Admin (ID 1) iniciada. Puedes probar la API.";
?>