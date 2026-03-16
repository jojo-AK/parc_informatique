<?php
require_once "../config/auth_admin.php";
require_once "../config/db.php";

$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_categorie = trim($_POST["nom_categorie"] ?? "");

    if ($nom_categorie !== "") {
        // Insertion dans la table categorie
        $sql = "INSERT INTO categorie (nom_categorie) VALUES (:nom)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nom" => $nom_categorie
        ]);

        // Retour à la liste après insertion
        header("Location: categories_list.php");
        exit;
    } else {
        $messageErreur = "Veuillez saisir un nom de catégorie.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une catégorie</title>
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
                <h1>Ajouter une catégorie</h1>
                <p>Créer une nouvelle catégorie de matériels</p>
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
                    <h2>Nouvelle catégorie</h2>
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
                    <?php endif; ?>

                    <form method="post">
                        <div class="filters" style="flex-direction: column; align-items: stretch; gap: .9rem; max-width: 420px;">

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Nom de la catégorie <span style="color:#f97373;">*</span>
                                </label>
                                <input type="text" name="nom_categorie" required
                                       placeholder="Ex : Laptop personnel, Serveur, Imprimante">
                            </div>

                            <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check"></i> Enregistrer
                                </button>
                                <a href="categories_list.php" class="btn-mode">
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
