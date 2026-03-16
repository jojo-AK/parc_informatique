# 🖥️ Parc IT — Gestion de Parc Informatique

> Application web de gestion de parc informatique — développée en PHP avec MySQL.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![Status](https://img.shields.io/badge/Status-Production-10b981?style=flat)
![License](https://img.shields.io/badge/License-MIT-6366f1?style=flat)

---

## 📌 À propos

**Parc IT** est une application web complète de gestion de parc informatique. Elle permet à une organisation de suivre l'ensemble de ses équipements informatiques : inventaire, affectations aux utilisateurs, suivi des pannes et de la maintenance, gestion des catégories et des utilisateurs.

L'application intègre un système d'authentification avec deux niveaux d'accès : **administrateur** (accès complet) et **viewer** (lecture seule).

---

## ✨ Fonctionnalités

| Module | Description |
|---|---|
| 🏠 **Tableau de bord** | Vue globale : compteurs par état, affectations actives, pannes en cours |
| 💻 **Matériels** | Inventaire complet avec filtres (état, catégorie, recherche texte) |
| 🔄 **Affectations** | Affectation de matériels aux utilisateurs, suivi des retours |
| ⚠️ **Pannes** | Déclaration et suivi des pannes/maintenances, résolution |
| 🗂️ **Catégories** | Gestion des catégories d'équipements |
| 👥 **Utilisateurs** | Gestion des utilisateurs du parc (nom, service, contact) |
| 🔐 **Authentification** | Deux rôles : Admin (CRUD complet) et Viewer (lecture seule) |

---

## 🔐 Accès et rôles

| Rôle | Identifiant | Mot de passe | Accès |
|---|---|---|---|
| 👑 Administrateur | `admin` | `admin123` | Lecture + écriture sur tout |
| 👤 Utilisateur | `user` | `user123` | Lecture seule (matériels) |

---

## 🛠️ Stack technique

| Composant | Technologie |
|---|---|
| Backend | PHP 8.x |
| Base de données | MySQL (via PDO) |
| Serveur local | WAMP / XAMPP / LAMP |
| Frontend | HTML5 + CSS3 + JavaScript vanilla |
| Icônes | Font Awesome 6.5 |
| Police | Google Fonts — Poppins |

---

## 🗄️ Structure de la base de données

```
materiel          → inventaire des équipements
categorie         → types d'équipements
utilisateur       → personnes du parc
affectation       → lien matériel ↔ utilisateur avec dates
panne_maintenance → incidents déclarés sur les matériels
```

**États possibles d'un matériel :** `disponible` · `affecte` · `panne` · `maintenance` · `hors_service`

---

## 🚀 Installation

### Prérequis
- WAMP / XAMPP / LAMP installé
- PHP 8.x
- MySQL

### Étapes

**1. Cloner le projet**
```bash
git clone https://github.com/TON_USERNAME/parc_informatique.git
```
Placer le dossier dans `www/` (WAMP) ou `htdocs/` (XAMPP).

**2. Créer la base de données**

Ouvrir phpMyAdmin et exécuter le script SQL fourni (`parc_informatique_db.sql`).

**3. Configurer la connexion**

Éditer `config/db.php` si nécessaire :
```php
$host   = "localhost";
$dbname = "parc_informatique_db";
$user   = "root";
$pass   = "";        // mot de passe vide par défaut sur WAMP
```

**4. Lancer l'application**

Ouvrir dans le navigateur :
```
http://localhost/parc_informatique/
```

---

## 📁 Structure du projet

```
parc_informatique/
├── index.php                     # Redirection vers le dashboard
├── login.php                     # Page de connexion
├── logout.php                    # Déconnexion
├── config/
│   ├── db.php                    # Connexion PDO à MySQL
│   ├── auth.php                  # Vérification : connecté (admin ou viewer)
│   └── auth_admin.php            # Vérification : admin uniquement
├── assets/
│   ├── css/style.css             # Styles de l'application (dark mode inclus)
│   └── js/app.js                 # Sidebar, dark mode, interactions
└── pages/
    ├── dashboard.php             # Tableau de bord
    ├── materiels_list.php        # Liste des matériels + filtres
    ├── materiels_add.php         # Ajout matériel
    ├── materiels_edit.php        # Modification matériel
    ├── materiels_delete.php      # Suppression matériel
    ├── affectations_list.php     # Liste des affectations
    ├── affectations_add.php      # Nouvelle affectation
    ├── affectations_delete.php   # Suppression affectation
    ├── affectations_retour.php   # Enregistrement du retour
    ├── pannes_list.php           # Liste des pannes
    ├── pannes_add.php            # Déclarer une panne
    ├── pannes_resoudre.php       # Résoudre une panne
    ├── pannes_delete.php         # Suppression panne
    ├── categories_list.php       # Liste des catégories
    ├── categories_add.php        # Ajout catégorie
    ├── categories_delete.php     # Suppression catégorie
    ├── utilisateurs_list.php     # Liste des utilisateurs
    ├── utilisateurs_add.php      # Ajout utilisateur
    └── utilisateurs_delete.php   # Suppression utilisateur
```

---

## 🗺️ Roadmap

- [x] Tableau de bord avec statistiques en temps réel
- [x] Gestion complète des matériels (CRUD + filtres)
- [x] Système d'affectation avec suivi des retours
- [x] Suivi des pannes et maintenances
- [x] Gestion des catégories et utilisateurs
- [x] Authentification avec deux niveaux de rôles
- [x] Interface responsive avec dark mode
- [ ] Export PDF / Excel de l'inventaire
- [ ] Historique complet des affectations par matériel
- [ ] Notifications pour pannes non résolues
- [ ] Gestion des contrats de maintenance

---

## 👤 Auteur

**Joseph** — Étudiant en informatique (ASI)

---

## 📄 Licence

Ce projet est sous licence MIT.
