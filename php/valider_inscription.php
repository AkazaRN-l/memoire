<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'télécommunication_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer l'ID et l'action (valider ou refuser)
$id = $_GET['id'];
$action = $_GET['action'];

// Mettre à jour le statut de l'utilisateur
if ($action === 'valider') {
    $statut = 'valide';
} elseif ($action === 'refuser') {
    $statut = 'refuse';
}

// Requête SQL pour mettre à jour le statut
$sql = "UPDATE utilisateurs SET statut = '$statut' WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    // Rediriger vers l'interface admin après la mise à jour
    header("Location: admin_validation.php");
    exit();
} else {
    echo "Erreur: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>