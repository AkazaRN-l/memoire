<?php
session_start();
require 'config.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $matricule = trim($_POST['numero_matricule']);

    $sql = "SELECT * FROM enseignants WHERE email = ? AND numero_matricule = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $matricule);
    
    if ($stmt->execute()) {
        $enseignant = $stmt->get_result()->fetch_assoc();
        if ($enseignant) {
            $_SESSION['enseignants'] = $enseignant;
            $_SESSION['user_id'] = $enseignant['id']; 
            $_SESSION['role'] = 'enseignant';
            header("Location: dashboard_enseignant.php");
            exit();

        } else {
            $error = "Identifiants invalides";
        }
    } else {
        $error = "Erreur système";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Enseignants | Télécom</title>
    <link rel="stylesheet" href="../css/login_enseignant.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Syne:wght@700&display=swap" rel="stylesheet">
    
    <!-- CSS Externe -->
    <link rel="stylesheet" href="../css/login_ensaignant.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Top -->
    <header class="univ-header">
        <h1 class="master-title">Mention Télécommunications</h1>
        <p class="master-subtitle">Université de Vakinankaratra</p>
    </header>

    <!-- Login Container -->
    <div class="login-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email académique" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-id-badge"></i>
                <input type="password" name="numero_matricule" placeholder="Numéro matricule" required>
            </div>
            
            <button type="submit" class="btn-login">
                <span>Se connecter</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <!-- JavaScript Externe -->
    <script src="../js/login_ensaignant.js"></script>
</body>
</html>