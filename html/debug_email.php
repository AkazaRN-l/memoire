<?php
session_start();
if (empty($_SESSION['email_debug'])) {
    header("Location: motdepasse_oublie.php");
    exit();
}

$debug_data = $_SESSION['email_debug'];
unset($_SESSION['email_debug']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Email</title>
    <link rel="stylesheet" href="../css/login_etudiant.css">
    <style>
        .debug-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .debug-header {
            color: #3498db;
            text-align: center;
            margin-bottom: 20px;
        }
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .debug-code {
            font-size: 24px;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 5px;
            color: #2c3e50;
        }
        .continue-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="debug-container">
            <h2 class="debug-header">Mode Développement</h2>
            
            <?php if (!empty($debug_data['message'])): ?>
                <div class="success-msg"><?= htmlspecialchars($debug_data['message']) ?></div>
            <?php endif; ?>
            
            <div class="debug-info">
                <p><strong>Destinataire (email) :</strong> <?= htmlspecialchars($debug_data['email']) ?></p>
                <p><strong>Téléphone enregistré :</strong> <?= htmlspecialchars($debug_data['telephone']) ?></p>
                <p>En production, ce code serait envoyé par email/SMS.</p>
            </div>
            
            <div class="debug-code">
                Votre code : <strong><?= htmlspecialchars($debug_data['code']) ?></strong>
            </div>
            
            <p>Ce code est valide 15 minutes.</p>
            
            <a href="verifier_code.php" class="btn continue-btn">
                Continuer vers la vérification
            </a>
        </div>
    </div>
</body>
</html>