<?php
require_once "../config/auth.php";
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom       = $_POST["nom"] ?? "";
    $prenom    = $_POST["prenom"] ?? "";
    $service   = $_POST["service"] ?? "";
    $email     = $_POST["email"] ?? "";
    $telephone = $_POST["telephone"] ?? "";

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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <h1>Ajouter un utilisateur</h1>

    <div class="menu">
        <a href="utilisateurs_list.php" class="btn">← Retour à la liste</a>
    </div>

    <?php if (!empty($messageErreur)): ?>
        <p style="color: red;"><?= htmlspecialchars($messageErreur) ?></p>
    <?php endif; ?>

    <form method="post" class="form-card">

        <div class="form-row">
            <label>Nom *</label>
            <input type="text" name="nom" required>
        </div>

        <div class="form-row">
            <label>Prénom *</label>
            <input type="text" name="prenom" required>
        </div>

        <div class="form-row">
            <label>Service</label>
            <input type="text" name="service">
        </div>

        <div class="form-row">
            <label>Email</label>
            <input type="email" name="email">
        </div>

        <div class="form-row">
            <label>Téléphone</label>
            <input type="text" name="telephone">
        </div>

        <div class="form-row">
            <button type="submit" class="btn primary">Enregistrer</button>
            <a href="utilisateurs_list.php" class="btn">Annuler</a>
        </div>

    </form>

</body>
</html>
