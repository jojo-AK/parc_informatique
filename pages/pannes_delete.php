<?php
require_once "../config/auth_admin.php";
require_once "../config/db.php";

$messageErreur = "";

$id_panne = $_GET["id"] ?? null;

if ($id_panne === null) {
    die("Panne non spécifiée.");
}

// Charger la panne + matériel
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo->beginTransaction();

        $idMateriel  = $panne["id_materiel"];
        $statutAvant = $panne["statut"];

        // 1) Supprimer la panne
        $sqlDel = "DELETE FROM panne_maintenance WHERE id_panne = :id";
        $stmtDel = $pdo->prepare($sqlDel);
        $stmtDel->execute([":id" => $id_panne]);

        // 2) Recalculer l'état du matériel seulement si on supprime une panne en cours
        if ($statutAvant === "en_cours") {
            // Vérifier s'il reste une autre panne en cours
            $sqlPEnCours = "SELECT COUNT(*) FROM panne_maintenance
                            WHERE id_materiel = :id_mat
                              AND statut = 'en_cours'";
            $stmtP = $pdo->prepare($sqlPEnCours);
            $stmtP->execute([":id_mat" => $idMateriel]);
            $nbPannesEnCours = $stmtP->fetchColumn();

            // Vérifier s'il existe une affectation active
            $sqlAff = "SELECT COUNT(*) FROM affectation
                       WHERE id_materiel = :id_mat
                         AND date_retour IS NULL";
            $stmtAff = $pdo->prepare($sqlAff);
            $stmtAff->execute([":id_mat" => $idMateriel]);
            $nbAffectationsActives = $stmtAff->fetchColumn();

            if ($nbPannesEnCours > 0) {
                $nouvelEtat = "panne";
            } elseif ($nbAffectationsActives > 0) {
                $nouvelEtat = "affecte";
            } else {
                $nouvelEtat = "disponible";
            }

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
        $messageErreur = "Erreur lors de la suppression : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression d'une panne</title>
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
                <h1>Suppression d'une panne</h1>
                <p>Confirmation avant suppression définitive de l'incident</p>
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

                        <a href="pannes_list.php" class="btn-mode">
                            ← Retour à la liste
                        </a>

                    <?php else: ?>

                        <div style="
                            margin-bottom: 1rem;
                            padding:.8rem .95rem;
                            border-radius:1rem;
                            border:1px solid rgba(234,179,8,0.8);
                            background: radial-gradient(circle at left, rgba(250,204,21,0.25), rgba(15,23,42,0.95));
                            color:#fef9c3;
                            font-size:.85rem;
                        ">
                            <div style="display:flex; align-items:flex-start; gap:.6rem;">
                                <i class="fa-solid fa-triangle-exclamation" style="margin-top:.15rem;"></i>
                                <div>
                                    <strong>Attention :</strong> cette action va supprimer définitivement cet incident
                                    de la liste des pannes. Si la panne est <strong>en cours</strong>,
                                    l'état du matériel sera recalculé automatiquement
                                    (panne, affecté ou disponible selon les autres données).
                                </div>
                            </div>
                        </div>

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
                                <strong>Statut :</strong>
                                <?= htmlspecialchars($panne["statut"]) ?>
                            </p>
                            <?php if (!empty($panne["description"])): ?>
                                <p style="margin-bottom:.3rem;">
                                    <strong>Description :</strong><br>
                                    <?= nl2br(htmlspecialchars($panne["description"])) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <form method="post" style="margin-top:.8rem;">
                            <p style="font-size:.85rem; margin-bottom:.7rem;">
                                Confirmez-vous la suppression de cette panne ?
                            </p>
                            <div style="display:flex; gap:.6rem;">
                                <button type="submit" class="btn-primary"
                                        style="background: linear-gradient(135deg,#ef4444,#f97316); box-shadow:0 16px 40px rgba(239,68,68,0.9);">
                                    <i class="fa-solid fa-trash-can"></i> Oui, supprimer
                                </button>
                                <a href="pannes_list.php" class="btn-mode">
                                    Annuler
                                </a>
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
