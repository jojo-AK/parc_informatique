<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Récupérer les utilisateurs
$sql = "SELECT * FROM utilisateur ORDER BY nom, prenom";
$stmt = $pdo->query($sql);
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Petites stats
$totalUsers  = count($utilisateurs);
$withEmail   = 0;
$withPhone   = 0;
$services    = [];

foreach ($utilisateurs as $u) {
    if (!empty($u["email"])) {
        $withEmail++;
    }
    if (!empty($u["telephone"])) {
        $withPhone++;
    }
    if (!empty($u["service"])) {
        $services[] = $u["service"];
    }
}
$servicesUniques = count(array_unique($services));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Utilisateurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Police + icônes -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS principal -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <span class="logo-icon"><i class="fa-solid fa-laptop-code"></i></span>
            <span class="logo-text">Parc IT</span>
        </div>

        <nav class="menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="materiels_list.php" class="menu-item">
                <i class="fa-solid fa-computer"></i>
                <span>Matériels</span>
            </a>
            <a href="categories_list.php" class="menu-item">
                <i class="fa-solid fa-layer-group"></i>
                <span>Catégories</span>
            </a>
            <a href="utilisateurs_list.php" class="menu-item active">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="affectations_list.php" class="menu-item">
                <i class="fa-solid fa-people-arrows"></i>
                <span>Affectations</span>
            </a>
            <a href="pannes_list.php" class="menu-item">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Pannes</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <div class="main">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn-toggle-sidebar" id="btn-toggle-sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-title">
                <h1>Gestion des utilisateurs</h1>
                <p>Annuaire des collaborateurs du parc informatique</p>
            </div>

            <div class="topbar-actions">
                <button class="btn-mode" id="btn-mode">
                    <i class="fa-solid fa-moon"></i>
                    <span>Mode sombre</span>
                </button>
            </div>
        </header>

        <main class="content">
            <!-- Cartes de stats -->
            <section class="cards">
                <div class="card card-gradient-1">
                    <div class="card-header">
                        <span>Utilisateurs</span>
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalUsers ?></h2>
                        <p>Collaborateurs enregistrés</p>
                    </div>
                </div>

                <div class="card card-gradient-2">
                    <div class="card-header">
                        <span>Avec contact</span>
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$withEmail ?> / <?= (int)$withPhone ?></h2>
                        <p>Avec email / téléphone</p>
                    </div>
                </div>

                <div class="card card-gradient-3">
                    <div class="card-header">
                        <span>Services</span>
                        <i class="fa-solid fa-building-user"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$servicesUniques ?></h2>
                        <p>Services différents</p>
                    </div>
                </div>
            </section>

            <!-- Panel principal -->
            <section class="panel" style="margin-top: 1rem;">
                <div class="panel-header">
                    <h2>Liste des utilisateurs</h2>
                    <a href="utilisateurs_add.php" class="btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
                    </a>
                </div>

                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Service</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($utilisateurs)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:1rem;">
                                        Aucun utilisateur enregistré pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($utilisateurs as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u["id_utilisateur"]) ?></td>
                                        <td><?= htmlspecialchars($u["nom"]) ?></td>
                                        <td><?= htmlspecialchars($u["prenom"]) ?></td>
                                        <td><?= htmlspecialchars($u["service"] ?? "") ?></td>
                                        <td><?= htmlspecialchars($u["email"] ?? "") ?></td>
                                        <td><?= htmlspecialchars($u["telephone"] ?? "") ?></td>
                                        <td>
                                            <!-- Plus tard : bouton modifier -->
                                            <a href="utilisateurs_delete.php?id=<?= urlencode($u["id_utilisateur"]) ?>"
                                               class="btn-table btn-danger"
                                               title="Supprimer"
                                               onclick="return confirm('Supprimer cet utilisateur ?');">
                                                <i class="fa-solid fa-user-xmark"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Bouton flottant -->
            <button class="fab" onclick="window.location.href='utilisateurs_add.php'">
                <i class="fa-solid fa-user-plus"></i>
            </button>
        </main>
    </div>
</div>

<!-- JS principal -->
<script src="../assets/js/app.js"></script>
</body>
</html>
