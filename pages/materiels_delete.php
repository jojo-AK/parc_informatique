<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$messageErreur = "";

// Récupérer id
$id_materiel = $_GET["id"] ?? null;

if ($id_materiel === null) {
    die("Matériel non spécifié.");
}

// Charger le matériel
$sql = "SELECT * FROM materiel WHERE id_materiel = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id_materiel]);
$materiel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materiel) {
    die("Matériel introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérifier si le matériel est utilisé dans affectation ou panne_maintenance
    $sqlAff = "SELECT COUNT(*) FROM affectation WHERE id_materiel = :id";
    $stmt = $pdo->prepare($sqlAff);
    $stmt->execute([":id" => $id_materiel]);
    $nbAffectations = $stmt->fetchColumn();

    $sqlPanne = "SELECT COUNT(*) FROM panne_maintenance WHERE id_materiel = :id";
    $stmt = $pdo->prepare($sqlPanne);
    $stmt->execute([":id" => $id_materiel]);
    $nbPannes = $stmt->fetchColumn();

    if ($nbAffectations > 0 || $nbPannes > 0) {
        $messageErreur = "Impossible de supprimer ce matériel car il est lié à des affectations ou des pannes.";
    } else {
        // On peut supprimer
        $sqlDel = "DELETE FROM materiel WHERE id_materiel = :id";
        $stmt = $pdo->prepare($sqlDel);
        $stmt->execute([":id" => $id_materiel]);

        header("Location: materiels_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression d'un matériel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>Suppression d'un matériel</h1>

<div class="menu">
    <a href="dashboard.php" class="btn">Dashboard</a>
    <a href="materiels_list.php" class="btn active">Matériel</a>
    <a href="categories_list.php" class="btn">Catégories</a>
    <a href="utilisateurs_list.php" class="btn">Utilisateurs</a>
    <a href="affectations_list.php" class="btn">Affectations</a>
    <a href="pannes_list.php" class="btn">Pannes</a>
    <a href="../logout.php" class="btn">Déconnexion</a>
</div>

<div class="form-card">
    <?php if (!empty($messageErreur)): ?>
        <p style="color:red;"><?= htmlspecialchars($messageErreur) ?></p>
        <p>
            <a href="materiels_list.php" class="btn">← Retour à la liste</a>
        </p>
    <?php else: ?>
        <p>Vous êtes sur le point de supprimer le matériel suivant :</p>
        <p><strong>Code :</strong> <?= htmlspecialchars($materiel["code_inventaire"]) ?></p>
        <p><strong>Désignation :</strong> <?= htmlspecialchars($materiel["designation"]) ?></p>

        <form method="post">
            <p>Confirmez-vous la suppression ?</p>
            <button type="submit" class="btn primary">Oui, supprimer</button>
            <a href="materiels_list.php" class="btn">Annuler</a>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
