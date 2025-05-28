<?php
// Vérifie si un fichier est spécifié dans l'URL
if (isset($_GET['fichier']) && !empty($_GET['fichier'])) {
    $fichier = basename($_GET['fichier']); 
    $chemin = '../uploads/' . $fichier; // Chemin complet vers le fichier

    // Vérifie si le fichier existe sur le serveur
    if (file_exists($chemin)) {
        // Envoie les bons en-têtes pour forcer le téléchargement
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fichier . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($chemin));
        flush(); // Vide le tampon de sortie
        readfile($chemin); // Lit le fichier
        exit;
    } else {
        // Fichier introuvable
        echo "Fichier non disponible sur le serveur.";
    }
} else {
    // Aucun fichier spécifié dans l'URL
    echo "Aucun fichier spécifié.";
}
?>