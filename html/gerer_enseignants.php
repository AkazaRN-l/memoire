<?php
include 'config.php';
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


// Vérifier si l'utilisateur est bien le chef de mention
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

// Ajouter un enseignant
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST["matricule"];
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $specialite = $_POST["specialite"];

    // Générer un mot de passe aléatoire
    $motdepasse = bin2hex(random_bytes(4)); 
    $motdepasse_hash = password_hash($motdepasse, PASSWORD_DEFAULT); 

    $sql = "INSERT INTO enseignants (numero_matricule, nom, prenom, email, specialite, motdepasse) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $matricule, $nom, $prenom, $email, $specialite, $motdepasse_hash);

    if ($stmt->execute()) {
        // Envoi de l'email avec PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rahaja.ranjanirina@gmail.com';
            $mail->Password = 'lmgf mzzx nyut xseq';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('rahaja.ranjanirina@gmail.com', 'Université de Vakinankaratra');
            $mail->addAddress($email, $prenom . ' ' . $nom);
            
            $mail->isHTML(true);
            $mail->Subject = 'Vos identifiants pour la plateforme pédagogique';
            $mail->Body = "
                <h2>Bonjour $prenom $nom,</h2>
                <p>Vous avez été enregistré comme enseignant sur la plateforme pédagogique de la mention télécommunication</p>
                <p><strong>Voici vos identifiants :</strong></p>
                <ul>
                    <li>Email: $email</li>
                    <li>Mot de passe temporaire: $motdepasse</li>
                    <li>Numéro matricule: $matricule</li>
                </ul>
                <p>Nous vous recommandons de changer votre mot de passe après votre première connexion.</p>
                <p>Cordialement,<br>Chef de mention</p>
            ";

            $mail->send();
            $_SESSION['success'] = "✅ Enseignant ajouté avec succès et email envoyé à $email";
        } catch (Exception $e) {
            $_SESSION['error'] = "✅ Enseignant ajouté mais l'email n'a pas pu être envoyé. Erreur : " . $mail->ErrorInfo;
        }

        header("Location: gerer_enseignants.php");
        exit();
    } else {
        $_SESSION['error'] = "❌ Erreur lors de l'ajout de l'enseignant";
        header("Location: gerer_enseignants.php");
        exit();
    }

    $stmt->close();
}

// Récupérer les enseignants
$sql_enseignants = "SELECT * FROM enseignants";
$result_enseignants = $conn->query($sql_enseignants);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Enseignants</title>
    <link rel="stylesheet" href="../css/gerer_enseignant.css">
</head>
<body>
    <div class="main-container">
        <!-- Carte Ajout Enseignant -->
        <div class="premium-card">
            <div class="section-header">
                <h2>👨‍🏫 Ajouter un Enseignant</h2>
            </div>

            <?php if (isset($_SESSION['success'])) : ?>
                <div class="success-message">
                    <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])) : ?>
                <div class="error-message">
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form class="teacher-form" action="gerer_enseignants.php" method="POST">
                <div class="form-group">
                    <label>Numéro Matricule :</label>
                    <input type="text" name="matricule" class="form-control" required>
                    <label>Nom :</label>
                    <input type="text" name="nom" class="form-control" required>
                    <label>Prénom :</label>
                    <input type="text" name="prenom" class="form-control" required>
                    <label>Email :</label>
                    <input type="email" name="email" class="form-control" required>
                    <label>Spécialité :</label>
                    <input type="text" name="specialite" class="form-control" required>
                </div>
                <br> <br> <br>
                <button type="submit" class="btn-premium">➕ Ajouter</button>
            </form>
        </div>

        <!-- Carte Liste Enseignants -->
        <div class="premium-card">
            <div class="section-header">
                <h2>📜 Liste des Enseignants</h2>
            </div>

            <div class="table-container">
                <table class="teacher-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Numéro Matricule</th>
                            <th>Spécialité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_enseignants->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nom']) ?></td>
                            <td><?= htmlspecialchars($row['prenom']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['numero_matricule']) ?></td>
                            <td><?= htmlspecialchars($row['specialite']) ?></td>
                            <td>
                              <a href="supprimer_enseignant.php?id=<?= $row['id'] ?>" class="btn-action btn-delete">
                                 <span class="btn-icon">🗑️</span>
                                 <span class="btn-text">Supprimer</span>
                               </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="dashboard_chef.php" class="back-btn">🔙 Retour au tableau de bord</a>
    </div>

    <script src="../js/gerer_enseignant.js"></script>
</body>
</html>