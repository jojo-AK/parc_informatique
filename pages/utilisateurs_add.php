<?php
require_once "../config/auth_admin.php";
require_once "../config/db.php";

$messageErreur = "";

// valeurs pour re-remplir le formulaire en cas d'erreur
$nom       = "";
$prenom    = "";
$service   = "";
$email     = "";
$telephone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom       = trim($_POST["nom"] ?? "");
    $prenom    = trim($_POST["prenom"] ?? "");
    $service   = trim($_POST["service"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");

    if ($nom !== "" && $prenom !== "") {
        $sql = "INSERT INTO utilisateur (nom, prenom, service, email, telephone)
                VALUES (:nom, :prenom, :service, :email, :telephone)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nom"       => $nom,
            ":prenom"    => $prenom,
            ":service"   => $service,
            ":email"     => $email,
            ":telephone" => $telephone
        ]);

        // Retour à la liste
        header("Location: utilisateurs_list.php");
        exit;
    } else {
        $messageErreur = "Les champs Nom et Prénom sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
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
            <a href="utilisateurs_list.php" class="menu-item active">
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
                <h1>Ajouter un utilisateur</h1>
                <p>Enregistrer un nouveau collaborateur dans le parc</p>
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
                    <h2>Nouveau utilisateur</h2>
                    <a href="utilisateurs_list.php" class="btn-primary">
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
                        <div class="filters" style="flex-direction: column; align-items: stretch; gap:.9rem; max-width: 600px;">

                            <div style="display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.8rem;">
                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Nom <span style="color:#f97373;">*</span>
                                    </label>
                                    <input type="text" name="nom" required
                                           value="<?= htmlspecialchars($nom) ?>">
                                </div>

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Prénom <span style="color:#f97373;">*</span>
                                    </label>
                                    <input type="text" name="prenom" required
                                           value="<?= htmlspecialchars($prenom) ?>">
                                </div>
                            </div>

                            <div>
                                <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                    Service
                                </label>
                                <input type="text" name="service"
                                       placeholder="Ex : Informatique, Comptabilité..."
                                       value="<?= htmlspecialchars($service) ?>">
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.8rem;">
                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Email
                                    </label>
                                    <input type="email" name="email"
                                           placeholder="exemple@entreprise.com"
                                           value="<?= htmlspecialchars($email) ?>">
                                </div>

                                <div>
                                    <label style="display:block; font-size:.82rem; margin-bottom:.25rem;">
                                        Téléphone
                                    </label>
                                    <input type="text" name="telephone"
                                           placeholder="+228 ..."
                                           value="<?= htmlspecialchars($telephone) ?>">
                                </div>
                            </div>

                            <div style="display:flex; gap:.6rem; margin-top:.4rem;">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check"></i> Enregistrer
                                </button>
                                <a href="utilisateurs_list.php" class="btn-mode">
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
