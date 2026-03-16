<?php
require_once "../config/auth_admin.php";
require_once "../config/db.php";

$messageErreur = "";

$id_categorie = $_GET["id"] ?? null;

if ($id_categorie === null) {
    die("Catégorie non spécifiée.");
}

// Charger la catégorie
$sql = "SELECT * FROM categorie WHERE id_categorie = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id_categorie]);
$categorie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categorie) {
    die("Catégorie introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérifier s'il existe au moins un matériel pour cette catégorie
    $sqlMat = "SELECT COUNT(*) FROM materiel WHERE id_categorie = :id";
    $stmt = $pdo->prepare($sqlMat);
    $stmt->execute([":id" => $id_categorie]);
    $nbMateriels = $stmt->fetchColumn();

    if ($nbMateriels > 0) {
        $messageErreur = "Impossible de supprimer cette catégorie car des matériels y sont rattachés.";
    } else {
        $sqlDel = "DELETE FROM categorie WHERE id_categorie = :id";
        $stmt = $pdo->prepare($sqlDel);
        $stmt->execute([":id" => $id_categorie]);

        header("Location: categories_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression d'une catégorie</title>
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
            <a href="affectations_list.php" class="menu-item">
                <i class="fa-solid fa-people-arrows"></i><span>Affectations</span>
            </a>
            <a href="pannes_list.php" class="menu-item">
                <i class="fa-solid fa-triangle-exclamation"></i><span>Pannes</span>
            </a>
            <a href="categories_list.php" class="menu-item active">
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
                <h1>Suppression d'une catégorie</h1>
                <p>Confirmation avant suppression définitive de la catégorie</p>
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
                    <h2>Détail de la catégorie</h2>
                    <a href="categories_list.php" class="btn-primary">
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
                        <a href="categories_list.php" class="btn-mode">
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
                                    <strong>Attention :</strong> cette action va supprimer la catégorie
                                    <strong><?= htmlspecialchars($categorie["nom_categorie"]) ?></strong>.
                                    Cette opération est irréversible.
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem; font-size:.85rem;">
                            <p>
                                <strong>Nom de la catégorie :</strong>
                                <?= htmlspecialchars($categorie["nom_categorie"]) ?>
                            </p>
                        </div>

                        <form method="post" style="margin-top:.8rem;">
                            <p style="font-size:.85rem; margin-bottom:.7rem;">
                                Confirmez-vous la suppression de cette catégorie ?
                            </p>
                            <div style="display:flex; gap:.6rem;">
                                <button type="submit" class="btn-primary"
                                        style="background: linear-gradient(135deg,#ef4444,#f97316); box-shadow:0 16px 40px rgba(239,68,68,0.9);">
                                    <i class="fa-solid fa-trash-can"></i> Oui, supprimer
                                </button>
                                <a href="categories_list.php" class="btn-mode">
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

<!-- JS -->
<script src="../assets/js/app.js"></script>
</body>
</html>
