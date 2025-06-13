<?php
session_start();
include 'config.php'; // Assurez-vous que ce fichier contient les informations de connexion à votre BDD

$error = ''; // Initialisation de la variable d'erreur
$matricule = ''; // Initialiser pour éviter les notices si le formulaire n'est pas encore soumis

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST["numero"] ?? '';
    $password = $_POST["pass"] ?? '';
    $niveau = $_POST["niveau"] ?? '';

    // Validation basique des entrées
    if (empty($matricule) || empty($password) || empty($niveau)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Préparation de la requête SQL pour éviter les injections
        $sql = "SELECT * FROM etudiants WHERE numero_inscription = ? AND niveau = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $error = "Erreur de préparation de la requête : " . $conn->error;
        } else {
            $stmt->bind_param("ss", $matricule, $niveau);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Vérifier si l'étudiant est validé par le chef de mention
                if ($user["statut"] !== "validé") {
                    $error = "❌ Votre compte n'a pas encore été validé par le chef de mention.";
                } else {
                    // Vérifier le mot de passe haché
                    if (password_verify($password, $user["mot_de_passe"])) {
                        // Stockage des informations utilisateur en session
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
            $stmt->close();
        }
    }
}

// Fermeture de la connexion à la base de données (si non persistante)
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Étudiant | Télécommunications</title>
    <link rel="stylesheet" href="../css/login_etudiant.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-wrapper">
        <div class="login-container">
            <div class="left-panel">
                <h1>Espace Étudiant</h1>
                <p>Connectez-vous pour accéder à votre plateforme des Télécommunications.</p>
            </div>
            <div class="right-panel">
                <div class="login-box">
                    <h2>Connexion Étudiant</h2>
                    
                    <?php if (!empty($error)) { ?>
                        <p class="error-msg"><?= htmlspecialchars($error) ?></p>
                    <?php } ?>
                    
                    <form action="login_etudiant.php" method="POST">
                        <div class="input-group">
                            <input id="numero" name="numero" type="text" required placeholder="Numéro d'inscription" value="<?= htmlspecialchars($matricule) ?>">
                        </div>

                        <div class="input-group password-group">
                            <input id="pass" name="pass" type="password" required placeholder="Mot de passe">
                        </div>
                        
                        <div class="show-password-container">
                            <input type="checkbox" id="showPassword">
                            <span class="checkmark"></span>
                            <label for="showPassword">Afficher le mot de passe</label>
                        </div>

                        <div class="input-group">
                            <select id="niveau" name="niveau" required class="placeholder-active">
                                <option value="" disabled selected>Sélectionnez votre niveau</option>
                                <option value="Licence I">Licence I</option>
                                <option value="Licence II">Licence II</option>
                                <option value="Licence III">Licence III</option>
                                <option value="Master I">Master I</option>
                                <option value="Master II">Master II</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary">Se connecter</button>
                        
                        <a href="motdepasse_oublie.php" class="forgot-password">Mot de passe oublié ?</a>

                        <div class="or-divider"></div>
                        
                        <button type="button" class="btn-secondary" onclick="window.location.href='register.html'">Créer un compte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('pass');
            const showPasswordCheckbox = document.getElementById('showPassword');
            
            showPasswordCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    passwordInput.type = 'text';
                } else {
                    passwordInput.type = 'password';
                }
            });

            const niveauSelect = document.getElementById('niveau');
            // Gérer la classe 'placeholder-active' pour le select
            function updateSelectPlaceholder() {
                if (niveauSelect.value === "") {
                    niveauSelect.classList.add('placeholder-active');
                } else {
                    niveauSelect.classList.remove('placeholder-active');
                }
            }
            niveauSelect.addEventListener('change', updateSelectPlaceholder);
            // Appel initial au chargement de la page
            updateSelectPlaceholder();
        });
    </script>
</body>
</html>