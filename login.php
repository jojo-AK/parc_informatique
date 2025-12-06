<?php
session_start();

// Identifiants "admin" codés en dur pour simplifier le projet
$ADMIN_USER = "admin";
$ADMIN_PASS = "admin123"; // à changer si tu veux

$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION["admin_logged_in"] = true;
        $_SESSION["admin_username"]  = $username;

        header("Location: pages/dashboard.php");
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
    <title>Connexion administrateur</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h1>Connexion administrateur</h1>

<div class="form-card" style="max-width:400px;margin:0 auto;">
    <?php if (!empty($messageErreur)): ?>
        <p style="color:red;"><?= htmlspecialchars($messageErreur) ?></p>
    <?php endif; ?>

    <form method="post">
        <div class="form-row">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-row">
            <label>Mot de passe</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-row">
            <button type="submit" class="btn primary">Se connecter</button>
        </div>
    </form>
</div>

</body>
</html>
