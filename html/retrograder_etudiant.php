<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

if (!isset($_GET['id']) || !isset($_GET['niveau'])) {
    die("Paramètres manquants.");
}

$id = intval($_GET['id']);
$niveau_actuel = $_GET['niveau'];

$niveaux = ['Licence I', 'Licence II', 'Licence III', 'Master I', 'Master II'];

$index = array_search($niveau_actuel, $niveaux);

if ($index === false) {
    die("❌ Niveau inconnu.");
}

if ($index === 0) {
    die("⚠️ L'étudiant est déjà au niveau le plus bas.");
}

$nouveau_niveau = $niveaux[$index - 1];

$sql = "UPDATE etudiants SET niveau = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nouveau_niveau, $id);

if ($stmt->execute()) {
    header("Location: dashboard_chef.php?niveau=" . urlencode($niveau_actuel));
    exit();
} else {
    echo "Erreur lors de la rétrogradation de l'étudiant.";
}
?>