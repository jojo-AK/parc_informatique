<?php
require_once "../config/auth_admin.php";
require_once "../config/db.php";

$messageErreur = "";

// Récupérer id
$id_materiel = $_GET["id"] ?? null;

if ($id_materiel === null) {
    die("Matériel non spécifié.");
}

// Charger le matériel
$sql = "SELECT * FROM materiel WHERE id_materiel = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id_materiel]);
$materiel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materiel) {
    die("Matériel introuvable.");
}

$etatInitial = $materiel["etat"];

// Vérifier s'il existe une panne en cours pour ce matériel
$sqlPanne = "SELECT COUNT(*) FROM panne_maintenance
             WHERE id_materiel = :id_mat
               AND statut = 'en_cours'";
$stmtP = $pdo->prepare($sqlPanne);
$stmtP->execute([":id_mat" => $id_materiel]);
$hasPanneEnCours = $stmtP->fetchColumn() > 0;

// Récupérer catégories
$sqlCat = "SELECT id_categorie, nom_categorie FROM categorie ORDER BY nom_categorie";
$stmtCat = $pdo->query($sqlCat);
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Pré-remplissage des champs (valeurs actuelles)
$code_inventaire  = $materiel["code_inventaire"];
$designation      = $materiel["designation"];
$id_categorie     = $materiel["id_categorie"];
$marque           = $materiel["marque"] ?? "";
$modele           = $materiel["modele"] ?? "";
$date_acquisition = $materiel["date_acquisition"] ?? "";
$etat             = $materiel["etat"];
$localisation     = $materiel["localisation"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code_inventaire = trim($_POST["code_inventaire"] ?? "");
    $designation     = trim($_POST["designation"] ?? "");
    $id_categorie    = $_POST["id_categorie"] ?? "";
    $marque          = trim($_POST["marque"] ?? "");
    $modele          = trim($_POST["modele"] ?? "");
    $date_acquisition= $_POST["date_acquisition"] ?? "";
    $etat            = $_POST["etat"] ?? "disponible";
    $localisation    = trim($_POST["localisation"] ?? "");

    // Si une panne est en cours, on force l'état à "panne" côté serveur
    if ($hasPanneEnCours) {
        $etat = "panne";
    }

    if ($code_inventaire === "" || $designation === "" || $id_categorie === "") {
        $messageErreur = "Code inventaire, Désignation et Catégorie sont obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();

            // Vérifier que la catégorie existe
            $sqlCheckCat = "SELECT COUNT(*) FROM categorie WHERE id_categorie = :id";
            $stmt = $pdo->prepare($sqlCheckCat);
            $stmt->execute([":id" => $id_categorie]);
            $catExiste = $stmt->fetchColumn();

            if (!$catExiste) {
                $pdo->rollBack();
                $messageErreur = "La catégorie sélectionnée n'existe pas.";
            } else {
                // Vérifier unicité du code inventaire (hors ce matériel)
                $sqlCheckCode = "SELECT COUNT(*) FROM materiel
                                 WHERE code_inventaire = :code
                                   AND id_materiel <> :id";
                $stmt = $pdo->prepare($sqlCheckCode);
                $stmt->execute([
                    ":code" => $code_inventaire,
                    ":id"   => $id_materiel
                ]);
                $codeExiste = $stmt->fetchColumn();

                if ($codeExiste) {
                    $pdo->rollBack();
                    $messageErreur = "Ce code d'inventaire est déjà utilisé par un autre matériel.";
                } else {
                    // 1) Mise à jour du matériel
                    $sqlUpdate = "UPDATE materiel
                                  SET code_inventaire = :code_inventaire,
                                      designation     = :designation,
                                      id_categorie    = :id_categorie,
                                      marque          = :marque,
                                      modele          = :modele,
                                      date_acquisition= :date_acquisition,
                                      etat            = :etat,
                                      localisation    = :localisation
                                  WHERE id_materiel = :id";

                    $stmt = $pdo->prepare($sqlUpdate);
                    $stmt->execute([
                        ":code_inventaire" => $code_inventaire,
                        ":designation"     => $designation,
                        ":id_categorie"    => $id_categorie,
                        ":marque"          => $marque,
                        ":modele"          => $modele,
                        ":date_acquisition"=> ($date_acquisition !== "" ? $date_acquisition : null),
                        ":etat"            => $etat,
                        ":localisation"    => $localisation,
                        ":id"              => $id_materiel
                    ]);

                    // 2) Si on vient de passer l'état en "panne"
                    //    alors qu'il ne l'était pas avant ET qu'il n'y a pas déjà de panne_en_cours
                    //    → créer une panne en_cours
                    if (!$hasPanneEnCours && $etat === "panne" && $etatInitial !== "panne") {

                        $sqlCheckPanne = "SELECT COUNT(*) FROM panne_maintenance
                                          WHERE id_materiel = :id_mat
                                            AND statut = 'en_cours'";
                        $stmtP2 = $pdo->prepare($sqlCheckPanne);
                        $stmtP2->execute([":id_mat" => $id_materiel]);
                        $panneActive = $stmtP2->fetchColumn();

                        if ($panneActive == 0) {
                            $sqlInsertP = "INSERT INTO panne_maintenance
                                           (id_materiel, date_panne, description, statut, date_resolution)
                                           VALUES
                                           (:id_mat, :date_panne, :description, 'en_cours', NULL)";
                            $stmtIns = $pdo->prepare($sqlInsertP);
                            $stmtIns->execute([
                                ":id_mat"     => $id_materiel,
                                ":date_panne" => date('Y-m-d'),
                                ":description"=> "Panne enregistrée via modification du matériel."
                            ]);
                        }
                    }

                    $pdo->commit();

                    header("Location: materiels_list.php");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $messageErreur = "Erreur lors de la mise à jour : " . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollBack();
            $messageErreur = "Erreur inattendue : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un matériel</title>
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
                <h1>Modifier un matériel</h1>
                <p>Mettre à jour les informations d’un équipement existant</p>
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
                    <h2>Fiche matériel</h2>
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

                    <?php if ($hasPanneEnCours): ?>
                        <div style="
                            margin-bottom: .9rem;
                            padding: .7rem .9rem;
                            border-radius: 1rem;
                            border:1px solid rgba(234,179,8,0.9);
                            background: radial-gradient(circle at left, rgba(250,204,21,0.22), rgba(15,23,42,0.95));
                            color:#fef3c7;
                            font-size:.84rem;
                        ">
                            <div style="display:flex; gap:.6rem; align-items:flex-start;">
                                <i class="fa-solid fa-triangle-exclamation" style="margin-top:.12rem;"></i>
                                <div>
                                    <strong>Panne en cours :</strong> ce matériel a une panne <strong>en cours</strong>.
                                    Son état est verrouillé à <strong>panne</strong>.
                                    Pour le remettre <em>disponible</em> ou <em>affecté</em>, résolvez d'abord la panne
                                    dans la page <a href="pannes_list.php" style="color:#facc15; text-decoration:underline;">Pannes</a>.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="filters" style="flex-direction: column; align-items: stretch; gap: .9rem; max-width: 650px;">

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Code inventaire <span style="color:#f97373;">*</span>
                                </label>
                                <input type="text" name="code_inventaire" required
                                       value="<?= htmlspecialchars($code_inventaire) ?>">
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Désignation <span style="color:#f97373;">*</span>
                                </label>
                                <input type="text" name="designation" required
                                       value="<?= htmlspecialchars($designation) ?>">
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
                                           value="<?= htmlspecialchars($marque) ?>">
                                </div>

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Modèle
                                    </label>
                                    <input type="text" name="modele"
                                           value="<?= htmlspecialchars($modele) ?>">
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
                                    <?php if ($hasPanneEnCours): ?>
                                        <select disabled>
                                            <option>panne</option>
                                        </select>
                                        <input type="hidden" name="etat" value="panne">
                                    <?php else: ?>
                                        <select name="etat">
                                            <?php
                                            $etats = ["disponible","affecte","panne","maintenance","hors_service"];
                                            foreach ($etats as $opt): ?>
                                                <option value="<?= $opt ?>"
                                                    <?= ($etat === $opt) ? "selected" : "" ?>>
                                                    <?= ucfirst($opt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
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
