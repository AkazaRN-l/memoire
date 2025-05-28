<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST["numero"];
    $password = $_POST["pass"];
    $niveau = $_POST["niveau"];

    $sql = "SELECT * FROM etudiants WHERE numero_inscription = ? AND niveau = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $matricule, $niveau);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Vérifier si l'étudiant est validé par le chef de mention
        if ($user["statut"] !== "validé") {
            $error = "❌ Votre compte n'a pas encore été validé par le chef de mention.";
        } else {
            if (password_verify($password, $user["mot_de_passe"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["nom"] = $user["nom"];
                $_SESSION["prenom"] = $user["prenom"];
                $_SESSION["matricule"] = $user["numero_inscription"];
                $_SESSION["photo"] = $user["photo"];
                $_SESSION["niveau"] = $user["niveau"];
                $_SESSION["role"] = "etudiant";
                $_SESSION["matiere_id"] = $user["matiere_id"];
                header("Location: dashboard_etudiant.php");
                exit();
            } else {
                $error = "❌ Mot de passe incorrect.";
            }
        }
    } else {
        $error = "❌ Numéro d'inscription ou niveau invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Étudiant | Télécommunications</title>
    <link rel="stylesheet" href="../css/login_etudiant.css">
</head>
<body>
    <div class="container">
        <div class="image-section">
            <img src="../photo/OIP.JFIF" alt="Étudiant assis avec un ordinateur">
        </div>

        <div class="login-box">
            <h2>Connexion Étudiant</h2>
            <p>Accédez à votre espace personnel</p>

            <?php if (isset($error)) { ?>
                <p class="error-msg"><?= $error ?></p>
            <?php } ?>
            
            <form action="login_etudiant.php" method="POST">
                <div class="input-group">
                    <input id="numero" name="numero" type="text" required placeholder=" " value="<?= isset($matricule) ? htmlspecialchars($matricule) : '' ?>">
                    <label for="numero">Numéro d'inscription</label>
                </div>

                <div class="input-group password-group">
    <input id="pass" name="pass" type="password" required placeholder=" ">
    <label for="pass">Mot de passe</label>
</div>
<a href="motdepasse_oublie.php" class="forgot-password">Mot de passe oublié ?</a>

<div class="show-password-container">
    <input type="checkbox" id="showPassword">
    <span class="checkmark"></span>
    <label for="showPassword">Afficher le mot de passe</label>
</div>
                <div class="input-group">
                    <select id="niveau" name="niveau" required>
                        <option value="" disabled selected>Choisissez votre niveau</option>
                        <option value="Licence I">Licence I</option>
                        <option value="Licence II">Licence II</option>
                        <option value="Licence III">Licence III</option>
                        <option value="Master I">Master I</option>
                        <option value="Master II">Master II</option>
                    </select>
                </div>

                <button type="submit">Se connecter</button>
                
                <!-- Ligne "ou" -->
                <div class="or-divider"><span>ou</span></div>
                
                <button class="register-btn" onclick="window.location.href='register.html'">Créer un compte</button>
            </form>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('pass');
    const showPasswordCheckbox = document.getElementById('show-password');
    
    showPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });
});

document.getElementById('showPassword').addEventListener('change', function() {
    const passwordField = document.getElementById('pass');
    passwordField.type = this.checked ? 'text' : 'password';
});
</script>
</body>
</html>