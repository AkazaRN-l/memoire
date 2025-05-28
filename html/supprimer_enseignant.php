<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM enseignants WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: gerer_enseignants.php?deleted=1");
        exit();
    } else {
        die("❌ Erreur lors de la suppression.");
    }

    $stmt->close();
    $conn->close();
}
?>