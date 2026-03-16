<?php
require_once "../config/auth_admin.php";
require_once "../config/db.php";

// 1) Récupérer la liste des catégories pour la liste déroulante
$sqlCat = "SELECT id_categorie, nom_categorie FROM categorie ORDER BY nom_categorie";
$stmtCat = $pdo->query($sqlCat);
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$messageErreur = "";

// Valeurs par défaut pour “re-remplir” le formulaire en cas d’erreur
$code_inventaire  = "";
$designation      = "";
$id_categorie     = "";
$marque           = "";
$modele           = "";
$date_acquisition = "";
$etat             = "disponible";
$localisation     = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer les données envoyées par le formulaire
    $code_inventaire = trim($_POST["code_inventaire"] ?? "");
    $designation     = trim($_POST["designation"] ?? "");
    $id_categorie    = $_POST["id_categorie"] ?? "";
    $marque          = trim($_POST["marque"] ?? "");
    $modele          = trim($_POST["modele"] ?? "");
    $date_acquisition= $_POST["date_acquisition"] ?? "";
    $etat            = $_POST["etat"] ?? "disponible";
    $localisation    = trim($_POST["localisation"] ?? "");

    // Validation côté serveur
    if ($code_inventaire === "" || $designation === "" || $id_categorie === "") {
        $messageErreur = "Code inventaire, Désignation et Catégorie sont obligatoires.";
    } else {
        try {
            // Vérifier si la catégorie existe bien
            $sqlCheckCat = "SELECT COUNT(*) FROM categorie WHERE id_categorie = :id";
            $stmt = $pdo->prepare($sqlCheckCat);
            $stmt->execute([":id" => $id_categorie]);
            $catExiste = $stmt->fetchColumn();

            if (!$catExiste) {
                $messageErreur = "La catégorie sélectionnée n'existe pas.";
            } else {
                // Vérifier l'unicité du code inventaire
                $sqlCheckCode = "SELECT COUNT(*) FROM materiel WHERE code_inventaire = :code";
                $stmt = $pdo->prepare($sqlCheckCode);
                $stmt->execute([":code" => $code_inventaire]);
                $codeExiste = $stmt->fetchColumn();

                if ($codeExiste) {
                    $messageErreur = "Ce code d'inventaire existe déjà. Veuillez en choisir un autre.";
                } else {
                    // Tout est OK → insertion
                    $sqlInsert = "INSERT INTO materiel
                        (code_inventaire, designation, id_categorie, marque, modele, date_acquisition, etat, localisation)
                        VALUES
                        (:code_inventaire, :designation, :id_categorie, :marque, :modele, :date_acquisition, :etat, :localisation)";
                    
                    $stmt = $pdo->prepare($sqlInsert);
                    $stmt->execute([
                        ":code_inventaire" => $code_inventaire,
                        ":designation"     => $designation,
                        ":id_categorie"    => $id_categorie,
                        ":marque"          => $marque,
                        ":modele"          => $modele,
                        ":date_acquisition"=> ($date_acquisition !== "" ? $date_acquisition : null),
                        ":etat"            => $etat,
                        ":localisation"    => $localisation
                    ]);

                    header("Location: materiels_list.php");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $messageErreur = "Erreur lors de l'enregistrement du matériel : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un matériel</title>
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
            <a href="materiels_list.php" class="menu-item active">
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
                <h1>Ajouter un matériel</h1>
                <p>Enregistrer un nouvel équipement dans le parc</p>
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
            <section class="panel">
                <div class="panel-header">
                    <h2>Nouveau matériel</h2>
                    <a href="materiels_list.php" class="btn-primary">
                        <i class="fa-solid fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <div class="panel-body">

                    <?php if (!empty($messageErreur)): ?>
                        <div style="
                            margin-bottom: .9rem;
                            padding: .6rem .8rem;
                            border-radius: .8rem;
                            border: 1px solid rgba(239,68,68,0.7);
                            background: rgba(127,29,29,0.4);
                            color: #fecaca;
                            font-size: .82rem;
                        ">
                            <i class="fa-solid fa-circle-exclamation" style="margin-right:.4rem;"></i>
                            <?= htmlspecialchars($messageErreur) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="filters" style="flex-direction: column; align-items: stretch; gap: .9rem; max-width: 600px;">

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Code inventaire <span style="color:#f97373;">*</span>
                                </label>
                                <input type="text" name="code_inventaire" required
                                       value="<?= htmlspecialchars($code_inventaire) ?>"
                                       placeholder="Ex : LAPTOP-001, SRV-2024-03, etc.">
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Désignation <span style="color:#f97373;">*</span>
                                </label>
                                <input type="text" name="designation" required
                                       value="<?= htmlspecialchars($designation) ?>"
                                       placeholder="Ex : Laptop Dell XPS 13, Imprimante HP LaserJet…">
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Catégorie <span style="color:#f97373;">*</span>
                                </label>
                                <select name="id_categorie" required>
                                    <option value="">-- Choisir une catégorie --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat["id_categorie"] ?>"
                                            <?= ($id_categorie == $cat["id_categorie"]) ? "selected" : "" ?>>
                                            <?= htmlspecialchars($cat["nom_categorie"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.8rem;">
                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Marque
                                    </label>
                                    <input type="text" name="marque"
                                           value="<?= htmlspecialchars($marque) ?>"
                                           placeholder="Ex : Dell, HP, Lenovo…">
                                </div>

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Modèle
                                    </label>
                                    <input type="text" name="modele"
                                           value="<?= htmlspecialchars($modele) ?>"
                                           placeholder="Ex : XPS 13, ProLiant DL380…">
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.8rem;">
                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Date d'acquisition
                                    </label>
                                    <input type="date" name="date_acquisition"
                                           value="<?= htmlspecialchars($date_acquisition) ?>">
                                </div>

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        État
                                    </label>
                                    <select name="etat">
                                        <option value="disponible"   <?= $etat === "disponible"   ? "selected" : "" ?>>Disponible</option>
                                        <option value="affecte"      <?= $etat === "affecte"      ? "selected" : "" ?>>Affecté</option>
                                        <option value="panne"        <?= $etat === "panne"        ? "selected" : "" ?>>En panne</option>
                                        <option value="maintenance"  <?= $etat === "maintenance"  ? "selected" : "" ?>>En maintenance</option>
                                        <option value="hors_service" <?= $etat === "hors_service" ? "selected" : "" ?>>Hors service</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Localisation
                                </label>
                                <input type="text" name="localisation"
                                       value="<?= htmlspecialchars($localisation) ?>"
                                       placeholder="Ex : Salle B2, Bureau 302, Datacenter…">
                            </div>

                            <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check"></i> Enregistrer
                                </button>
                                <a href="materiels_list.php" class="btn-mode">
                                    Annuler
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </section>
        </main>
    </div>
</div>

<!-- JS -->
<script src="../assets/js/app.js"></script>
</body>
</html>
