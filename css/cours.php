<?php
session_start();
include 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}

// Vérifier si l'utilisateur est un étudiant validé
$sql = "SELECT statut FROM etudiants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($statut);
$stmt->fetch();
$stmt->close();

if ($statut !== 'validé') {
    die("Accès refusé. Votre compte n'est pas encore validé.");
}

// Récupérer les cours
$sql = "SELECT * FROM cours";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cours Disponibles</title>
</head>
<body>
    <h2>Liste des Cours Disponibles</h2>
    <table border="1">
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Téléchargement</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['titre']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><a href="<?= htmlspecialchars($row['fichier']) ?>" download>📥 Télécharger</a></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>