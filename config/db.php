<?php
// Paramètres de connexion
$host = "localhost";              // WAMP en local
$dbname = "parc_informatique_db"; // Le nom de la base qu'on a créée
$user = "root";                   // Utilisateur par défaut de WAMP
$pass = "";                       // Mot de passe vide par défaut sur WAMP

try {
    // Construction de la chaîne de connexion (DSN)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);

    // On active le mode d'erreur pour voir les problèmes SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la connexion échoue, on arrête tout et on affiche l'erreur
    die("Erreur de connexion : " . $e->getMessage());
}
