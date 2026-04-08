<?php
/**
 * Script d'initialisation de la base SQLite.
 * A exécuter une seule fois : php config/init_sqlite.php
 */

$dbPath = __DIR__ . "/../database/parc_informatique.db";

if (!is_dir(__DIR__ . "/../database")) {
    mkdir(__DIR__ . "/../database", 0755, true);
}

$pdo = new PDO("sqlite:" . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA foreign_keys = ON;");

$pdo->exec("
CREATE TABLE IF NOT EXISTS categorie (
    id_categorie   INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_categorie  TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS materiel (
    id_materiel      INTEGER PRIMARY KEY AUTOINCREMENT,
    code_inventaire  TEXT NOT NULL UNIQUE,
    designation      TEXT NOT NULL,
    id_categorie     INTEGER,
    marque           TEXT,
    modele           TEXT,
    date_acquisition TEXT,
    etat             TEXT NOT NULL DEFAULT 'disponible',
    localisation     TEXT,
    FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie)
);

CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur INTEGER PRIMARY KEY AUTOINCREMENT,
    nom            TEXT NOT NULL,
    prenom         TEXT NOT NULL,
    service        TEXT,
    email          TEXT,
    telephone      TEXT
);

CREATE TABLE IF NOT EXISTS affectation (
    id_affectation   INTEGER PRIMARY KEY AUTOINCREMENT,
    id_materiel      INTEGER NOT NULL,
    id_utilisateur   INTEGER NOT NULL,
    date_affectation TEXT NOT NULL,
    date_retour      TEXT,
    commentaire      TEXT,
    FOREIGN KEY (id_materiel)    REFERENCES materiel(id_materiel),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE IF NOT EXISTS panne_maintenance (
    id_panne        INTEGER PRIMARY KEY AUTOINCREMENT,
    id_materiel     INTEGER NOT NULL,
    date_panne      TEXT NOT NULL,
    description     TEXT NOT NULL,
    statut          TEXT NOT NULL DEFAULT 'en_cours',
    date_resolution TEXT,
    FOREIGN KEY (id_materiel) REFERENCES materiel(id_materiel)
);
");

echo "Base SQLite initialisee avec succes : $dbPath" . PHP_EOL;
