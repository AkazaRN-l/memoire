<?php
session_start();
include 'config.php';

// Vérification des permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    header("Location: login_chef.php");
    exit();
}

// Récupérer l'historique des années
$sql = "SELECT DISTINCT annee_academique FROM archive_matieres ORDER BY annee_academique DESC";
$result = $conn->query($sql);
$annees = $result->fetch_all(MYSQLI_ASSOC);

// Récupérer l'année actuelle
$sql_actuelle = "SELECT valeur FROM configuration WHERE cle = 'annee_academique'";
$annee_actuelle = $conn->query($sql_actuelle)->fetch_assoc()['valeur'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Années Académiques</title>
    <link rel="stylesheet" href="../css/dashboard_chef.css">
    <style>
        /* [Garder les mêmes styles que précédemment] */
    </style>
</head>
<body>
    <div class="dashboard-container">
    <aside class="sidebar"  id="sidebar">
        <img src="../photo/satellite-dish-310868_1920.png" alt="Logo Chef" style="width: 50px; display: block; margin: 0 auto 10px;">
        <h2>Chef de Mention</h2>
        <div class="annee-academique">
            <h3>Année Académique: <?= $annee_academique ?></h3>
            <form method="POST">
                <button type="submit" name="nouvelle_annee" class="btn-nouvelle-annee" 
                        onclick="return confirm('Êtes-vous sûr de vouloir démarrer une nouvelle année académique? Tous les étudiants validés seront archivés.')">
                    🎓 Nouvelle Année
                </button>
            </form>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard_chef.php?niveau=Licence I">Licence I</a></li>
                <li><a href="dashboard_chef.php?niveau=Licence II">Licence II</a></li>
                <li><a href="dashboard_chef.php?niveau=Licence III">Licence III</a></li>
                <li><a href="dashboard_chef.php?niveau=Master I">Master I</a></li>
                <li><a href="dashboard_chef.php?niveau=Master II">Master II</a></li>
                <li><a href="gerer_enseignants.php">🏫 Gérer Enseignants</a></li>
                <li><a href="envoyer_information.php">📢 Envoyer Information</a></li>
                <li><a href="historique_annees.php">📅 Historique des Années</a></li>
                <li><a href="logout_chef.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>
    </aside>
        
        <main class="dashboard-main">
            <div class="historique-container">
                <div class="header">
                    <h1 class="titre">📅 Historique des Années Académiques</h1>
                    <a href="dashboard_chef.php" class="btn-retour">← Retour</a>
                </div>
                
                <div class="annee-actuelle-card">
                    <h3>Année en cours : <?= htmlspecialchars($annee_actuelle) ?></h3>
                </div>
                
                <h3>Années précédentes :</h3>
                
                <?php foreach ($annees as $annee): ?>
                    <div class="annee-card <?= $annee['annee_academique'] == $annee_actuelle ? 'annee-actuelle' : '' ?>">
                        <span><?= htmlspecialchars($annee['annee_academique']) ?></span>
                        <a href="details_annee.php?annee=<?= urlencode($annee['annee_academique']) ?>" class="btn-voir">
                            Voir les détails
                        </a>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($annees)): ?>
                    <p>Aucun historique d'années académiques disponible.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>