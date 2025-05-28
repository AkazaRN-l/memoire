<?php
session_start();
include 'config.php';

if (!isset($_SESSION['valid_reset']) || !isset($_SESSION['reset_id'])) {
    header("Location: motdepasse_oublie.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];
    $id = $_SESSION['reset_id'];

    if (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE etudiants SET mot_de_passe = ?, reset_code = NULL, reset_expires = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed, $id);
        $stmt->execute();

        unset($_SESSION['reset_id'], $_SESSION['valid_reset']);
        $_SESSION['success'] = "Mot de passe réinitialisé avec succès.";
        header("Location: login_etudiant.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe</title>
    <style>
        body {
            background-color: #f4f7f9;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Nouveau mot de passe</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <input type="password" name="password" placeholder="Nouveau mot de passe" required>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
        </div>
        <button type="submit">Réinitialiser</button>
    </form>
</div>

</body>
</html>
