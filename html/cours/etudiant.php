<?php
// Activation des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Connexion à la base
require_once('db.php');

// Vérification de session
if (!isset($_SESSION["user_id"])) {
    header("Location: login_etudiant.php");
    exit();
}

// Vérification connexion
if (!isset($conn) || $conn->connect_error) {
    die("<div style='padding:20px;background:#fff3e0;border:2px solid #ff9800;'>
        La connexion à la base de données a échoué. Contactez l'administrateur.
    </div>");
}

// Récupération des données de session
$niveau = $_SESSION["niveau"] ?? '';
$nom = $_SESSION["nom"] ?? '';
$prenom = $_SESSION["prenom"] ?? '';
$matricule = $_SESSION["matricule"] ?? '';
$photo = $_SESSION["photo"] ?? '';

// Annonces
$sql_info = "SELECT * FROM informations WHERE niveau = ? ORDER BY date_envoi DESC";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("s", $niveau);
$stmt_info->execute();
$result_info = $stmt_info->get_result();
$stmt_info->close();

// Cours
$sql_cours = "SELECT c.id, c.titre, c.fichier_nom, c.date_envoi, e.nom, e.prenom 
              FROM cours c
              JOIN enseignants e ON c.enseignant_id = e.id
              WHERE c.niveau = ?
              ORDER BY c.date_envoi DESC";
$stmt_cours = $conn->prepare($sql_cours);
$stmt_cours->bind_param("s", $niveau);
$stmt_cours->execute();
$result_cours = $stmt_cours->get_result();
$stmt_cours->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Étudiant - <?= htmlspecialchars($niveau) ?></title>
    <link rel="stylesheet" href="../css/dashboard_etudiant.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Barre latérale -->
    <aside class="sidebar">
        <h2>🎓 Étudiant</h2>
        <nav>
            <ul>
                <li><a href="visioconference.php">🎥 Visioconférence</a></li>
                <li><a href="logout_etudiant.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <main class="dashboard-main">
        <header>
            <h1>📚 Espace Étudiant - <?= htmlspecialchars($niveau) ?></h1>
        </header>

        <!-- Profil étudiant -->
        <section class="profile-card">
            <img src="<?= htmlspecialchars($photo) ?>" class="profile-photo">
            <h2><?= htmlspecialchars($nom . " " . $prenom) ?></h2>
            <p>🎓 Numéro Matricule : <?= htmlspecialchars($matricule) ?></p>
        </section>

        <!-- Annonces -->
        <section class="info-section">
            <h2>📢 Annonces du Chef de Mention</h2>
            <?php if ($result_info->num_rows > 0): ?>
                <?php while ($info = $result_info->fetch_assoc()) : ?>
                    <div class="info-card">
                        <h3><?= htmlspecialchars($info["titre"]) ?></h3>
                        <p><?= nl2br(htmlspecialchars($info["contenu"])) ?></p>
                        <small>🕒 Envoyé le : <?= htmlspecialchars($info["date_envoi"]) ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Aucune annonce pour le moment.</p>
            <?php endif; ?>
        </section>

        <!-- Cours -->
        <section class="messages-section">
            <h2>📜 Messages et Fichiers des Enseignants</h2>
            <?php if ($result_cours->num_rows > 0): ?>
                <table class="cours-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Fichier</th>
                            <th>Enseignant</th>
                            <th>Date</th>
                            <th>Téléchargement</th>
                        </tr>
                    </thead>
                    <?php while ($c = $result_cours->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($c['titre']) ?></td>
        <td><?= htmlspecialchars($c['fichier_nom']) ?></td>
        <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
        <td><?= htmlspecialchars($c['date_envoi']) ?></td>
        <td>
            <!-- Lien pour télécharger le fichier -->
            <a href="telecharger.php?id=<?= intval($c['id']) ?>">Télécharger</a>
        </td>
    </tr>
<?php endwhile; ?>

                </table>
            <?php else: ?>
                <p>Aucun cours disponible pour votre niveau pour le moment.</p>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>