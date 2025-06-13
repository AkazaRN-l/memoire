<?php
session_start();
require 'config.php';

if (!isset($_SESSION['reset_id'])) {
    header("Location: motdepasse_oublie.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["code"]);
    $id = $_SESSION['reset_id'];

    $sql = "SELECT id FROM etudiants WHERE id = ? AND reset_code = ? AND reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $_SESSION['valid_reset'] = true;
        header("Location: nouveau_motdepasse.php");
        exit();
    } else {
        $error = "Code invalide ou expiré.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification du code</title>
    <link rel="stylesheet" href="../css/verifier_code.css">
</head>
<body>
<div class="box">
    <h2>Vérification du code</h2>

    <?php if (!empty($error)) : ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="input-group">
            <input type="text" name="code" placeholder="Entrez le code reçu par email" required>
        </div>
        <button class="bouton" type="submit">Vérifier</button>
    </form>
</div>
</body>
</html>
