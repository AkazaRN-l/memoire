<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['enseignants'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$type = $_POST['type'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($type) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO activites (enseignant_id, type, description, date_creation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        $_SESSION['enseignants']['id'],
        $type,
        $description
    ]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}