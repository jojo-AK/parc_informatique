<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$messageErreur = "";

$id_utilisateur = $_GET["id"] ?? null;

if ($id_utilisateur === null) {
    die("Utilisateur non spécifié.");
}

// Charger l'utilisateur
$sql = "SELECT * FROM utilisateur WHERE id_utilisateur = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id_utilisateur]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    die("Utilisateur introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérifier si l'utilisateur est utilisé dans une affectation
    $sqlAff = "SELECT COUNT(*) FROM affectation WHERE id_utilisateur = :id";
    $stmt = $pdo->prepare($sqlAff);
    $stmt->execute([":id" => $id_utilisateur]);
    $nbAffectations = $stmt->fetchColumn();

    if ($nbAffectations > 0) {
        $messageErreur = "Impossible de supprimer cet utilisateur car il est lié à des affectations.";
    } else {
        $sqlDel = "DELETE FROM utilisateur WHERE id_utilisateur = :id";
        $stmt = $pdo->prepare($sqlDel);
        $stmt->execute([":id" => $id_utilisateur]);

        header("Location: utilisateurs_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression d'un utilisateur</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>Suppression d'un utilisateur</h1>

<div class="menu">
    <a href="dashboard.php" class="btn">Dashboard</a>
    <a href="materiels_list.php" class="btn">Matériel</a>
    <a href="categories_list.php" class="btn">Catégories</a>
    <a href="utilisateurs_list.php" class="btn active">Utilisateurs</a>
    <a href="affectations_list.php" class="btn">Affectations</a>
    <a href="pannes_list.php" class="btn">Pannes</a>
    <a href="../logout.php" class="btn">Déconnexion</a>
</div>

<div class="form-card">
    <?php if (!empty($messageErreur)): ?>
        <p style="color:red;"><?= htmlspecialchars($messageErreur) ?></p>
        <p>
            <a href="utilisateurs_list.php" class="btn">← Retour à la liste</a>
        </p>
    <?php else: ?>
        <p>Vous êtes sur le point de supprimer l'utilisateur suivant :</p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($utilisateur["nom"]) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($utilisateur["prenom"]) ?></p>

        <form method="post">
            <p>Confirmez-vous la suppression ?</p>
            <button type="submit" class="btn primary">Oui, supprimer</button>
            <a href="utilisateurs_list.php" class="btn">Annuler</a>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
