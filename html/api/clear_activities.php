<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['enseignants'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM activites WHERE enseignant_id = ?");
    $stmt->execute([$_SESSION['enseignants']['id']]);
    
    echo json_encode(['success' => true, 'count' => $stmt->rowCount()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}