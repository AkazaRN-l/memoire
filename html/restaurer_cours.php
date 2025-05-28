<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

$course_id = $_GET['id'] ?? die("Cours non spécifié");
$year_id = $_GET['annee_id'] ?? die("Année non spécifiée");

// Vérifier que le cours existe dans les archives
$archive = $conn->query("
    SELECT * FROM archives_cours 
    WHERE cours_id = $course_id AND annee_id = $year_id
")->fetch_assoc();

if (!$archive) die("Ce cours n'est pas dans les archives de cette année");

// Récupérer les détails du cours
$course = $conn->query("SELECT * FROM cours WHERE id = $course_id")->fetch_assoc();

// Vérifier que le cours n'existe pas déjà dans la table courante
$existing = $conn->query("SELECT id FROM cours WHERE id = $course_id")->fetch_assoc();
if ($existing) die("Ce cours existe déjà dans la base active");

// Restaurer le cours
try {
    $conn->begin_transaction();
    
    // Supprimer de l'archive
    $conn->query("DELETE FROM archives_cours WHERE id = {$archive['id']}");
    
    // Insérer dans la table courante
    $stmt = $conn->prepare("
        INSERT INTO cours (id, titre, description, fichier, fichier_nom, matiere_id, enseignant_id, niveau, date_envoi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "issssiiss",
        $course['id'],
        $course['titre'],
        $course['description'],
        $course['fichier'],
        $course['fichier_nom'],
        $course['matiere_id'],
        $course['enseignant_id'],
        $course['niveau'],
        $course['date_envoi']
    );
    
    $stmt->execute();
    $conn->commit();
    
    $_SESSION['success'] = "Le cours a été restauré avec succès !";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Erreur lors de la restauration : " . $e->getMessage();
}

header("Location: voir_archives.php?annee_id=$year_id");
exit();
?>