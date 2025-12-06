<?php
require_once "../config/auth.php";
require_once "../config/db.php";

// Requête la plus simple possible : juste id + nom
$sql = "SELECT id_categorie, nom_categorie 
        FROM categorie
        ORDER BY nom_categorie";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catégories</title>
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
            <a href="categories_list.php" class="menu-item active">
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
                <h1>Gestion des catégories</h1>
                <p>Liste simple des catégories du parc</p>
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
                    <h2>Liste des catégories</h2>
                    <a href="categories_add.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Ajouter une catégorie
                    </a>
                </div>

                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" style="text-align:center; padding:1rem;">
                                        Aucune catégorie enregistrée.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c["id_categorie"]) ?></td>
                                        <td><?= htmlspecialchars($c["nom_categorie"]) ?></td>
                                        <td>
                                            <a href="categories_delete.php?id=<?= urlencode($c["id_categorie"]) ?>"
                                               class="btn-table btn-danger"
                                               onclick="return confirm('Supprimer cette catégorie ?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <button class="fab" onclick="window.location.href='categories_add.php'">
                <i class="fa-solid fa-plus"></i>
            </button>
        </main>
    </div>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
