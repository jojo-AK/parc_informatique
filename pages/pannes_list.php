<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Récupérer toutes les pannes avec infos matériel
$sql = "SELECT p.id_panne,
               p.date_panne,
               p.description,
               p.statut,
               p.date_resolution,
               m.id_materiel,
               m.code_inventaire,
               m.designation,
               m.etat AS etat_materiel
        FROM panne_maintenance p
        JOIN materiel m ON p.id_materiel = m.id_materiel
        ORDER BY p.date_panne DESC, p.id_panne DESC";

$stmt = $pdo->query($sql);
$pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$totalPannes    = count($pannes);
$totalEnCours   = 0;
$totalResolues  = 0;

foreach ($pannes as $p) {
    if ($p["statut"] === "en_cours") {
        $totalEnCours++;
    } elseif ($p["statut"] === "resolue" || $p["statut"] === "résolue") {
        $totalResolues++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des pannes</title>
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
            <a href="utilisateurs_list.php" class="menu-item">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="affectations_list.php" class="menu-item">
                <i class="fa-solid fa-people-arrows"></i>
                <span>Affectations</span>
            </a>
            <a href="pannes_list.php" class="menu-item active">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Pannes</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <div class="main">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn-toggle-sidebar" id="btn-toggle-sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-title">
                <h1>Gestion des pannes / maintenances</h1>
                <p>Suivi des incidents sur le parc informatique</p>
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
                        <span>Pannes totales</span>
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalPannes ?></h2>
                        <p>Incidents enregistrés</p>
                    </div>
                </div>

                <div class="card card-gradient-2">
                    <div class="card-header">
                        <span>En cours</span>
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalEnCours ?></h2>
                        <p>Pannes à traiter</p>
                    </div>
                </div>

                <div class="card card-gradient-3">
                    <div class="card-header">
                        <span>Résolues</span>
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalResolues ?></h2>
                        <p>Incidents clôturés</p>
                    </div>
                </div>
            </section>

            <!-- Panel principal -->
            <section class="panel" style="margin-top: 1rem;">
                <div class="panel-header">
                    <h2>Liste des pannes</h2>
                    <a href="pannes_add.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Déclarer une panne
                    </a>
                </div>

                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                <th>Matériel</th>
                                <th>Date panne</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th>Date résolution</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($pannes)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 1rem;">
                                        Aucune panne enregistrée pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pannes as $p): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($p["code_inventaire"] ?? "") ?>
                                            - <?= htmlspecialchars($p["designation"] ?? "") ?>
                                        </td>

                                        <td><?= htmlspecialchars($p["date_panne"] ?? "") ?></td>

                                        <td><?= nl2br(htmlspecialchars($p["description"] ?? "")) ?></td>

                                        <td>
                                            <?php
                                            $statut = $p["statut"] ?? "";
                                            $label = $statut;
                                            $badgeClass = "badge-warning";

                                            if ($statut === "en_cours") {
                                                $badgeClass = "badge-warning";
                                                $label = "En cours";
                                            } elseif ($statut === "resolue" || $statut === "résolue") {
                                                $badgeClass = "badge-success";
                                                $label = "Résolue";
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($label) ?></span>
                                        </td>

                                        <td><?= htmlspecialchars($p["date_resolution"] ?? "") ?></td>

                                        <td>
                                            <?php if ($p["statut"] === "en_cours"): ?>
                                                <a href="pannes_resoudre.php?id=<?= urlencode($p["id_panne"]) ?>"
                                                   class="btn-table"
                                                   title="Marquer comme résolue">
                                                    <i class="fa-solid fa-wrench"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="pannes_delete.php?id=<?= urlencode($p["id_panne"]) ?>"
                                               class="btn-table btn-danger"
                                               title="Supprimer"
                                               onclick="return confirm('Supprimer cette panne ?');">
                                                <i class="fa-solid fa-trash"></i>
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
            <button class="fab" onclick="window.location.href='pannes_add.php'">
                <i class="fa-solid fa-plus"></i>
            </button>
        </main>
    </div>
</div>

<!-- JS principal -->
<script src="../assets/js/app.js"></script>
</body>
</html>
