<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autorise UNIQUEMENT les admins
if (empty($_SESSION["logged_in"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    // On peut soit renvoyer au login, soit à la page matériels
    header("Location: ../login.php");
    exit;
}
