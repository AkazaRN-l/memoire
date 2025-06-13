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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des matières - <?= htmlspecialchars($user_niveau) ?></title>
    <link rel="stylesheet" href="../css/liste_matieres.css">
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Matières disponibles pour <?= htmlspecialchars($user_niveau) ?></h1>
            <div class="user-info">
                <p class="user-details">Connecté : <span class="user-name"><?= htmlspecialchars($user_name) ?></span> <span class="user-role">(<?= htmlspecialchars($role) ?>)</span></p>
                <a href="<?= ($role === 'etudiant') ? 'dashboard_etudiant.php' : 'dashboard_enseignant.php' ?>" class="btn btn-back">Retour au tableau de bord</a>
            </div>
        </header>

        <main class="content">
            <?php if (!empty($matieres)): ?>
                <ul class="matieres-list">
                    <?php foreach ($matieres as $matiere): ?>
                        <li class="matiere-item">
                            <a href="forum.php?matiere_id=<?= urlencode($matiere['id']) ?>" class="matiere-link">
                                <?= htmlspecialchars($matiere['nom']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty-message">Aucune matière disponible pour votre niveau.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>