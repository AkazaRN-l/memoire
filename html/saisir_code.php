<?php
session_start();
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: motdepasse_oublie.php");
    exit();
}

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_entre = $_POST['code'];
    
    // Vérifier le code
    $sql = "SELECT reset_code, reset_expires > NOW() as valide FROM etudiants WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['reset_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if ($data['valide'] && password_verify($code_entre, $data['reset_code'])) {
        header("Location: nouveau_motdepasse.php");
        exit();
    } else {
        $error = "Code invalide ou expiré";
    }
}
?>

<form method="POST">
    <h3>Entrez le code reçu par SMS</h3>
    <input type="text" name="code" required>
    <button type="submit">Valider</button>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</form>