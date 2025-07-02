<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Réussie</title>
    <link rel="stylesheet" href="../css/succes.css">
    <script>
        setTimeout(() => {
            window.location.href = "login_etudiant.php"; // Redirection automatique après 5s
        }, 10000);
    </script>
</head>
<body>

<div class="success-container">
    <div class="success-box">
        <i class="fas fa-check-circle"></i>
        <h2>🎉 Félicitations !</h2>
        <p>Votre inscription a été enregistrée avec succès.<br>Veuillez attendre la validation du Responsable de mention.<br>Vous recevrez un email dès que votre compte sera activé.</p>
        <a href="login_etudiant.php" class="btn">Se connecter</a>
        <p class="redirect-msg">Redirection automatique vers la connexion...</p>
    </div>
</div>

</body>
</html>