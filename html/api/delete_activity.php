<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['enseignants'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$activityId = $_GET['id'] ?? 0;

try {
    // Vérifier que l'activité appartient bien à l'enseignant
    $stmt = $pdo->prepare("DELETE FROM activites WHERE id = ? AND enseignant_id = ?");
    $stmt->execute([$activityId, $_SESSION['enseignants']['id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Activité non trouvée ou non autorisée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}