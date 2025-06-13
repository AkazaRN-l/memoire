<?php
session_start();
require 'config.php';

// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST["matricule"]);
    $niveau = trim($_POST["niveau"]);

    $sql = "SELECT id, email, telephone FROM etudiants WHERE numero_inscription = ? AND niveau = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $matricule, $niveau);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Générer le code
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Sauvegarder dans la base
        $update = $conn->prepare("UPDATE etudiants SET reset_code = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
        $update->bind_param("si", $code, $user['id']);
        $update->execute();

        // Envoi de l'email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rahaja.ranjanirina@gmail.com'; 
            $mail->Password = 'lmgf mzzx nyut xseq';    
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rahaja.ranjanirina@gmail.com', 'Université de Vakinankaratra');
            $mail->addAddress($user['email']);
            $mail->isHTML(true);
            $mail->Subject = "Code de réinitialisation du mot de passe";
            $mail->Body = "Bonjour,<br><br>Voici votre code de réinitialisation : <strong style='font-size: 18px;'>$code</strong><br>Ce code expirera dans 15 minutes.<br><br>Université de Vakinankaratra.";

            $mail->send();

            // Stocker dans la session
            $_SESSION['success'] = "Un code a été envoyé à votre adresse email.";
            $_SESSION['email_temp'] = $user['email'];
            $_SESSION['reset_id'] = $user['id']; // ← ESSENTIEL POUR accéder à verifier_code.php

            header("Location: verifier_code.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'envoi du mail : " . $mail->ErrorInfo;
            header("Location: motdepasse_oublie.php");
            exit();
        }

    } else {
        $_SESSION['error'] = "Aucun compte trouvé avec ces informations.";
        header("Location: motdepasse_oublie.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié | Télécommunications</title>
    <link rel="stylesheet" href="../css/login_etudiant.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-wrapper">
        <div class="login-container password-reset-container">
            <div class="left-panel">
                <h1>Réinitialisation du mot de passe</h1>
                <p>Entrez votre numéro d'inscription et votre niveau pour recevoir un code de réinitialisation par email.</p>
            </div>
            <div class="right-panel">
                <div class="login-box">
                    <h2>Mot de passe oublié</h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="input-group">
                            <input type="text" name="matricule" required placeholder="Numéro d'inscription">
                        </div>
                        <div class="input-group">
                            <select name="niveau" id="niveau_reset" required class="placeholder-active">
                                <option value="" disabled selected>Sélectionnez votre niveau</option>
                                <option value="Licence I">Licence I</option>
                                <option value="Licence II">Licence II</option>
                                <option value="Licence III">Licence III</option>
                                <option value="Master I">Master I</option>
                                <option value="Master II">Master II</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">Envoyer le code</button>
                    </form>

                    <div style="text-align:center; margin-top:20px;">
                        <a href="login_etudiant.php" class="back-to-login">Retour à la connexion</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const niveauSelectReset = document.getElementById('niveau_reset'); // ID unique pour ce select
            function updateSelectPlaceholderReset() {
                if (niveauSelectReset.value === "") {
                    niveauSelectReset.classList.add('placeholder-active');
                } else {
                    niveauSelectReset.classList.remove('placeholder-active');
                }
            }
            niveauSelectReset.addEventListener('change', updateSelectPlaceholderReset);
            updateSelectPlaceholderReset(); // Appel initial
        });
    </script>
</body>
</html>