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

// Liste ordonnée des niveaux
$niveaux = ['Licence I', 'Licence II', 'Licence III', 'Master I', 'Master II'];

$index = array_search($niveau_actuel, $niveaux);

if ($index === false) {
    die("❌ Niveau inconnu.");
}

if ($index === count($niveaux) - 1) {
    die("✅ Étudiant déjà au niveau le plus élevé.");
}

$nouveau_niveau = $niveaux[$index + 1];

// Mise à jour du niveau de l'étudiant
$sql = "UPDATE etudiants SET niveau = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nouveau_niveau, $id);

if ($stmt->execute()) {
    header("Location: dashboard_chef.php?niveau=" . urlencode($niveau_actuel));
    exit();
} else {
    echo "Erreur lors de la promotion de l'étudiant.";
}
?>