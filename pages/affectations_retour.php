<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$messageErreur = "";

// Récupérer l'id de l'affectation
$id_affectation = $_GET["id"] ?? null;

if ($id_affectation === null) {
    die("Affectation non spécifiée.");
}

// Récupérer les informations de l'affectation + matériel + utilisateur
$sql = "SELECT a.*,
               m.id_materiel,
               m.code_inventaire,
               m.designation,
               m.etat AS etat_materiel,
               u.nom,
               u.prenom
        FROM affectation a
        JOIN materiel m ON a.id_materiel = m.id_materiel
        JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
        WHERE a.id_affectation = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id_affectation]);
$affectation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$affectation) {
    die("Affectation introuvable.");
}

// Si déjà retournée, on peut juste informer
$dejaRetourne = !empty($affectation["date_retour"]);

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$dejaRetourne) {
    $date_retour = $_POST["date_retour"] ?? "";

    if ($date_retour === "") {
        $messageErreur = "Veuillez saisir une date de retour.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1) Mettre à jour l'affectation (date_retour)
            $sqlUpdateAff = "UPDATE affectation
                             SET date_retour = :date_retour
                             WHERE id_affectation = :id_affectation
                               AND date_retour IS NULL";
            $stmt = $pdo->prepare($sqlUpdateAff);
            $stmt->execute([
                ":date_retour"    => $date_retour,
                ":id_affectation" => $id_affectation
            ]);

            // 2) Mettre à jour l'état du matériel :
            //    - si l'état actuel est 'affecte', on le remet en 'disponible'
            //    - sinon, on ne touche pas (par ex. s'il est en 'panne')
            $sqlUpdateMat = "UPDATE materiel
                             SET etat = CASE
                                           WHEN etat = 'affecte' THEN 'disponible'
                                           ELSE etat
                                        END
                             WHERE id_materiel = :id_materiel";
            $stmt2 = $pdo->prepare($sqlUpdateMat);
            $stmt2->execute([":id_materiel" => $affectation["id_materiel"]]);

            $pdo->commit();

            header("Location: affectations_list.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $messageErreur = "Erreur lors du retour du matériel : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retour du matériel</title>
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
                <h1>Retour du matériel</h1>
                <p>Clôturer une affectation et mettre à jour l'état du matériel</p>
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
                    <h2>Détails de l'affectation</h2>
                    <a href="affectations_list.php" class="btn-primary">
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

                    <div style="margin-bottom: 1rem; font-size:.85rem;">
                        <p style="margin-bottom:.3rem;">
                            <strong>Matériel :</strong>
                            <?= htmlspecialchars($affectation["code_inventaire"] ?? "") ?>
                            – <?= htmlspecialchars($affectation["designation"] ?? "") ?>
                        </p>
                        <p style="margin-bottom:.3rem;">
                            <strong>Utilisateur :</strong>
                            <?= htmlspecialchars($affectation["nom"] ?? "") ?>
                            <?= htmlspecialchars($affectation["prenom"] ?? "") ?>
                        </p>
                        <p style="margin-bottom:.3rem;">
                            <strong>Date d'affectation :</strong>
                            <?= htmlspecialchars($affectation["date_affectation"] ?? "") ?>
                        </p>
                        <p style="margin-bottom:.3rem;">
                            <strong>État actuel du matériel :</strong>
                            <?= htmlspecialchars($affectation["etat_materiel"] ?? "") ?>
                        </p>
                    </div>

                    <?php if ($dejaRetourne): ?>

                        <div style="
                            margin-top:.5rem;
                            padding:.7rem .9rem;
                            border-radius:1rem;
                            border:1px solid rgba(34,197,94,0.8);
                            background: radial-gradient(circle at left, rgba(34,197,94,0.3), rgba(15,23,42,0.95));
                            color:#bbf7d0;
                            font-size:.85rem;
                        ">
                            <div style="display:flex; align-items:flex-start; gap:.6rem;">
                                <i class="fa-solid fa-circle-check" style="margin-top:.1rem;"></i>
                                <div>
                                    Ce matériel a déjà été retourné le
                                    <strong><?= htmlspecialchars($affectation["date_retour"] ?? "") ?></strong>.
                                </div>
                            </div>
                        </div>

                    <?php else: ?>

                        <div style="
                            margin-bottom: .9rem;
                            padding:.7rem .9rem;
                            border-radius:1rem;
                            border:1px solid rgba(56,189,248,0.8);
                            background: radial-gradient(circle at left, rgba(56,189,248,0.25), rgba(15,23,42,0.95));
                            color:#e0f2fe;
                            font-size:.82rem;
                        ">
                            <div style="display:flex; align-items:flex-start; gap:.6rem;">
                                <i class="fa-solid fa-info-circle" style="margin-top:.1rem;"></i>
                                <div>
                                    En validant le retour, l'affectation sera clôturée.
                                    Si le matériel était en état <strong>affecte</strong>, il sera remis en <strong>disponible</strong>.
                                </div>
                            </div>
                        </div>

                        <form method="post">
                            <div class="filters" style="flex-direction: column; align-items: stretch; gap: .8rem;">

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Date de retour <span style="color:#f97373;">*</span>
                                    </label>
                                    <input type="date" name="date_retour"
                                           value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                                </div>

                                <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                    <button type="submit" class="btn-primary">
                                        <i class="fa-solid fa-check"></i> Valider le retour
                                    </button>
                                    <a href="affectations_list.php" class="btn-mode">
                                        Annuler
                                    </a>
                                </div>
                            </div>
                        </form>

                    <?php endif; ?>

                </div>
            </section>
        </main>
    </div>
</div>

<!-- JS -->
<script src="../assets/js/app.js"></script>
</body>
</html>
