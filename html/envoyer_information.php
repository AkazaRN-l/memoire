<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['contenu']);
    $niveau = htmlspecialchars($_POST['niveau']);
    $date_envoi = date('Y-m-d H:i:s');

    // Gestion du fichier
    $fichier_nom = null;
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $fichier_tmp = $_FILES['fichier']['tmp_name'];
        $fichier_nom_original = basename($_FILES['fichier']['name']);
        $fichier_nom = time() . "_" . $fichier_nom_original;
        $destination = "../uploads/" . $fichier_nom;

        // Déplacement du fichier
        if (!move_uploaded_file($fichier_tmp, $destination)) {
            $_SESSION['message'] = "Erreur lors de l’envoi du fichier.";
            header("Location: envoyer_information.php");
            exit;
        }
    }

    // Insertion dans la base de données
    $stmt = $conn->prepare("INSERT INTO informations (titre, contenu, fichier, niveau, date_envoi) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $titre, $description, $fichier_nom, $niveau, $date_envoi);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Information envoyée avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de l’envoi de l’information.";
    }

    $stmt->close();
    $conn->close();

    header("Location: envoyer_information.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Envoyer une Information</title>
    <link rel="stylesheet" href="../css/envoyer_information.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="header-animation">
    <h1 class="animated-title">Envoyer Information</h1>
</div>

<div class="form-premium">
    <?php if (isset($_GET['success'])) { ?>
        <div class="success-message">
            ✅ Information envoyée avec succès !
        </div>
    <?php } ?>

    <form action="envoyer_information.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label>Titre :</label>
        <input type="text" name="titre" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Message :</label>
        <textarea name="contenu" class="form-control" required></textarea>
    </div>

    <div class="form-group">
        <label>Choisir le niveau :</label>
        <select name="niveau" class="form-control" required>
            <option value="Licence I">Licence I</option>
            <option value="Licence II">Licence II</option>
            <option value="Licence III">Licence III</option>
            <option value="Master I">Master I</option>
            <option value="Master II">Master II</option>
        </select>
    </div>

    <!-- Champ fichier -->
    <div class="form-fichier">
        <label>Fichier :</label>
        <input type="file" name="fichier" class="form-control">
    </div>

    <button type="submit" class="btn-gold">
        <span>📨</span>
        <span>Envoyer l'Information</span>
    </button>

        <a href="dashboard_chef.php" class="btn-retour-premium">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Retour
        </a>

        <div class="navigation-actions">
    <a href="voir_information.php" class="info_envoyer">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
        Informations envoyées
    </a>
</div>
</div>

<script src="../js/envoyer_information.js"></script>
</body>
</html>