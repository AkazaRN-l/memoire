<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chef_id = $_POST['chef_id'] ?? null;
    $email = $_POST['chef_email'] ?? '';
    $nom = $_POST['chef_nom'] ?? '';
    $prenom = $_POST['chef_prenom'] ?? '';

    if ($chef_id) {
        // Supprimer le chef de la base
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'chef_mention'");
        $stmt->execute([$chef_id]);

        // Envoyer l'e-mail de notification
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rahaja.ranjanirina@gmail.com';  // Remplace par ton email
            $mail->Password   = 'lmgf mzzx nyut xseq';      // Mot de passe d'application
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('rahaja.ranjanirina@gmail.com', 'Administrateur UNIV');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Fin de mission en tant que Responsable de Mention";
            $mail->Body    = "<p>Bonjour $prenom $nom,</p>
                             <p>Nous vous informons que vous avez été retiré du rôle de responsable de mention.</p>
                             <p>Merci pour votre service. Pour toute question, contactez l'administration.</p>";

            $mail->send();
            header("Location: dashboard_admin.php?deleted=1");
            exit;

        } catch (Exception $e) {
            echo "Erreur d'envoi de mail : {$mail->ErrorInfo}";
        }
    } else {
        echo "ID du chef introuvable.";
    }
} else {
    header("Location: dashboard_admin.php");
    exit;
}