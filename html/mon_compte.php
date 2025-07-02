<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mise à jour du nom et prénom
    if (isset($_POST['update_names'])) {
        $new_nom = trim($_POST['new_nom']);
        $new_prenom = trim($_POST['new_prenom']);

        if (empty($new_nom) || empty($new_prenom)) {
            $msg = "Le nom et le prénom ne peuvent pas être vides.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET nom = ?, prenom = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_nom, $new_prenom, $user_id);
            if ($stmt->execute()) {
                $msg = "Nom et prénom mis à jour avec succès.";
            } else {
                $msg = "Erreur lors de la mise à jour du nom et prénom.";
            }
        }
    }

    // Mise à jour de l'email
    if (isset($_POST['update_email'])) {
        $new_email = trim($_POST['new_email']);

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $msg = "Adresse email invalide.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $new_email, $user_id);
            if ($stmt->execute()) {
                $msg = "Email mis à jour avec succès.";
            } else {
                $msg = "Erreur lors de la mise à jour de l'email.";
            }
        }
    }

    // Mise à jour du mot de passe
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['mot_de_passe'])) {
            $msg = "Mot de passe actuel incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $msg = "Les nouveaux mots de passe ne correspondent pas.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $msg = "Mot de passe mis à jour avec succès.";
            } else {
                $msg = "Erreur lors de la mise à jour du mot de passe.";
            }
        }
    }
}

// Récupération des infos utilisateur pour affichage
$stmt = $conn->prepare("SELECT nom, prenom, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Responsable de Mention</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/mon_compte.css">
    <link rel="stylesheet" href="../css/dashboard_chef.css">
</head>
<body>
<div class="account-container">
<aside class="sidebar">

<img src="../photo/Logo_Mention.jpg" alt="Logo Chef" style="width: 50px; display: block; margin: 0 auto 10px;">
    <h2>Responsable de Mention</h2>
    <nav>
        <ul>
            <li><a href="dashboard_chef.php?niveau=Licence I">Licence I</a></li>
            <li><a href="dashboard_chef.php?niveau=Licence II">Licence II</a></li>
            <li><a href="dashboard_chef.php?niveau=Licence III">Licence III</a></li>
            <li><a href="dashboard_chef.php?niveau=Master I">Master I</a></li>
            <li><a href="dashboard_chef.php?niveau=Master II">Master II</a></li>
            <li><a href="gerer_enseignants.php">🏫 Gérer Enseignants</a></li>
            <li><a href="envoyer_information.php">📢 Envoyer Information</a></li>
            <li><a href="gerer_annee_academique.php">📅 Gérer Année Académique</a></li>
            <li><a href="mon_compte.php">⚙️ Mon compte</a></li>
            <li><a href="logout_chef.php">🚪 Déconnexion</a></li>
        </ul>
    </nav>
</aside>
    </div>

    <div class="account-content">
        <div class="account-sidebar">
            <div class="profile-header">
                <div class="avatar"><?php echo strtoupper(substr($user['prenom'], 0, 1) . strtoupper(substr($user['nom'], 0, 1))); ?></div>
                <h2><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        <div class="main-content-wrapper">
        <div class="account-content">
             <h1><i class="fas fa-user-cog"></i> Paramètres du compte</h1>
            
            <?php if ($msg): ?>
                <div class="alert <?php echo strpos($msg, 'succès') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <div class="settings-section">
        <h2><i class="fas fa-user-edit"></i> Modifier le nom et prénom</h2>
        <form method="POST" class="account-form">
            <div class="form-group">
                <label for="new_nom">Nouveau nom</label>
                <input type="text" id="new_nom" name="new_nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="new_prenom">Nouveau prénom</label>
                <input type="text" id="new_prenom" name="new_prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
            </div>
            
            <button type="submit" name="update_names" class="btn btn-primary">
                <i class="fas fa-save"></i> Mettre à jour
            </button>
            </form>
<div class="settings-section">
                <h2><i class="fas fa-envelope"></i> Modifier l'email</h2>
                <form method="POST" class="account-form">
                    <div class="form-group">
                        <label for="new_email">Nouvel email</label>
                        <input type="email" id="new_email" name="new_email" required>
                    </div>
                    <button type="submit" name="update_email" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour l'email
                    </button>
                </form>
            </div>




       
    </div>
            <div class="settings-section">
    <h2><i class="fas fa-lock"></i> Modifier le mot de passe</h2>
    <form method="POST" class="account-form">
        <div class="form-group">
            <label for="current_password">Mot de passe actuel</label>
            <div class="password-input-container">
                <input type="password" id="current_password" name="current_password" required>
                <button type="button" class="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="new_password">Nouveau mot de passe</label>
            <div class="password-input-container">
                <input type="password" id="new_password" name="new_password" required>
                <button type="button" class="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <!-- Password strength meter will be inserted here by JS -->
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
            <div class="password-input-container">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <button type="button" class="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <!-- Password match feedback will be inserted here by JS -->
        </div>

        <button type="submit" name="update_password" class="btn btn-primary">
            <i class="fas fa-key"></i> Mettre à jour le mot de passe
        </button>
    </form>
        </div>
    </div>
</div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="../js/mon_compte.js"></script>
</body>
</html>