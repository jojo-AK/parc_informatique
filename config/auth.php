<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie simplement qu'un utilisateur est connecté (admin OU viewer)
if (empty($_SESSION["logged_in"])) {
    header("Location: ../login.php");
    exit;
}
