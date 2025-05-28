<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['enseignants'])) {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM activites WHERE enseignant_id = ? ORDER BY date_creation DESC");
    $stmt->execute([$_SESSION['enseignants']['id']]);
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les dates
    foreach ($activites as &$activite) {
        $activite['date_formatted'] = date('d/m/Y - H:i', strtotime($activite['date_creation']));
    }
    
    echo json_encode($activites);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}