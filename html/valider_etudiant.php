<?php
include 'config.php';
session_start();

// Vérifier si l'utilisateur est bien le chef de mention
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

// Vérifier si l'ID de l'étudiant est bien envoyé
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ Erreur : ID étudiant manquant.");
}

$id = $_GET['id'];

// Mettre à jour le statut de l'étudiant
$sql = "UPDATE etudiants SET statut='validé' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboard_chef.php?niveau=Licence I"); // Redirection après validation
    exit();
} else {
    die("❌ Erreur : Impossible de valider l'étudiant.");
}

$stmt->close();
$conn->close();
?>