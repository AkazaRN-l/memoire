<?php
session_start();
require 'config.php'; // Assure-toi que ce fichier connecte à ta BDD "telecommunication"

// Traitement du formulaire
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $matricule = trim($_POST['numero_matricule']);

    // Requête DIRECTE vers ta table enseignants existante
    $sql = "SELECT * FROM enseignants 
            WHERE email = ? AND numero_matricule = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $matricule);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Authentification réussie
        $_SESSION['enseignants'] = $result->fetch_assoc();
        header("Location: dashboard_enseignant.php");
        exit();
    } else {
        $error = "Email ou matricule incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Enseignants | Télécom</title>
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --glass: rgba(255, 255, 255, 0.1);
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: var(--glass);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        button {
            background: white;
            color: #2575fc;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="color: white; text-align: center;">Connexion Enseignant</h2>
        
        <?php if ($error): ?>
            <p style="color: #ff6b6b; text-align: center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email académique" required>
            </div>
            
            <div class="form-group">
                <input type="text" name="numero_matricule" placeholder="Numéro matricule" required>
            </div>
            
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>