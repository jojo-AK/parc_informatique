<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// 1) Récupérer les matériels disponibles
$sqlMat = "SELECT id_materiel, code_inventaire, designation
           FROM materiel
           WHERE etat = 'disponible'
           ORDER BY code_inventaire";
$stmtMat = $pdo->query($sqlMat);
$materiels = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

// 2) Récupérer tous les utilisateurs
$sqlUser = "SELECT id_utilisateur, nom, prenom
            FROM utilisateur
            ORDER BY nom, prenom";
$stmtUser = $pdo->query($sqlUser);
$utilisateurs = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_materiel      = $_POST["id_materiel"] ?? "";
    $id_utilisateur   = $_POST["id_utilisateur"] ?? "";
    $date_affectation = $_POST["date_affectation"] ?? "";
    $commentaire      = trim($_POST["commentaire"] ?? "");

    if ($id_materiel === "" || $id_utilisateur === "" || $date_affectation === "") {
        $messageErreur = "Veuillez choisir un matériel, un utilisateur et une date d'affectation.";
    } else {
        try {
            $pdo->beginTransaction();

            // Vérifier que le matériel existe et est toujours disponible
            $sqlCheckMat = "SELECT etat FROM materiel WHERE id_materiel = :id";
            $stmt = $pdo->prepare($sqlCheckMat);
            $stmt->execute([":id" => $id_materiel]);
            $etatMat = $stmt->fetchColumn();

            if ($etatMat === false) {
                $pdo->rollBack();
                $messageErreur = "Le matériel sélectionné n'existe pas.";
            } elseif ($etatMat !== "disponible") {
                $pdo->rollBack();
                $messageErreur = "Ce matériel n'est plus disponible (état actuel : $etatMat).";
            } else {
                // Vérifier qu'il n'y a pas déjà une affectation active (date_retour NULL)
                $sqlCheckAff = "SELECT COUNT(*) FROM affectation
                                WHERE id_materiel = :id
                                  AND date_retour IS NULL";
                $stmt = $pdo->prepare($sqlCheckAff);
                $stmt->execute([":id" => $id_materiel]);
                $affActive = $stmt->fetchColumn();

                if ($affActive > 0) {
                    $pdo->rollBack();
                    $messageErreur = "Ce matériel possède déjà une affectation en cours.";
                } else {
                    // OK → insertion affectation
                    $sqlInsert = "INSERT INTO affectation
                        (id_materiel, id_utilisateur, date_affectation, date_retour, commentaire)
                        VALUES
                        (:id_materiel, :id_utilisateur, :date_affectation, NULL, :commentaire)";

                    $stmt = $pdo->prepare($sqlInsert);
                    $stmt->execute([
                        ":id_materiel"      => $id_materiel,
                        ":id_utilisateur"   => $id_utilisateur,
                        ":date_affectation" => $date_affectation,
                        ":commentaire"      => $commentaire
                    ]);

                    // Mettre à jour le matériel en "affecte"
                    $sqlUpdate = "UPDATE materiel
                                  SET etat = 'affecte'
                                  WHERE id_materiel = :id_materiel";
                    $stmt2 = $pdo->prepare($sqlUpdate);
                    $stmt2->execute([":id_materiel" => $id_materiel]);

                    $pdo->commit();

                    header("Location: affectations_list.php");
                    exit;
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $messageErreur = "Erreur lors de la création de l'affectation : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle affectation</title>
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
                <h1>Nouvelle affectation</h1>
                <p>Affecter un matériel disponible à un utilisateur</p>
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
                    <h2>Créer une nouvelle affectation</h2>
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

                    <form method="post">
                        <div class="filters" style="flex-direction: column; align-items: stretch; gap: .9rem;">

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Matériel à affecter <span style="color:#f97373;">*</span>
                                </label>
                                <select name="id_materiel" required>
                                    <option value="">-- Choisir un matériel disponible --</option>
                                    <?php foreach ($materiels as $m): ?>
                                        <option value="<?= $m["id_materiel"] ?>">
                                            <?= htmlspecialchars($m["code_inventaire"]) ?>
                                            - <?= htmlspecialchars($m["designation"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Utilisateur <span style="color:#f97373;">*</span>
                                </label>
                                <select name="id_utilisateur" required>
                                    <option value="">-- Choisir un utilisateur --</option>
                                    <?php foreach ($utilisateurs as $u): ?>
                                        <option value="<?= $u["id_utilisateur"] ?>">
                                            <?= htmlspecialchars($u["nom"]) ?> <?= htmlspecialchars($u["prenom"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Date d'affectation <span style="color:#f97373;">*</span>
                                </label>
                                <input type="date" name="date_affectation" required>
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Commentaire
                                </label>
                                <textarea name="commentaire" rows="3"
                                          style="
                                              width: 100%;
                                              resize: vertical;
                                              padding: .5rem .8rem;
                                              font-size: .8rem;
                                              border-radius: 1rem;
                                              border: 1px solid rgba(148,163,184,0.7);
                                              background: rgba(15,23,42,0.9);
                                              color: #e5e7eb;
                                              box-shadow: 0 10px 30px rgba(15,23,42,0.8);
                                              outline: none;
                                          "
                                          placeholder="Informations complémentaires éventuelles..."></textarea>
                            </div>

                            <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check"></i> Enregistrer
                                </button>
                                <a href="affectations_list.php" class="btn-mode">
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
