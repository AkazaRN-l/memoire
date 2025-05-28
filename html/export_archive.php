<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

$year_id = $_GET['annee_id'] ?? die("Année non spécifiée");
$export_type = $_GET['type'] ?? die("Type non spécifié");

$year = $conn->query("SELECT * FROM annee_academique WHERE id = $year_id")->fetch_assoc();
if (!$year) die("Année invalide");

$filename = "archive_{$year['annee']}_$export_type_" . date('Ymd') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');


$output = fopen('php://output', 'w');

switch ($export_type) {
    case 'cours':
       
        fputcsv($output, ['ID', 'Titre', 'Matière', 'Enseignant', 'Date', 'Fichier']);
        
        
        $query = $conn->query("
            SELECT c.id, c.titre, m.nom as matiere, 
                   CONCAT(e.nom, ' ', e.prenom) as enseignant,
                   c.date_envoi, c.fichier_nom
            FROM archives_cours a
            JOIN cours c ON a.cours_id = c.id
            JOIN matieres m ON c.matiere_id = m.id
            JOIN enseignants e ON c.enseignant_id = e.id
            WHERE a.annee_id = $year_id
            ORDER BY c.date_envoi DESC
        ");
        
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['titre'],
                $row['matiere'],
                $row['enseignant'],
                $row['date_envoi'],
                $row['fichier_nom']
            ]);
        }
        break;
        
    case 'informations':
        fputcsv($output, ['ID', 'Titre', 'Contenu', 'Date']);
        
        $query = $conn->query("
            SELECT i.id, i.titre, i.contenu, i.date_envoi
            FROM archives_informations a
            JOIN informations i ON a.info_id = i.id
            WHERE a.annee_id = $year_id
            ORDER BY i.date_envoi DESC
        ");
        
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['titre'],
                strip_tags($row['contenu']),
                $row['date_envoi']
            ]);
        }
        break;
        
    default:
        die("Type d'export non supporté");
}

fclose($output);
exit();
?>