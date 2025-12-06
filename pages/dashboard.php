<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Compteurs principaux
$totalMateriel      = $pdo->query("SELECT COUNT(*) FROM materiel")->fetchColumn();
$totalDispo         = $pdo->query("SELECT COUNT(*) FROM materiel WHERE etat = 'disponible'")->fetchColumn();
$totalAffecte       = $pdo->query("SELECT COUNT(*) FROM materiel WHERE etat = 'affecte'")->fetchColumn();
$totalPanne         = $pdo->query("SELECT COUNT(*) FROM materiel WHERE etat = 'panne'")->fetchColumn();
$totalMaintenance   = $pdo->query("SELECT COUNT(*) FROM materiel WHERE etat = 'maintenance'")->fetchColumn();
$totalHorsService   = $pdo->query("SELECT COUNT(*) FROM materiel WHERE etat = 'hors_service'")->fetchColumn();

$affectationsActives = $pdo->query("SELECT COUNT(*) FROM affectation WHERE date_retour IS NULL")->fetchColumn();
$pannesEnCours       = $pdo->query("SELECT COUNT(*) FROM panne_maintenance WHERE statut = 'en_cours'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Parc informatique</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Police + icônes -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS vif & moderne -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <span class="logo-icon"><i class="fa-solid fa-laptop-code"></i></span>
            <span class="logo-text">Parc IT</span>
        </div>

        <nav class="menu">
            <a href="dashboard.php" class="menu-item active">
                <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="materiels_list.php" class="menu-item">
                <i class="fa-solid fa-computer"></i><span>Matériels</span>
            </a>
            <a href="affectations_list.php" class="menu-item">
                <i class="fa-solid fa-people-arrows"></i><span>Affectations</span>
            </a>
            <a href="pannes_list.php" class="menu-item">
                <i class="fa-solid fa-triangle-exclamation"></i><span>Pannes</span>
            </a>
            <a href="categories_list.php" class="menu-item">
                <i class="fa-solid fa-layer-group"></i><span>Catégories</span>
            </a>
            <a href="utilisateurs_list.php" class="menu-item">
                <i class="fa-solid fa-users"></i><span>Utilisateurs</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="main">
        <!-- TOPBAR -->
        <header class="topbar">
            <button class="btn-toggle-sidebar" id="btn-toggle-sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-title">
                <h1>Tableau de bord</h1>
                <p>Vue d’ensemble du parc informatique</p>
            </div>

            <div class="topbar-actions">
                <button class="btn-mode" id="btn-mode">
                    <i class="fa-solid fa-moon"></i>
                    <span>Mode sombre</span>
                </button>
            </div>
        </header>

        <!-- CONTENU -->
        <main class="content">

            <!-- Cartes principales Matériels -->
            <section class="cards">
                <div class="card card-gradient-1">
                    <div class="card-header">
                        <span>Matériel total</span>
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalMateriel ?></h2>
                        <p>Équipements enregistrés dans le parc</p>
                    </div>
                </div>

                <div class="card card-gradient-2">
                    <div class="card-header">
                        <span>Disponibles</span>
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalDispo ?></h2>
                        <p>Prêts à être affectés</p>
                    </div>
                </div>

                <div class="card card-gradient-3">
                    <div class="card-header">
                        <span>Affectés</span>
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalAffecte ?></h2>
                        <p>Actuellement en utilisation</p>
                    </div>
                </div>
            </section>

            <!-- Deuxième ligne de cartes -->
            <section class="cards">
                <div class="card card-gradient-1">
                    <div class="card-header">
                        <span>En panne</span>
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalPanne ?></h2>
                        <p>Matériels déclarés en panne</p>
                    </div>
                </div>

                <div class="card card-gradient-2">
                    <div class="card-header">
                        <span>Maintenance</span>
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalMaintenance ?></h2>
                        <p>En cours de maintenance</p>
                    </div>
                </div>

                <div class="card card-gradient-3">
                    <div class="card-header">
                        <span>Hors service</span>
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalHorsService ?></h2>
                        <p>À retirer ou remplacer</p>
                    </div>
                </div>
            </section>

            <!-- Panel Affectations / Pannes -->
            <section class="panel" style="margin-top:1.3rem;">
                <div class="panel-header">
                    <h2>Activité du parc</h2>
                    <div style="display:flex; gap:.5rem; align-items:center;">
                        <a href="affectations_list.php" class="btn-primary">
                            <i class="fa-solid fa-people-arrows"></i> Voir les affectations
                        </a>
                        <a href="pannes_list.php" class="btn-mode">
                            <i class="fa-solid fa-triangle-exclamation"></i> Voir les pannes
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:1rem; max-width:520px;">
                        <div style="
                            padding:.9rem 1rem;
                            border-radius:1rem;
                            border:1px solid rgba(56,189,248,0.7);
                            background: radial-gradient(circle at left, rgba(56,189,248,0.22), rgba(15,23,42,0.95));
                            color:#e0f2fe;
                            font-size:.86rem;
                        ">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.3rem;">
                                <span>Affectations actives</span>
                                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                            </div>
                            <div style="font-size:1.6rem; font-weight:600; letter-spacing:.05em;">
                                <?= (int)$affectationsActives ?>
                            </div>
                            <p style="margin-top:.2rem; font-size:.78rem; color:rgba(226,232,240,0.9);">
                                Matériels actuellement prêtés à des utilisateurs.
                            </p>
                        </div>

                        <div style="
                            padding:.9rem 1rem;
                            border-radius:1rem;
                            border:1px solid rgba(239,68,68,0.7);
                            background: radial-gradient(circle at left, rgba(239,68,68,0.27), rgba(15,23,42,0.95));
                            color:#fecaca;
                            font-size:.86rem;
                        ">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.3rem;">
                                <span>Pannes en cours</span>
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </div>
                            <div style="font-size:1.6rem; font-weight:600; letter-spacing:.05em;">
                                <?= (int)$pannesEnCours ?>
                            </div>
                            <p style="margin-top:.2rem; font-size:.78rem; color:#fee2e2;">
                                Incidents ouverts dans le parc (pannes / maintenance).</p>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<!-- JS -->
<script src="../assets/js/app.js"></script>
</body>
</html>
