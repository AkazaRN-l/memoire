<?php
session_start();
include 'config.php';

// Vérification des permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    header("Location: login.php");
    exit();
}

// Récupération du niveau avec sécurité
$niveau_selected = isset($_GET['niveau']) ? $_GET['niveau'] : 'all';

// Récupération des informations
$sql = "SELECT id, titre, contenu, niveau, date_creation FROM informations";
if ($niveau_selected !== 'all') {
    $sql .= " WHERE niveau = ?";
}
$sql .= " ORDER BY date_creation DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Erreur de préparation de la requête: " . $conn->error);
}

if ($niveau_selected !== 'all') {
    $stmt->bind_param("s", $niveau_selected);
}

if (!$stmt->execute()) {
    die("Erreur d'exécution de la requête: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result === false) {
    die("Erreur de récupération des résultats: " . $conn->error);
}

// Récupérer l'année académique actuelle
$current_year = $conn->query("SELECT * FROM annee_academique WHERE est_active = TRUE LIMIT 1")->fetch_assoc();

// Traitement du formulaire de renouvellement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renouveler_annee'])) {
    // Démarrer une transaction SQL
    $conn->begin_transaction();

    try {
        // 1. Désactiver l'année actuelle
        $conn->query("UPDATE annee_academique SET est_actuelle = FALSE WHERE est_actuelle = TRUE");

        // 2. Créer une nouvelle année académique
        if ($current_year) {
            $years = explode('-', $current_year['annee']);
            $new_start_year = (int)$years[1]; // 2026-2027 → 2027
            $new_end_year = $new_start_year + 1; // 2028
            $new_year = $new_start_year . '-' . $new_end_year; // 2027-2028
        } else {
            $new_year = (date('Y') + 1) . '-' . (date('Y') + 2);
        }

        $new_start = date('Y-m-d');
        $new_end = date('Y-m-d', strtotime('+1 year'));

        $stmt = $conn->prepare("INSERT INTO annee_academique (annee, date_debut, date_fin, est_actuelle) VALUES (?, ?, ?, TRUE)");
        $stmt->bind_param("sss", $new_year, $new_start, $new_end);
        $stmt->execute();
        $new_year_id = $stmt->insert_id;

        // 3. ARCHIVAGE DES COURS (adapté à votre structure)
        $conn->query("
            INSERT INTO archives_cours (cours_id, titre, description, enseignant_id, annee_id)
            SELECT id, titre, description, enseignant_id, $new_year_id
            FROM cours
        ");
        $conn->query("DELETE FROM cours"); // Suppression après archivage

        // 4. ARCHIVAGE DES INFORMATIONS (adapté à votre structure)
        $conn->query("
            INSERT INTO archives_informations (info_id, titre, contenu, niveau, date_creation, annee_id)
            SELECT id, titre, contenu, niveau, date_creation, $new_year_id
            FROM informations
        ");
        $conn->query("DELETE FROM informations"); // Suppression après archivage

        // Valider la transaction
        $conn->commit();
        $_SESSION['success'] = "✅ Année renouvelée avec succès! Toutes les données ont été archivées.";
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        $conn->rollback();
        $_SESSION['error'] = "❌ Erreur: " . $e->getMessage();
    }

    header("Location: gerer_annee_academique.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Informations</title>
    <link rel="stylesheet" href="../css/voir_information.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="content">
                <h1>📨 Informations Envoyées</h1>
                
                <?php 
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert success">'.htmlspecialchars($_SESSION['success']).'</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert error">'.htmlspecialchars($_SESSION['error']).'</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <div class="filter-bar">
                    <form method="GET" action="voir_information.php">
                        <select name="niveau" id="niveau-filter" class="niveau-filter">
                            <option value="all" <?= $niveau_selected === 'all' ? 'selected' : '' ?>>Tous les niveaux</option>
                            <option value="Licence I" <?= $niveau_selected === 'Licence I' ? 'selected' : '' ?>>Licence I</option>
                            <option value="Licence II" <?= $niveau_selected === 'Licence II' ? 'selected' : '' ?>>Licence II</option>
                            <option value="Licence III" <?= $niveau_selected === 'Licence III' ? 'selected' : '' ?>>Licence III</option>
                            <option value="Master I" <?= $niveau_selected === 'Master I' ? 'selected' : '' ?>>Master I</option>
                            <option value="Master II" <?= $niveau_selected === 'Master II' ? 'selected' : '' ?>>Master II</option>
                        </select>
                        <button type="submit" class="btn-filter">Filtrer</button>
                    </form>
                </div>
                
                <div class="info-grid">
                    <?php while ($info = $result->fetch_assoc()): ?>
                    <div class="info-card" data-id="<?= $info['id'] ?>">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($info['titre']) ?></h3>
                            <span class="niveau-badge"><?= htmlspecialchars($info['niveau']) ?></span>
                        </div>
                        
                        <div class="card-body">
    <p><?= nl2br($info['contenu']) ?></p>
</div>
                        
                        <div class="card-footer">
                        <span class="date-publi">
  Publié le <?= date('d/m/Y à H:i', strtotime($info['date_creation'])) ?>
</span>
      <div>
      <button class="btn-action btn-modifier" onclick="toggleEdit(<?= $info['id'] ?>)">Modifier</button>
        <a href="supprimer_info.php?id=<?= $info['id'] ?>" class="btn-action btn-supprimer" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
    </div>
</div>
                        
                        <form class="edit-form" id="edit-form-<?= $info['id'] ?>" method="POST">
                            <input type="hidden" name="id" value="<?= $info['id'] ?>">
                            <div class="form-group">
                                <input type="text" name="titre" value="<?= htmlspecialchars($info['titre']) ?>" required>
                            </div>
                            <div class="form-group">
                                <textarea name="contenu" required><?= htmlspecialchars($info['contenu']) ?></textarea>
                            </div>
                            <button type="submit" name="update_info" class="btn-save">Enregistrer</button>
                            <button type="button" class="btn-cancel" onclick="toggleEdit(<?= $info['id'] ?>)">Annuler</button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <a class="btn-retour" href="envoyer_information.php">retour</a>

    <script>
    // voir_information.js
    function toggleEdit(id) {
        const card = document.querySelector(`.info-card[data-id="${id}"]`);
        const form = card.querySelector('.edit-form');
        const cardBody = card.querySelector('.card-body');
        
        if (form.style.display === 'block') {
            form.style.display = 'none';
            cardBody.style.display = 'block';
        } else {
            form.style.display = 'block';
            cardBody.style.display = 'none';
        }
    }

    // Initialisation - cacher tous les formulaires d'édition au chargement
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-form').forEach(form => {
            form.style.display = 'none';
        });
    });
    </script>
   
<div class="notification-center"></div>
</body>
</html>