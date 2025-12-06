<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$messageErreur = "";

// ID de la panne
$id_panne = $_GET["id"] ?? null;
if ($id_panne === null) {
    die("Panne non spécifiée.");
}

// Charger les infos de la panne + matériel
$sql = "SELECT p.*,
               m.id_materiel,
               m.code_inventaire,
               m.designation
        FROM panne_maintenance p
        JOIN materiel m ON p.id_materiel = m.id_materiel
        WHERE p.id_panne = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id_panne]);
$panne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$panne) {
    die("Panne introuvable.");
}

// déjà résolue ?
$dejaResolue = ($panne["statut"] === "resolue");

// valeur par défaut pour le champ date
$date_resolution_sel = $panne["date_resolution"] ?? date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$dejaResolue) {
    $date_resolution = $_POST["date_resolution"] ?? "";
    $date_resolution_sel = $date_resolution;

    if ($date_resolution === "") {
        $messageErreur = "Veuillez saisir une date de résolution.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1) Mise à jour de la panne
            $sqlUpdate = "UPDATE panne_maintenance
                          SET statut = 'resolue',
                              date_resolution = :date_resolution
                          WHERE id_panne = :id
                            AND statut = 'en_cours'";
            $stmt = $pdo->prepare($sqlUpdate);
            $stmt->execute([
                ":date_resolution" => $date_resolution,
                ":id"              => $id_panne
            ]);

            $idMateriel = $panne["id_materiel"];

            // 2) Lire l'état actuel du matériel
            $sqlEtat = "SELECT etat FROM materiel WHERE id_materiel = :id_mat";
            $stmtEtat = $pdo->prepare($sqlEtat);
            $stmtEtat->execute([":id_mat" => $idMateriel]);
            $etatActuel = $stmtEtat->fetchColumn();

            // On ne corrige l'état que si le matériel est encore marqué "panne"
            if ($etatActuel === "panne") {
                // 3) Vérifier s'il existe une affectation ACTIVE
                $sqlAff = "SELECT COUNT(*) FROM affectation
                           WHERE id_materiel = :id_mat
                             AND date_retour IS NULL";
                $stmtAff = $pdo->prepare($sqlAff);
                $stmtAff->execute([":id_mat" => $idMateriel]);
                $affectationsActives = $stmtAff->fetchColumn();

                $nouvelEtat = ($affectationsActives > 0) ? "affecte" : "disponible";

                // 4) MAJ état du matériel
                $sqlUpdateMat = "UPDATE materiel
                                 SET etat = :etat
                                 WHERE id_materiel = :id_mat";
                $stmtMat = $pdo->prepare($sqlUpdateMat);
                $stmtMat->execute([
                    ":etat"   => $nouvelEtat,
                    ":id_mat" => $idMateriel
                ]);
            }

            $pdo->commit();
            header("Location: pannes_list.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $messageErreur = "Erreur lors de la résolution : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résolution de la panne</title>
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

    <!-- Main -->
    <div class="main">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn-toggle-sidebar" id="btn-toggle-sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-title">
                <h1>Résolution de la panne</h1>
                <p>Clôturer un incident sur un matériel</p>
            </div>

            <div class="topbar-actions">
                <button class="btn-mode" id="btn-mode">
                    <i class="fa-solid fa-moon"></i>
                    <span>Mode sombre</span>
                </button>
            </div>
        </header>

        <!-- Contenu -->
        <main class="content">
            <section class="panel">
                <div class="panel-header">
                    <h2>Détail de la panne</h2>
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

                    <!-- Récap de la panne -->
                    <div style="margin-bottom: 1rem; font-size:.85rem;">
                        <p style="margin-bottom:.3rem;">
                            <strong>Matériel :</strong>
                            <?= htmlspecialchars($panne["code_inventaire"]) ?>
                            - <?= htmlspecialchars($panne["designation"]) ?>
                        </p>

                        <p style="margin-bottom:.3rem;">
                            <strong>Date de panne :</strong>
                            <?= htmlspecialchars($panne["date_panne"]) ?>
                        </p>

                        <p style="margin-bottom:.3rem;">
                            <strong>Description :</strong><br>
                            <?= nl2br(htmlspecialchars($panne["description"])) ?>
                        </p>
                    </div>

                    <?php if ($dejaResolue): ?>

                        <!-- Message : déjà résolue -->
                        <div style="
                            margin-top:.5rem;
                            padding:.7rem .9rem;
                            border-radius:1rem;
                            border:1px solid rgba(34,197,94,0.6);
                            background: radial-gradient(circle at left, rgba(34,197,94,0.25), rgba(15,23,42,0.95));
                            color:#bbf7d0;
                            font-size:.84rem;
                        ">
                            <div style="display:flex; gap:.6rem; align-items:flex-start;">
                                <i class="fa-solid fa-circle-check" style="margin-top:.12rem;"></i>
                                <div>
                                    Cette panne est déjà marquée comme <strong>résolue</strong> depuis le
                                    <strong><?= htmlspecialchars($panne["date_resolution"]) ?></strong>.
                                </div>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- Bandeau d’info -->
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
                                <i class="fa-solid fa-wrench" style="margin-top:.15rem;"></i>
                                <div>
                                    En confirmant la résolution :
                                    <ul style="margin:.3rem 0 0 .9rem; padding:0; list-style:disc;">
                                        <li>La panne sera marquée comme <strong>résolue</strong>.</li>
                                        <li>L'état du matériel sera ajusté en <em>affecté</em> ou <em>disponible</em>
                                            selon les affectations en cours.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de résolution -->
                        <form method="post">
                            <div class="filters" style="flex-direction: column; align-items: flex-start; gap:.9rem; max-width:420px;">

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Date de résolution <span style="color:#f97373;">*</span>
                                    </label>
                                    <input type="date" name="date_resolution"
                                           required
                                           value="<?= htmlspecialchars($date_resolution_sel) ?>">
                                </div>

                                <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                    <button type="submit" class="btn-primary">
                                        <i class="fa-solid fa-check"></i> Confirmer la résolution
                                    </button>
                                    <a href="pannes_list.php" class="btn-mode">
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

<!-- JS principal -->
<script src="../assets/js/app.js"></script>
</body>
</html>
