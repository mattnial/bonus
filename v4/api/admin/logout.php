<?php
session_start();
session_destroy(); // Destruye el pase
header("Location: /login.html"); // Manda al login
exit;
?>