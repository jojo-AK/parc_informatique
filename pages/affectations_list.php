<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Requête pour récupérer les affectations avec infos matériel + utilisateur
$sql = "SELECT a.id_affectation,
               a.date_affectation,
               a.date_retour,
               a.commentaire,
               m.id_materiel,
               m.code_inventaire,
               m.designation,
               u.nom,
               u.prenom
        FROM affectation a
        JOIN materiel m ON a.id_materiel = m.id_materiel
        JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
        ORDER BY a.date_affectation DESC, a.id_affectation DESC";

$stmt = $pdo->query($sql);
$affectations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Petites stats pour les cartes
$totalAffect = count($affectations);
$enCours     = 0;
$retournees  = 0;

foreach ($affectations as $a) {
    if (empty($a["date_retour"])) {
        $enCours++;
    } else {
        $retournees++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Affectations - Parc Informatique</title>
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
            <a href="dashboard.php" class="menu-item">
                <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="materiels_list.php" class="menu-item">
                <i class="fa-solid fa-computer"></i><span>Matériels</span>
            </a>
            <a href="affectations_list.php" class="menu-item active">
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
                <h1>Affectations</h1>
                <p>Suivi des matériels affectés aux utilisateurs</p>
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

            <!-- Cartes de stats -->
            <section class="cards">
                <div class="card card-gradient-1">
                    <div class="card-header">
                        <span>Total affectations</span>
                        <i class="fa-solid fa-people-arrows"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalAffect ?></h2>
                        <p>Toutes affectations enregistrées</p>
                    </div>
                </div>

                <div class="card card-gradient-2">
                    <div class="card-header">
                        <span>En cours</span>
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$enCours ?></h2>
                        <p>Affectations actives (non retournées)</p>
                    </div>
                </div>

                <div class="card card-gradient-3">
                    <div class="card-header">
                        <span>Retournées</span>
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$retournees ?></h2>
                        <p>Affectations clôturées</p>
                    </div>
                </div>
            </section>

            <!-- Panel liste -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Liste des affectations</h2>
                    <a href="affectations_add.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Nouvelle affectation
                    </a>
                </div>

                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                <th>Matériel</th>
                                <th>Utilisateur</th>
                                <th>Date affectation</th>
                                <th>État / Retour</th>
                                <th>Commentaire</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($affectations)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:1rem;">
                                        Aucune affectation enregistrée pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($affectations as $a): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($a["code_inventaire"] ?? "") ?>
                                            – <?= htmlspecialchars($a["designation"] ?? "") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($a["nom"] ?? "") ?>
                                            <?= htmlspecialchars($a["prenom"] ?? "") ?>
                                        </td>

                                        <td><?= htmlspecialchars($a["date_affectation"] ?? "") ?></td>

                                        <td>
                                            <?php
                                            if (empty($a["date_retour"])) {
                                                // Affectation en cours
                                                $badgeClass = "badge-warning";
                                                $labelEtat  = "En cours";
                                                $details    = "Non retourné";
                                            } else {
                                                $badgeClass = "badge-success";
                                                $labelEtat  = "Retourné";
                                                $details    = "Retour le " . htmlspecialchars($a["date_retour"]);
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $labelEtat ?></span>
                                            <div style="font-size:.7rem; color:rgba(209,213,219,0.85); margin-top:.15rem;">
                                                <?= $details ?>
                                            </div>
                                        </td>

                                        <td style="max-width:260px;">
                                            <?= nl2br(htmlspecialchars($a["commentaire"] ?? "")) ?>
                                        </td>

                                        <td>
                                            <?php if (empty($a["date_retour"])): ?>
                                                <a href="affectations_retour.php?id=<?= urlencode($a["id_affectation"]) ?>"
                                                   class="btn-table"
                                                   title="Marquer comme retourné">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="affectations_delete.php?id=<?= urlencode($a["id_affectation"]) ?>"
                                               class="btn-table btn-danger"
                                               title="Supprimer"
                                               onclick="return confirm('Supprimer cette affectation ?');">
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

            <!-- FAB pour nouvelle affectation -->
            <button class="fab" onclick="window.location.href='affectations_add.php'">
                <i class="fa-solid fa-plus"></i>
            </button>

        </main>
    </div>
</div>

<!-- JS -->
<script src="../assets/js/app.js"></script>
</body>
</html>
