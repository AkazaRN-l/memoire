<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_GET['niveau'])) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nom FROM matieres WHERE niveau = ? ORDER BY nom");
    $stmt->execute([$_GET['niveau']]);
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($matieres);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>