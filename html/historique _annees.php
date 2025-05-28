<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("Accès refusé");
}

// Récupérer toutes les années existantes
$annees = $conn->query("SELECT * FROM annee_academique ORDER BY annee DESC");

// Récupérer l’année sélectionnée
$annee_id = isset($_GET['annee_id']) ? intval($_GET['annee_id']) : 0;
$cours_archives = [];

if ($annee_id > 0) {
    $stmt = $conn->prepare("
        SELECT c.titre, c.niveau, c.description, c.date_envoi, m.nom AS matiere, e.nom AS enseignant_nom, e.prenom AS enseignant_prenom
        FROM cours c
        JOIN matieres m ON c.matiere_id = m.id
        JOIN enseignants e ON c.enseignant_id = e.id
        JOIN archives_cours a ON c.id = a.cours_id
        WHERE a.annee_id = ?
        ORDER BY c.date_envoi DESC
    ");
    $stmt->bind_param("i", $annee_id);
    $stmt->execute();
    $cours_archives = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des années académiques</title>
    <link rel="stylesheet" href="../css/dashboard_chef.css">
</head>
<body>
<div class="dashboard-container">
    <main class="dashboard-main">
        <h1>📅 Historique des Années Académiques</h1>

        <form method="GET">
            <label for="annee_id">Sélectionnez une année :</label>
            <select name="annee_id" id="annee_id" onchange="this.form.submit()">
                <option value="">-- Choisir une année --</option>
                <?php while ($a = $annees->fetch_assoc()): ?>
                    <option value="<?= $a['id'] ?>" <?= ($a['id'] == $annee_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['annee']) ?><?= $a['est_active'] ? ' (active)' : '' ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($annee_id && $cours_archives->num_rows > 0): ?>
            <h2>📚 Cours pour l’année <?= htmlspecialchars($_GET['annee_id']) ?></h2>
            <table class="matieres-table">
                <tr>
                    <th>Titre</th>
                    <th>Matière</th>
                    <th>Niveau</th>
                    <th>Enseignant</th>
                    <th>Description</th>
                    <th>Date d'envoi</th>
                </tr>
                <?php while ($c = $cours_archives->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['titre']) ?></td>
                        <td><?= htmlspecialchars($c['matiere']) ?></td>
                        <td><?= htmlspecialchars($c['niveau']) ?></td>
                        <td><?= htmlspecialchars($c['enseignant_prenom'] . ' ' . $c['enseignant_nom']) ?></td>
                        <td><?= nl2br(htmlspecialchars($c['description'])) ?></td>
                        <td><?= htmlspecialchars($c['date_envoi']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php elseif ($annee_id): ?>
            <p>Aucun cours archivé pour cette année.</p>
        <?php endif; ?>

        <br><a href="dashboard_chef.php">⬅ Retour au tableau de bord</a>
    </main>
</div>
</body>
</html>