<?php
session_start();
require_once('config.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login_etudiant.php");
    exit();
}

$matiere_id = intval($_GET['id'] ?? 0);

// Récupérer l'année académique active **AVANT**
$sql_annee = "SELECT id FROM annee_academique WHERE est_active = 1 LIMIT 1";
$result_annee = $conn->query($sql_annee);
$annee_active = $result_annee->fetch_assoc();
$annee_id = $annee_active ? $annee_active['id'] : 0;

// Récupérer le nom de la matière
$sql_matiere = "SELECT nom FROM matieres WHERE id = ?";
$stmt = $conn->prepare($sql_matiere);
$stmt->bind_param("i", $matiere_id);
$stmt->execute();
$matiere = $stmt->get_result()->fetch_assoc();

// Requête cours avec les deux paramètres
$sql_cours = "SELECT c.id, c.titre, c.description, c.fichier_nom, c.date_envoi, e.nom, e.prenom 
              FROM cours c
              JOIN enseignants e ON c.enseignant_id = e.id
              WHERE c.matiere_id = ? AND c.annee_id = ?
              ORDER BY c.date_envoi DESC";
$stmt_cours = $conn->prepare($sql_cours);
$stmt_cours->bind_param("ii", $matiere_id, $annee_id);
$stmt_cours->execute();
$result_cours = $stmt_cours->get_result();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cours - <?= htmlspecialchars($matiere['nom']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/cours_matieres.css">
<script src="../js/cours_matiere.js" defer></script>
</head>
<body>
<div class="dashboard-container">
    <main class="dashboard-main">
   
    <a href="telecommunication/html/dashboard_etudiant.php" class="back-button">Retour</a>
     

    <header>
        <h1>📚 <?= htmlspecialchars($matiere['nom']) ?></h1>
    </header>
    ...



    <section class="courses-stream">
    <h2>📚 Cours disponibles</h2>
    
    <?php if ($result_cours->num_rows > 0): ?>
        <div class="courses-timeline">
            <?php while ($c = $result_cours->fetch_assoc()): ?>
                <div class="course-item">
                    <div class="course-main">
                        <h3 class="course-title"><?= htmlspecialchars($c['titre']) ?></h3>
                        <div class="course-meta">
                            <span class="teacher">Par <?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></span>
                            <span class="date"><?= htmlspecialchars($c['date_envoi']) ?></span>
                        </div>
                        
                        <?php if (!empty($c['description'])): ?>
                            <p class="course-desc"><?= nl2br(htmlspecialchars($c['description'])) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-file">
                        <span class="file-name">📄 <?= htmlspecialchars($c['fichier_nom']) ?></span>
                        <a href="telecharger.php?id=<?= intval($c['id']) ?>" class="download-link">
                            Télécharger <span>↓</span>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-courses-msg">Aucun cours disponible pour cette matière.</p>
    <?php endif; ?>
</section>
    </main>
</div>



<script>
document.querySelector('.back-button').addEventListener('click', function(e) {
    e.preventDefault();
    this.classList.add('animate__animated', 'animate__fadeOutLeft');
    setTimeout(() => {
        window.history.back();
    }, 500);
});
</script>
</body>
</html>