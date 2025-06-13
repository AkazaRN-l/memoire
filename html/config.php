<?php

$serveur = "127.0.0.1";
$utilisateur = "root"; 
$motdepasse = "R02102002";     
$basededonnees = "telecommunication_db";


$conn = new mysqli($serveur, $utilisateur, $motdepasse, $basededonnees);


if ($conn->connect_error) {
  
    die("<div style='padding:20px;background:#ffebee;border:2px solid red;'>
        <h3>Erreur de Connexion à la Base de Données</h3>
        <p><strong>Code erreur :</strong> ".$conn->connect_errno."</p>
        <p><strong>Message :</strong> ".$conn->connect_error."</p>
        <p>Vérifiez :</p>
        <ul>
            <li>Que MySQL est bien démarré</li>
            <li>Les identifiants dans config.php</li>
            <li>Que la base 'telecommunication_db' existe</li>
        </ul>
        </div>");
}


$conn->set_charset("utf8mb4");


?>

