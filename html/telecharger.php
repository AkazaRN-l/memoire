<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION["user_id"])) {
    die("Accès non autorisé");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de cours invalide");
}

$stmt = $pdo->prepare("SELECT fichier_nom, chemin_fichier FROM cours WHERE id = ?");
$stmt->execute([intval($_GET['id'])]);
$cours = $stmt->fetch();

if (!$cours) {
    die("Fichier non trouvé dans la base");
}

// SOLUTION CLEF ICI :
// Si chemin_fichier contient déjà le chemin complet, utilisez-le directement
$filepath = $cours['chemin_fichier'];

// Nettoyage du chemin (au cas où)
$filepath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filepath);
$filepath = realpath($filepath);

if (!$filepath || !file_exists($filepath)) {
    die("Erreur : Le fichier '" . htmlspecialchars($cours['chemin_fichier']) . "' 
        n'existe pas. Chemin résolu : " . htmlspecialchars($filepath) . "
        Vérifiez que le fichier est bien présent à cet emplacement.");
}

// Envoi du fichier
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($cours['fichier_nom']) . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>