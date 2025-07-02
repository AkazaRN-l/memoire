<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';

    // Générer un mot de passe aléatoire
    $motdepasse = bin2hex(random_bytes(4)); // 8 caractères hex
    $hashed = password_hash($motdepasse, PASSWORD_DEFAULT);

    // Supprimer les anciens chefs (s'il n'en faut qu'un)
    $pdo->prepare("DELETE FROM users WHERE role = 'chef_mention'")->execute();

    // Ajouter le nouveau chef
    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'chef_mention')");
    $stmt->execute([$nom, $prenom, $email, $hashed]);

    // Envoyer un email avec PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rahaja.ranjanirina@gmail.com'; 
        $mail->Password   = 'lmgf mzzx nyut xseq';    
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('rahaja.ranjanirina@gmail.com', 'Administrateur UNIV');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Nomination Chef de Mention";
        $mail->Body    = "<h3>Félicitations $prenom $nom !</h3>
                         <p>Vous avez été nommé Chef de Mention.<br>
                         Voici vos identifiants :</p>
                         <ul>
                            <li><strong>Email :</strong> $email</li>
                            <li><strong>Mot de passe :</strong> $motdepasse</li>
                         </ul>
                         <p>Connectez-vous ici : <a href='http://localhost/telecommunication/html/login_chef.php'>Tableau de bord Chef</a></p>";

        $mail->send();
        header("Location: dashboard_admin.php?success=1");
        exit;
    } catch (Exception $e) {
        echo "Erreur d'envoi de mail : {$mail->ErrorInfo}";
    }
}