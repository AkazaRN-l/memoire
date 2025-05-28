<?php
session_start();
require 'config.php';

// Vérification de session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['niveau'])) {
    header("Location: login.php");
    exit();
}

$user_niveau = $_SESSION['niveau'];
$user_name = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$role = $_SESSION['role'];

// Requête pour récupérer les matières du niveau
$sql = "SELECT id, nom FROM matieres WHERE niveau = ? ORDER BY nom";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_niveau);
$stmt->execute();
$matieres = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Liste des matières - <?= htmlspecialchars($user_niveau) ?></title>
</head>
<body>
    <h1>Matières disponibles pour <?= htmlspecialchars($user_niveau) ?></h1>
    <p>Connecté : <?= htmlspecialchars($user_name) ?> (<?= htmlspecialchars($role) ?>)</p>
    <a href="<?= ($role === 'etudiant') ? 'dashboard_etudiant.php' : 'dashboard_prof.php' ?>">Retour au tableau de bord</a>

    <?php if (!empty($matieres)): ?>
        <ul>
            <?php foreach ($matieres as $matiere): ?>
                <li>
                    <a href="forum.php?matiere_id=<?= urlencode($matiere['id']) ?>">
                        <?= htmlspecialchars($matiere['nom']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucune matière disponible pour votre niveau.</p>
    <?php endif; ?>
</body>
</html>
