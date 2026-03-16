<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Rôle courant (admin ou viewer)
$role = $_SESSION["user_role"] ?? "admin";
$isViewer = ($role === "viewer");

// Récupérer les catégories pour le filtre
$sqlCat = "SELECT id_categorie, nom_categorie FROM categorie ORDER BY nom_categorie";
$stmtCat = $pdo->query($sqlCat);
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les filtres (GET)
$q            = trim($_GET["q"] ?? "");
$filtreEtat   = $_GET["etat"] ?? "";
$filtreCat    = $_GET["id_categorie"] ?? "";

// Compter combien de filtres sont actifs (pour affichage)
$nbFiltresActifs = 0;
if ($q !== "")          $nbFiltresActifs++;
if ($filtreEtat !== "") $nbFiltresActifs++;
if ($filtreCat !== "")  $nbFiltresActifs++;

// Construire la requête avec conditions dynamiques
$sql = "SELECT m.id_materiel,
               m.code_inventaire,
               m.designation,
               m.etat,
               m.localisation,
               c.nom_categorie
        FROM materiel m
        JOIN categorie c ON m.id_categorie = c.id_categorie";

$conditions = [];
$params = [];

// Filtre texte (code ou désignation)
if ($q !== "") {
    $conditions[] = "(m.code_inventaire LIKE :q OR m.designation LIKE :q)";
    $params[":q"] = "%".$q."%";
}

// Filtre état
if ($filtreEtat !== "") {
    $conditions[] = "m.etat = :etat";
    $params[":etat"] = $filtreEtat;
}

// Filtre catégorie
if ($filtreCat !== "") {
    $conditions[] = "m.id_categorie = :id_categorie";
    $params[":id_categorie"] = $filtreCat;
}

// Ajouter WHERE si nécessaire
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY m.id_materiel DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materiels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques rapides
$totalMateriel   = count($materiels);
$totalDispo      = 0;
$totalAffecte    = 0;
$totalPanne      = 0;
$totalMaint      = 0;
$totalHorsServ   = 0;

foreach ($materiels as $m) {
    switch ($m["etat"]) {
        case "disponible":
            $totalDispo++;
            break;
        case "affecte":
            $totalAffecte++;
            break;
        case "panne":
            $totalPanne++;
            break;
        case "maintenance":
            $totalMaint++;
            break;
        case "hors_service":
            $totalHorsServ++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Parc Informatique - Matériels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Police Google -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Icones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            <?php if (!$isViewer): ?>
                <a href="dashboard.php" class="menu-item">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <a href="materiels_list.php" class="menu-item active">
                <i class="fa-solid fa-computer"></i>
                <span>Matériels</span>
            </a>

            <?php if (!$isViewer): ?>
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
                <a href="pannes_list.php" class="menu-item">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Pannes</span>
                </a>
            <?php endif; ?>

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
                <h1>Matériels</h1>
                <p>Gestion et suivi de l'inventaire du parc</p>
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
                        <span>Total matériels</span>
                        <i class="fa-solid fa-computer"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalMateriel ?></h2>
                        <p>Matériels trouvés avec ce filtre</p>
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
                        <span>Affectés / Incidents</span>
                        <i class="fa-solid fa-plug-circle-exclamation"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= (int)$totalAffecte ?> / <?= (int)($totalPanne + $totalMaint + $totalHorsServ) ?></h2>
                        <p>Affectés & matériels en panne / maintenance / HS</p>
                    </div>
                </div>
            </section>

            <!-- Filtres + bouton ajouter -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Filtrer les matériels</h2>
                    <?php if (!$isViewer): ?>
                        <a href="materiels_add.php" class="btn-primary">
                            <i class="fa-solid fa-plus"></i> Nouveau matériel
                        </a>
                    <?php endif; ?>
                </div>

                <div class="panel-body">
                    <form method="get" class="filters">
                        <input type="text"
                               name="q"
                               placeholder="Rechercher (code ou désignation)"
                               value="<?= htmlspecialchars($q) ?>">

                        <select name="etat">
                            <option value="">-- Tous les états --</option>
                            <option value="disponible"   <?= $filtreEtat === "disponible"   ? "selected" : "" ?>>Disponible</option>
                            <option value="affecte"      <?= $filtreEtat === "affecte"      ? "selected" : "" ?>>Affecté</option>
                            <option value="panne"        <?= $filtreEtat === "panne"        ? "selected" : "" ?>>Panne</option>
                            <option value="maintenance"  <?= $filtreEtat === "maintenance"  ? "selected" : "" ?>>Maintenance</option>
                            <option value="hors_service" <?= $filtreEtat === "hors_service" ? "selected" : "" ?>>Hors service</option>
                        </select>

                        <select name="id_categorie">
                            <option value="">-- Toutes les catégories --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat["id_categorie"] ?>"
                                    <?= ($filtreCat == $cat["id_categorie"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($cat["nom_categorie"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-filter"></i> Filtrer
                        </button>
                        <a href="materiels_list.php" class="btn-mode">
                            Réinitialiser
                        </a>
                    </form>

                    <?php if ($nbFiltresActifs > 0): ?>
                        <p style="font-size:.78rem; margin-top:.4rem; color:rgba(209,213,219,0.9);">
                            <i class="fa-solid fa-sliders" style="margin-right:.3rem;"></i>
                            <?= $nbFiltresActifs ?> filtre<?= $nbFiltresActifs > 1 ? "s" : "" ?> appliqué<?= $nbFiltresActifs > 1 ? "s" : "" ?>.
                        </p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Tableau des matériels -->
            <section class="panel" style="margin-top: 1rem;">
                <div class="panel-header">
                    <h2>Liste des matériels</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Désignation</th>
                                <th>Catégorie</th>
                                <th>État</th>
                                <th>Localisation</th>
                                <?php if (!$isViewer): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($materiels)): ?>
                                <tr>
                                    <td colspan="<?= $isViewer ? 5 : 6 ?>" style="text-align: center; padding: 1rem;">
                                        Aucun matériel trouvé avec ces critères.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($materiels as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m["code_inventaire"]) ?></td>
                                        <td><?= htmlspecialchars($m["designation"]) ?></td>
                                        <td><?= htmlspecialchars($m["nom_categorie"]) ?></td>
                                        <td>
                                            <?php
                                            $etat = $m["etat"];
                                            $label = htmlspecialchars($etat);
                                            $badgeClass = "badge-warning";

                                            if ($etat === "disponible") {
                                                $badgeClass = "badge-success";
                                                $label = "Disponible";
                                            } elseif ($etat === "affecte") {
                                                $badgeClass = "badge-warning";
                                                $label = "Affecté";
                                            } elseif ($etat === "panne") {
                                                $badgeClass = "badge-danger";
                                                $label = "Panne";
                                            } elseif ($etat === "maintenance") {
                                                $badgeClass = "badge-warning";
                                                $label = "Maintenance";
                                            } elseif ($etat === "hors_service") {
                                                $badgeClass = "badge-danger";
                                                $label = "Hors service";
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($m["localisation"] ?? "") ?></td>

                                        <?php if (!$isViewer): ?>
                                            <td>
                                                <a href="materiels_edit.php?id=<?= urlencode($m["id_materiel"]) ?>" class="btn-table" title="Modifier">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="materiels_delete.php?id=<?= urlencode($m["id_materiel"]) ?>"
                                                   class="btn-table btn-danger"
                                                   title="Supprimer"
                                                   onclick="return confirm('Supprimer ce matériel ?');">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Bouton flottant -->
            <?php if (!$isViewer): ?>
                <button class="fab" onclick="window.location.href='materiels_add.php'">
                    <i class="fa-solid fa-plus"></i>
                </button>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- JS principal -->
<script src="../assets/js/app.js"></script>
</body>
</html>
