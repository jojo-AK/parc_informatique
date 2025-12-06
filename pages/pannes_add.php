<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Récupérer les matériels qui ne sont pas hors service
$sqlMat = "SELECT id_materiel, code_inventaire, designation, etat
           FROM materiel
           WHERE etat <> 'hors_service'
           ORDER BY code_inventaire";
$stmtMat = $pdo->query($sqlMat);
$materiels = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

$messageErreur = "";

// valeurs pour re-remplir le formulaire
$id_materiel_sel = "";
$date_panne_sel  = "";
$description_sel = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_materiel = $_POST["id_materiel"] ?? "";
    $date_panne  = $_POST["date_panne"] ?? "";
    $description = trim($_POST["description"] ?? "");

    // garder les valeurs pour le formulaire
    $id_materiel_sel = $id_materiel;
    $date_panne_sel  = $date_panne;
    $description_sel = $description;

    if ($id_materiel === "" || $date_panne === "" || $description === "") {
        $messageErreur = "Veuillez choisir un matériel, saisir une date de panne et une description.";
    } else {
        try {
            $pdo->beginTransaction();

            // Vérifier que le matériel existe
            $sqlCheckMat = "SELECT etat FROM materiel WHERE id_materiel = :id";
            $stmt = $pdo->prepare($sqlCheckMat);
            $stmt->execute([":id" => $id_materiel]);
            $etatMat = $stmt->fetchColumn();

            if ($etatMat === false) {
                $pdo->rollBack();
                $messageErreur = "Le matériel sélectionné n'existe pas.";
            } else {
                // Vérifier s'il y a déjà une panne en cours
                $sqlCheckPanne = "SELECT COUNT(*) FROM panne_maintenance
                                  WHERE id_materiel = :id
                                    AND statut = 'en_cours'";
                $stmt = $pdo->prepare($sqlCheckPanne);
                $stmt->execute([":id" => $id_materiel]);
                $panneActive = $stmt->fetchColumn();

                if ($panneActive > 0) {
                    $pdo->rollBack();
                    $messageErreur = "Ce matériel a déjà une panne en cours.";
                } else {
                    // Ajouter la panne
                    $sqlInsert = "INSERT INTO panne_maintenance
                                  (id_materiel, date_panne, description, statut, date_resolution)
                                  VALUES
                                  (:id_materiel, :date_panne, :description, 'en_cours', NULL)";
                    $stmt = $pdo->prepare($sqlInsert);
                    $stmt->execute([
                        ":id_materiel" => $id_materiel,
                        ":date_panne"  => $date_panne,
                        ":description" => $description
                    ]);

                    // Mettre etat = 'panne' (sauf si déjà 'panne' ou 'hors_service')
                    if ($etatMat !== "panne" && $etatMat !== "hors_service") {
                        $sqlUpdate = "UPDATE materiel
                                      SET etat = 'panne'
                                      WHERE id_materiel = :id_materiel";
                        $stmt2 = $pdo->prepare($sqlUpdate);
                        $stmt2->execute([":id_materiel" => $id_materiel]);
                    }

                    $pdo->commit();

                    header("Location: pannes_list.php");
                    exit;
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $messageErreur = "Erreur lors de la déclaration de la panne : " . $e->getMessage();
        }
    }
} else {
    // valeur par défaut pour la date : aujourd'hui
    $date_panne_sel = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclarer une panne</title>
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
                <h1>Déclarer une panne</h1>
                <p>Enregistrer un incident sur un matériel</p>
            </div>

            <div class="topbar-actions">
                <button class="btn-mode" id="btn-mode">
                    <i class="fa-solid fa-moon"></i>
                    <span>Mode sombre</span>
                </button>
            </div>
        </header>

        <main class="content">
            <section class="panel">
                <div class="panel-header">
                    <h2>Nouvelle panne</h2>
                    <a href="pannes_list.php" class="btn-primary">
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

                    <!-- Bandeau d'information -->
                    <div style="
                        margin-bottom: 1rem;
                        padding:.8rem .95rem;
                        border-radius:1rem;
                        border:1px solid rgba(59,130,246,0.7);
                        background: radial-gradient(circle at left, rgba(59,130,246,0.25), rgba(15,23,42,0.95));
                        color:#bfdbfe;
                        font-size:.85rem;
                    ">
                        <div style="display:flex; align-items:flex-start; gap:.6rem;">
                            <i class="fa-solid fa-circle-info" style="margin-top:.15rem;"></i>
                            <div>
                                En déclarant une panne :
                                <ul style="margin:.3rem 0 0 .9rem; padding:0; list-style:disc;">
                                    <li>Une entrée est créée dans le suivi des pannes (<strong>statut : en cours</strong>).</li>
                                    <li>L’état du matériel passe automatiquement à <strong>panne</strong>
                                        (sauf s’il est déjà <em>panne</em> ou <em>hors service</em>).</li>
                                    <li>Un seul incident <strong>en cours</strong> est autorisé par matériel.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <form method="post">
                        <div class="filters" style="flex-direction: column; align-items: stretch; gap: .9rem; max-width: 650px;">

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Matériel en panne <span style="color:#f97373;">*</span>
                                </label>
                                <select name="id_materiel" required>
                                    <option value="">-- Choisir un matériel --</option>
                                    <?php foreach ($materiels as $m): ?>
                                        <option value="<?= $m["id_materiel"] ?>"
                                            <?= ($id_materiel_sel == $m["id_materiel"]) ? "selected" : "" ?>>
                                            <?= htmlspecialchars($m["code_inventaire"]) ?>
                                            - <?= htmlspecialchars($m["designation"]) ?>
                                            (<?= htmlspecialchars($m["etat"]) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.8rem;">
                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Date de la panne <span style="color:#f97373;">*</span>
                                    </label>
                                    <input type="date" name="date_panne" required
                                           value="<?= htmlspecialchars($date_panne_sel) ?>">
                                </div>
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Description <span style="color:#f97373;">*</span>
                                </label>
                                <textarea name="description" rows="4" required
                                          placeholder="Décrivez le problème rencontré (symptômes, contexte, actions effectuées, etc.)"><?= htmlspecialchars($description_sel) ?></textarea>
                            </div>

                            <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check"></i> Enregistrer
                                </button>
                                <a href="pannes_list.php" class="btn-mode">
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

<!-- JS principal -->
<script src="../assets/js/app.js"></script>
</body>
</html>
