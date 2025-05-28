<?php
session_start();
include 'config.php';

$message = "";


if (isset($_POST['ajouter'])) {
    $nouvelle_annee = $_POST['annee'];

    // Désactiver toutes les anciennes années
    mysqli_query($conn, "UPDATE annee_academique SET est_active = 0");

    // Ajouter la nouvelle année comme active
    $insert = mysqli_query($conn, "INSERT INTO annee_academique (annee, est_active) VALUES ('$nouvelle_annee', 1)");

    if ($insert) {
        mysqli_query($conn, "DELETE FROM cours");
        mysqli_query($conn, "DELETE FROM informations");
        $message = "Nouvelle année académique définie avec succès. Anciennes données supprimées.";
    } else {
        $message = "Erreur lors de la mise à jour.";
    }
}

$active = mysqli_query($conn, "SELECT * FROM annee_academique WHERE est_active = 1 ORDER BY id DESC LIMIT 1");
$annee_active = mysqli_fetch_assoc($active);

$archives = mysqli_query($conn, "SELECT * FROM annee_academique WHERE est_active = 0 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<link rel="stylesheet" href="../css/gerer_annee_academique.css">
<head>
    <title>Gérer Année Académique</title>
    
</head>
<body>
<div class="container">
    <h2>Gérer l'année académique</h2>

    <?php if ($message) echo "<p class='success'>$message</p>"; ?>

    <form method="post">
        <label>Nouvelle année académique :</label>
        <input type="text" name="annee" placeholder="ex : 2025/2026" required>
        <button type="submit" name="ajouter">Renouveler année d'étude</button>
    </form>

    <div class="section">
        <h3>Année académique active :</h3>
        <?php if ($annee_active): ?>
            <p><strong><?= htmlspecialchars($annee_active['annee']) ?></strong></p>
        <?php else: ?>
            <p>Aucune année académique active.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h3>Anciennes années académiques (archives) :</h3>
        <ul>
        <?php while ($row = mysqli_fetch_assoc($archives)): ?>
            <li><?= htmlspecialchars($row['annee']) ?> — créée le <?= date("d/m/Y", strtotime($row['date_creation'])) ?></li>
        <?php endwhile; ?>
        </ul>
    </div>
</div>
</body>
</html>