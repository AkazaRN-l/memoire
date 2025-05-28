<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifier si l'utilisateur existe
    $sql = "SELECT id, role, statut, mot_de_passe FROM etudiants WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $role, $statut, $mot_de_passe_hash);
    $stmt->fetch();
    $stmt->close();

    if ($id) {
        if (!password_verify($mot_de_passe, $mot_de_passe_hash)) {
            die("Mot de passe incorrect.");
        }

        // Stocker les infos dans la session
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['statut'] = $statut;

        // Rediriger selon le rôle
        if ($role == 'chef_mention') {
            header("Location: dashboard_chef.php");
        } elseif ($role == 'etudiant' && $statut == 'validé') {
            header("Location: cours.php");
        } else {
            die("Votre compte n'est pas encore validé.");
        }
        exit();
    } else {
        die("Email introuvable.");
    }
}
?>