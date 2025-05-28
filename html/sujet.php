<?php
session_start();
include("connexion.php");

$sujet_id = $_GET['id'];
$etudiant_id = $_SESSION['id'];

$reqSujet = $conn->prepare("SELECT s.*, u.nom, u.prenom FROM forum_sujets s JOIN users u ON u.id = s.etudiant_id WHERE s.id = ?");
$reqSujet->execute([$sujet_id]);
$sujet = $reqSujet->fetch();

$reqReponses = $conn->prepare("SELECT r.*, u.nom, u.prenom FROM forum_reponses r JOIN users u ON u.id = r.etudiant_id WHERE r.sujet_id = ? ORDER BY r.date_reponse ASC");
$reqReponses->execute([$sujet_id]);
$reponses = $reqReponses->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contenu = $_POST['contenu'];
    $stmt = $conn->prepare("INSERT INTO forum_reponses (sujet_id, etudiant_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$sujet_id, $etudiant_id, $contenu]);
    header("Location: sujet.php?id=" . $sujet_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($sujet['titre']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sujet-card">
        <h2><?= htmlspecialchars($sujet['titre']) ?></h2>
        <p><?= nl2br(htmlspecialchars($sujet['contenu'])) ?></p>
        <p><em>Posté par <?= htmlspecialchars($sujet['nom']) ?> <?= htmlspecialchars($sujet['prenom']) ?> le <?= $sujet['date_creation'] ?></em></p>
    </div>

    <h3>Réponses</h3>
    <?php foreach ($reponses as $rep): ?>
        <div class="reponse-card">
            <p><?= nl2br(htmlspecialchars($rep['contenu'])) ?></p>
            <small>Par <?= htmlspecialchars($rep['nom']) ?> <?= htmlspecialchars($rep['prenom']) ?> le <?= $rep['date_reponse'] ?></small>
        </div>
    <?php endforeach; ?>

    <h3>Répondre</h3>
    <form method="POST">
        <textarea name="contenu" placeholder="Votre réponse..." required></textarea><br>
        <button type="submit" class="btn-noble">Répondre</button>
    </form>
</body>
</html>