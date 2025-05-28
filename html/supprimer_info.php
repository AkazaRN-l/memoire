<?php
session_start();
include 'config.php';

// Vérification des permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $niveau = isset($_GET['niveau']) ? $_GET['niveau'] : 'all';
    
    $sql = "DELETE FROM informations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Information supprimée avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression";
    }
    
    header("Location: voir_information.php?niveau=" . urlencode($niveau));
    exit();
}
?>