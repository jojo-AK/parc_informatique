< !DOCTYPE html >
    <html lang="fr">
        <head>
            <meta charset="UTF-8">
                <title>Parc Informatique - Tableau de bord</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">

                    <!-- Police Google (optionnelle) -->
                    <link rel="preconnect" href="https://fonts.gstatic.com">
                        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

                            <!-- Icones (optionnel) -->
                            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

                                <!-- Ton CSS -->
                                <link rel="stylesheet" href="assets/css/style.css">
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
                                                <a href="index.php" class="menu-item active">
                                                    <i class="fa-solid fa-chart-line"></i>
                                                    <span>Dashboard</span>
                                                </a>
                                                <a href="materiels_list.php" class="menu-item">
                                                    <i class="fa-solid fa-computer"></i>
                                                    <span>Matériels</span>
                                                </a>
                                                <a href="affectations_list.php" class="menu-item">
                                                    <i class="fa-solid fa-people-arrows"></i>
                                                    <span>Affectations</span>
                                                </a>
                                                <a href="pannes_list.php" class="menu-item">
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                    <span>Pannes</span>
                                                </a>
                                                <a href="stats.php" class="menu-item">
                                                    <i class="fa-solid fa-chart-pie"></i>
                                                    <span>Statistiques</span>
                                                </a>
                                            </nav>
                                        </aside>

                                        <!-- Contenu -->
                                        <div class="main">
                                            <!-- Topbar -->
                                            <header class="topbar">
                                                <button class="btn-toggle-sidebar" id="btn-toggle-sidebar">
                                                    <i class="fa-solid fa-bars"></i>
                                                </button>

                                                <div class="topbar-title">
                                                    <h1>Tableau de bord</h1>
                                                    <p>Vue générale du parc informatique</p>
                                                </div>

                                                <div class="topbar-actions">
                                                    <button class="btn-mode" id="btn-mode">
                                                        <i class="fa-solid fa-moon"></i>
                                                        <span>Mode sombre</span>
                                                    </button>
                                                </div>
                                            </header>

                                            <!-- Contenu principal -->
                                            <main class="content">
                                                <!-- Cartes de stats -->
                                                <section class="cards">
                                                    <div class="card card-gradient-1">
                                                        <div class="card-header">
                                                            <span>Matériels</span>
                                                            <i class="fa-solid fa-computer"></i>
                                                        </div>
                                                        <div class="card-body">
                                                            <h2>128</h2>
                                                            <p>Matériels enregistrés</p>
                                                        </div>
                                                    </div>

                                                    <div class="card card-gradient-2">
                                                        <div class="card-header">
                                                            <span>Affectations</span>
                                                            <i class="fa-solid fa-people-group"></i>
                                                        </div>
                                                        <div class="card-body">
                                                            <h2>54</h2>
                                                            <p>Affectations actives</p>
                                                        </div>
                                                    </div>

                                                    <div class="card card-gradient-3">
                                                        <div class="card-header">
                                                            <span>Pannes</span>
                                                            <i class="fa-solid fa-bolt-lightning"></i>
                                                        </div>
                                                        <div class="card-body">
                                                            <h2>7</h2>
                                                            <p>Pannes en cours</p>
                                                        </div>
                                                    </div>
                                                </section>

                                                <!-- Exemple de tableau -->
                                                <section class="panel">
                                                    <div class="panel-header">
                                                        <h2>Derniers matériels ajoutés</h2>
                                                        <button class="btn-primary">
                                                            <i class="fa-solid fa-plus"></i> Nouveau matériel
                                                        </button>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="table-wrapper">
                                                            <table>
                                                                <thead>
                                                                    <tr>
                                                                        <th>ID</th>
                                                                        <th>Désignation</th>
                                                                        <th>Catégorie</th>
                                                                        <th>État</th>
                                                                        <th>Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>#1</td>
                                                                        <td>Laptop HP ProBook</td>
                                                                        <td>Laptop personnel</td>
                                                                        <td><span class="badge badge-success">En service</span></td>
                                                                        <td>
                                                                            <button class="btn-table"><i class="fa-solid fa-pen"></i></button>
                                                                            <button class="btn-table btn-danger"><i class="fa-solid fa-trash"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>#2</td>
                                                                        <td>Serveur Dell R740</td>
                                                                        <td>Serveur</td>
                                                                        <td><span class="badge badge-warning">Maintenance</span></td>
                                                                        <td>
                                                                            <button class="btn-table"><i class="fa-solid fa-pen"></i></button>
                                                                            <button class="btn-table btn-danger"><i class="fa-solid fa-trash"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                    <!-- Remplace par ton PHP (boucle) -->
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </section>
                                            </main>

                                            <!-- Bouton flottant pour ajouter -->
                                            <button class="fab">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- JS -->
                                    <script src="assets/js/app.js"></script>
                                </body>
                            </html>
