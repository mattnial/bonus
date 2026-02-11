<?php
session_start();

// Si no hay variable de sesión 'staff_id', fuera.
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}
?>