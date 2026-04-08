-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 11 déc. 2025 à 11:29
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `parc_informatique_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `affectation`
--

DROP TABLE IF EXISTS `affectation`;
CREATE TABLE IF NOT EXISTS `affectation` (
  `id_affectation` int NOT NULL AUTO_INCREMENT,
  `id_materiel` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `date_affectation` date NOT NULL,
  `date_retour` date DEFAULT NULL,
  `commentaire` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_affectation`),
  KEY `fk_affectation_materiel` (`id_materiel`),
  KEY `fk_affectation_utilisateur` (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `affectation`
--

INSERT INTO `affectation` (`id_affectation`, `id_materiel`, `id_utilisateur`, `date_affectation`, `date_retour`, `commentaire`) VALUES
(3, 6, 1, '2025-12-19', NULL, 'ok'),
(2, 4, 3, '2005-12-05', NULL, 'ca marche');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_categorie`),
  UNIQUE KEY `nom_categorie` (`nom_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom_categorie`) VALUES
(1, 'PC portable'),
(2, 'PC bureau'),
(3, 'Serveur'),
(4, 'Imprimante'),
(5, 'Routeur'),
(6, 'Switch'),
(7, 'Console'),
(8, 'Serveur p');

-- --------------------------------------------------------

--
-- Structure de la table `materiel`
--

DROP TABLE IF EXISTS `materiel`;
CREATE TABLE IF NOT EXISTS `materiel` (
  `id_materiel` int NOT NULL AUTO_INCREMENT,
  `code_inventaire` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `designation` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `id_categorie` int NOT NULL,
  `marque` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modele` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_acquisition` date DEFAULT NULL,
  `etat` enum('disponible','affecte','panne','maintenance','hors_service') COLLATE utf8mb4_general_ci DEFAULT 'disponible',
  `localisation` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_materiel`),
  UNIQUE KEY `code_inventaire` (`code_inventaire`),
  KEY `fk_materiel_categorie` (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `materiel`
--

INSERT INTO `materiel` (`id_materiel`, `code_inventaire`, `designation`, `id_categorie`, `marque`, `modele`, `date_acquisition`, `etat`, `localisation`) VALUES
(1, 'PC-001', 'HP ProBook 450 G7', 1, 'HP', 'ProBook 450 G7', '2023-05-15', 'disponible', 'Bureau 101'),
(2, 'PC-002', 'Dell Latitude 5420', 1, 'Dell', 'Latitude 5420', '2023-06-10', 'disponible', 'Bureau 102'),
(3, 'SRV-001', 'Serveur Dell PowerEdge T40', 3, 'Dell', 'PowerEdge T40', '2022-03-01', 'disponible', 'Salle serveur'),
(4, '47554', 'dell', 2, 'hjkhlh', 'hhjkhii', '2025-12-10', 'affecte', 'lome'),
(5, 'PC-003', 'Lenovo ThinkPad', 1, 'Thinkpad', 'v5', '2025-12-03', 'disponible', 'lome'),
(6, 'PC-004', 'Laptop dell expiron', 1, 'dell', 'xps 13', '2025-12-19', 'affecte', 'salle B2');

-- --------------------------------------------------------

--
-- Structure de la table `panne_maintenance`
--

DROP TABLE IF EXISTS `panne_maintenance`;
CREATE TABLE IF NOT EXISTS `panne_maintenance` (
  `id_panne` int NOT NULL AUTO_INCREMENT,
  `id_materiel` int NOT NULL,
  `date_panne` date NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `statut` enum('en_cours','resolue') COLLATE utf8mb4_general_ci DEFAULT 'en_cours',
  `date_resolution` date DEFAULT NULL,
  PRIMARY KEY (`id_panne`),
  KEY `fk_panne_materiel` (`id_materiel`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `panne_maintenance`
--

INSERT INTO `panne_maintenance` (`id_panne`, `id_materiel`, `date_panne`, `description`, `statut`, `date_resolution`) VALUES
(1, 2, '2025-12-05', 'court_circuit', 'resolue', '2025-12-05'),
(2, 4, '2025-12-02', 'Panne enregistrée via modification du matériel.', 'resolue', '2025-12-02'),
(3, 5, '2025-12-02', 'Panne enregistrée via modification du matériel.', 'resolue', '2025-12-02'),
(4, 4, '2025-12-02', 'Panne enregistrée via modification du matériel.', 'resolue', '2025-12-02'),
(5, 4, '2025-12-02', 'Panne enregistrée via modification du matériel.', 'resolue', '2025-12-04');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `service` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telephone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `service`, `email`, `telephone`) VALUES
(1, 'AGOUTOCO', 'Caleb', 'IT', 'caleb@entreprise.com', '+22890000000'),
(2, 'DOSSOU', 'Yann', 'Comptabilite', 'yann@entreprise.com', '+22891000000'),
(3, 'AKPATCHA', 'Joseph', 'IT', 'kossi@gmail.com', '93256575');

COMMIT;


