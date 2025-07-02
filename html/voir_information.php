<?php
session_start();
include 'config.php';

// Vérification des permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    header("Location: login.php");
    exit();
}

// Traitement de la suppression
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM informations WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Information supprimée avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression: " . $stmt->error;
    }
    
    header("Location: voir_information.php");
    exit();
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $id = $_POST['id'];
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    
    $stmt = $conn->prepare("UPDATE informations SET titre = ?, contenu = ? WHERE id = ?");
    $stmt->bind_param("ssi", $titre, $contenu, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Information mise à jour avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour: " . $stmt->error;
    }
    
    header("Location: voir_information.php");
    exit();
}

// Récupération des informations
$niveau_selected = $_GET['niveau'] ?? 'all';
$sql = "SELECT id, titre, contenu, niveau, date_creation FROM informations";
if ($niveau_selected !== 'all') {
    $sql .= " WHERE niveau = ?";
}
$sql .= " ORDER BY date_creation DESC";

$stmt = $conn->prepare($sql);
if ($niveau_selected !== 'all') {
    $stmt->bind_param("s", $niveau_selected);
}
$stmt->execute();
$result = $stmt->get_result();
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