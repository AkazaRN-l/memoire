<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password_saisi = $_POST['mot_de_passe'];

    // Vérifier si l'utilisateur existe
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Vérifier le mot de passe
        if (password_verify($password_saisi, $row['mot_de_passe'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'chef_mention') {
                header("Location: dashboard_chef.php");
            } else {
                header("Location: etudiant_dashboard.php");
            }
            exit();
        } else {
            $error = "❌ Mot de passe incorrect.";
        }
    } else {
        $error = "❌ Utilisateur non trouvé.";
    }
}
?>

<!-- VOTRE HTML ORIGINAL -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | Télécom</title>
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="background-image"></div>

    <div class="login-container">
        <h2>🔑 Connexion Chef de Mention</h2>

        <?php if (isset($error)) { ?>
            <p class="error-msg"><?= $error ?></p>
        <?php } ?>

        <form action="login.php" method="POST">
            <label>Email :</label>
            <div class="input-icon">
                <span class="material-icons">mail</span>
                <input type="email" name="email" placeholder="Adresse e-mail" required>
            </div>

            <label>Mot de passe :</label>
            <div class="input-icon">
                <span class="material-icons">lock</span>
                <input type="password" name="mot_de_passe" id="password" placeholder="Mot de passe" required>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="show-password" onclick="togglePassword()">
                <label for="show-password">Afficher le mot de passe</label>
            </div>

            <button type="submit">Se connecter</button>
        </form>
    </div>

    <script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        passwordField.type = passwordField.type === "password" ? "text" : "password";
    }
    </script>
</body>
</html>