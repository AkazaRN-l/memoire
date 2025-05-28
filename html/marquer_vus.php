<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Non autorisé");
}

$etudiant_id = $_SESSION['user_id'];
$matiere_id = intval($_GET['matiere_id']);

// Marquer tous les cours de cette matière comme vus
$stmt = $conn->prepare("INSERT INTO cours_vus (etudiant_id, cours_id, matiere_id)
                        SELECT ?, c.id, c.matiere_id 
                        FROM cours c
                        WHERE c.matiere_id = ?
                        AND NOT EXISTS (
                            SELECT 1 FROM cours_vus cv 
                            WHERE cv.etudiant_id = ? 
                            AND cv.cours_id = c.id
                        )");

$stmt->bind_param("iii", $etudiant_id, $matiere_id, $etudiant_id);
$stmt->execute();

echo json_encode(['success' => true]);
?>