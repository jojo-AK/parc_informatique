<?php
session_start();

// Si déjà connecté, on redirige en fonction du rôle
if (!empty($_SESSION["logged_in"]) && !empty($_SESSION["user_role"])) {
    if ($_SESSION["user_role"] === "admin") {
        header("Location: pages/dashboard.php");
        exit;
    } elseif ($_SESSION["user_role"] === "viewer") {
        header("Location: pages/materiels_list.php");
        exit;
    }
}

// Identifiants codés en dur
$ADMIN_USER = "admin";
$ADMIN_PASS = "admin123";

$VIEW_USER = "user";
$VIEW_PASS = "user123";

$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION["logged_in"]  = true;
        $_SESSION["user_role"]  = "admin";
        $_SESSION["username"]   = $username;
        header("Location: pages/dashboard.php");
        exit;

    } elseif ($username === $VIEW_USER && $password === $VIEW_PASS) {
        $_SESSION["logged_in"]  = true;
        $_SESSION["user_role"]  = "viewer";
        $_SESSION["username"]   = $username;
        header("Location: pages/materiels_list.php");
        exit;

    } else {
        $messageErreur = "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Parc Informatique</title>

    <!-- Fonts + Icons -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Ton style principal -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Wrapper principal */
        .login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            padding: 2rem 1.5rem;
            gap: 2.2rem;
            align-items: center;
        }

        /* Partie gauche (logo + texte) */
        .login-hero {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            color: var(--text-main);
        }

        .logo-parc-it {
            width: 95px;
            height: 95px;
            border-radius: 999px;
            background: conic-gradient(from 140deg, #22c55e, #22d3ee, #6366f1, #ec4899, #22c55e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b1120;
            font-size: 2.2rem;
            box-shadow: 0 14px 40px rgba(79,70,229,0.8);
        }

        .login-hero h1 {
            font-size: 2rem;
            font-weight: 600;
        }

        .login-hero p {
            font-size: .95rem;
            color: var(--text-muted);
            max-width: 430px;
        }

        /* Carte de login (droite) */
        .login-card {
            background: var(--bg-glass);
            border-radius: 1.2rem;
            padding: 1.4rem 1.7rem;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(22px);
        }

        .login-card h2 {
            font-size: 1.35rem;
            margin-bottom: .6rem;
        }

        .alert-error {
            padding: .55rem .75rem;
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.7);
            border-radius: .6rem;
            color: #b91c1c;
            font-size: .83rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: .8rem;
        }

        .alert-error i {
            font-size: .9rem;
        }

        .login-form-group {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .login-form-group label {
            font-size: .8rem;
            margin-bottom: .25rem;
        }

        .login-form-group input {
            border-radius: .75rem;
            border: 1px solid rgba(148,163,184,0.75);
            padding: .5rem .75rem;
            font-size: .87rem;
            background: #ffffff;
            color: #111827;
            outline: none;
        }

        body.dark-mode .login-form-group input {
            background: rgba(15,23,42,0.92);
            color: var(--text-main);
        }

        .login-form-group input:focus {
            border-color: #38bdf8;
            box-shadow: 0 10px 26px rgba(56,189,248,0.5);
        }

        .login-footer {
            margin-top: 2.2rem;
            font-size: .78rem;
            color: var(--text-muted);
            text-align: center;
        }

        @media (max-width: 880px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                padding-top: 3rem;
                max-width: 480px;
                margin: auto;
            }

            .login-hero {
                text-align: center;
                align-items: center;
            }

            .login-hero p {
                max-width: none;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">

    <!-- Colonne gauche : logo + texte -->
    <section class="login-hero">
        <div class="logo-parc-it">
            <i class="fa-solid fa-laptop-code"></i>
        </div>

        <h1>Bienvenue sur Parc IT</h1>

        <p>
            Gérez efficacement les matériels, affectations et pannes de votre parc informatique
            via une interface moderne, fluide et intuitive.
        </p>

        <p style="font-size:.83rem; opacity:.7;">
            Optimisé • Sécurisé • Professionnel
        </p>
    </section>

    <!-- Colonne droite : carte de connexion -->
    <section class="login-card">

        <h2><i class="fa-solid fa-right-to-bracket"></i> Connexion</h2>

        <?php if (!empty($messageErreur)): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($messageErreur) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="login-form-group">
                <label for="username">Nom d'utilisateur</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    placeholder="admin ou user"
                    autocomplete="username"
                >
            </div>

            <div class="login-form-group">
                <label for="password">Mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Votre mot de passe"
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">
                <i class="fa-solid fa-lock"></i>
                <span>Se connecter</span>
            </button>
        </form>

        <!-- Identifiants de test -->
        <div style="
            margin-top: 1.4rem;
            padding: .9rem 1rem;
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56,189,248,0.45);
            border-radius: .75rem;
            font-size: .8rem;
            color: var(--text-main);
            line-height: 1.4;
        ">
            <strong><i class="fa-solid fa-key"></i> Identifiants de test :</strong><br>
            👑 <strong>Admin :</strong> admin / admin123<br>
            👤 <strong>Utilisateur :</strong> user / user123
        </div>

        <div class="login-footer">
            © <?= date("Y") ?> — Parc IT • Tous droits réservés
        </div>
    </section>
</div>

</body>
</html>
