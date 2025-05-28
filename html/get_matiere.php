<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['niveau'])) {
    die(json_encode([]));
}

$niveau = $_GET['niveau'];
$matieres = [];

try {
    $stmt = $pdo->prepare("SELECT id, nom FROM matieres WHERE niveau = ? ORDER BY nom");
    $stmt->execute([$niveau]);
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error
}

echo json_encode($matieres);