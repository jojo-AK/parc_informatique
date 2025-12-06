<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'admin n'est pas connecté, on redirige vers la page de login
if (empty($_SESSION["admin_logged_in"])) {
    header("Location: ../login.php");
    exit;
}
