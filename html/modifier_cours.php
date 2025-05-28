<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['enseignants'])) {
    header('Location: login_enseignant.php');
    exit();
}

// Vérifier si l'ID du cours est présent dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de cours invalide";
    header('Location: dashboard_enseignant.php');
    exit();
}

$cours_id = intval($_GET['id']);
$enseignant_id = $_SESSION['enseignants']['id'];

require_once __DIR__ . '/db.php';

// Récupérer les données du cours à modifier
try {
    $stmt = $pdo->prepare("SELECT c.*, m.niveau 
                          FROM cours c
                          JOIN matieres m ON c.matiere_id = m.id
                          WHERE c.id = ? AND c.enseignant_id = ?");
    $stmt->execute([$cours_id, $enseignant_id]);
    $cours = $stmt->fetch();

    if (!$cours) {
        $_SESSION['error'] = "Cours non trouvé ou vous n'avez pas les droits pour le modifier";
        header('Location: dashboard_enseignant.php');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $niveau = $_POST['niveau'];
    $matiere_id = intval($_POST['matiere_id']);

    // Validation des données
    if (empty($titre)) {
        $error = "Le titre du cours est obligatoire";
    } else {
        try {
            // Commencer une transaction
            $pdo->beginTransaction();

            // Mettre à jour les informations de base du cours
            $stmt = $pdo->prepare("UPDATE cours 
                                  SET titre = ?, description = ?, matiere_id = ?
                                  WHERE id = ? AND enseignant_id = ?");
            $stmt->execute([$titre, $description, $matiere_id, $cours_id, $enseignant_id]);

            // Gestion du fichier uploadé (si un nouveau fichier est fourni)
            if (!empty($_FILES['fichier']['name'])) {
                $file_name = basename($_FILES['fichier']['name']);
                $file_tmp = $_FILES['fichier']['tmp_name'];
                $file_size = $_FILES['fichier']['size'];
                $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Valider le fichier
                $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception("Type de fichier non autorisé");
                }

                if ($file_size > 20000000) { // 20MB max
                    throw new Exception("Fichier trop volumineux (max 20MB)");
                }

                // Supprimer l'ancien fichier s'il existe
                if (!empty($cours['fichier_nom'])) {
                    $old_file = __DIR__ . '/uploads/' . $cours['fichier_nom'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }

                // Générer un nom de fichier unique
                $new_filename = uniqid() . '.' . $file_type;
                $upload_path = __DIR__ . '/uploads/' . $new_filename;

                // Déplacer le fichier uploadé
                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    throw new Exception("Erreur lors de l'upload du fichier");
                }

                // Mettre à jour le nom du fichier dans la base
                $stmt = $pdo->prepare("UPDATE cours SET fichier_nom = ? WHERE id = ?");
                $stmt->execute([$new_filename, $cours_id]);
            }

            // Valider la transaction
            $pdo->commit();

            $_SESSION['success'] = "Le cours a été mis à jour avec succès";
            header('Location: dashboard_enseignant.php');
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la modification: " . $e->getMessage();
        }
    }
}

// Récupérer les matières pour le niveau du cours
try {
    $stmt = $pdo->prepare("SELECT id, nom FROM matieres WHERE niveau = ? ORDER BY nom");
    $stmt->execute([$cours['niveau']]);
    $matieres = $stmt->fetchAll();
} catch (PDOException $e) {
    $matieres = [];
    $error = "Erreur de chargement des matières";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le cours - <?= htmlspecialchars($cours['titre']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/modifier_cours.css">
</head>
<body class="modifier-page">
    <div class="dashboard-container">
        <main class="dashboard-main">
            <a href="dashboard_enseignant.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <header>
                <h1><i class="fas fa-edit"></i> Modifier le cours</h1>
            </header>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="modifier_cours.php?id=<?= $cours_id ?>" method="POST" enctype="multipart/form-data" class="course-form">
                <div class="form-group">
                    <label for="titre">Titre du cours</label>
                    <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($cours['titre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?= htmlspecialchars($cours['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Niveau</label>
                    <div class="niveau-display">
                        <?= htmlspecialchars($cours['niveau']) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="matiere_id">Matière</label>
                    <select id="matiere_id" name="matiere_id" required>
                        <?php foreach ($matieres as $matiere): ?>
                            <option value="<?= $matiere['id'] ?>" <?= $matiere['id'] == $cours['matiere_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($matiere['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
    <label class="file-upload-label">
        <i class="fas fa-file-upload"></i> Nouveau fichier (optionnel)
    </label>
    
    <div class="file-upload-wrapper">
        <input type="file" id="fichier" name="fichier" class="file-input">
        <label for="fichier" class="file-upload-design">
            <i class="fas fa-cloud-upload-alt upload-icon"></i>
            <span class="upload-text">cliquez pour parcourir</span>
        </label>
    </div>
    
    <?php if (!empty($cours['fichier_nom'])): ?>
    <div class="current-file-display">
        <i class="fas fa-file-alt file-icon"></i>
        <div class="file-details">
            <span class="file-name"><?= htmlspecialchars($cours['fichier_nom']) ?></span>
            <span class="file-size">Fichier actuel</span>
        </div>
        <button type="button" class="file-clear-btn" onclick="document.getElementById('fichier').value=''">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>
</div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
    // Afficher le nom du fichier sélectionné
    document.getElementById('fichier').addEventListener('change', function(e) {
        if (this.files.length > 0) {
            const fileName = this.files[0].name;
            const fileSize = (this.files[0].size / (1024 * 1024)).toFixed(2); // en MB
            alert(`Fichier sélectionné: ${fileName} (${fileSize} MB)`);
        }
    });
    </script>
</body>
</html>