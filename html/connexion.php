<?php
$host = 'localhost';  
$db   = 'telecommunication_db';  
$user = 'root';  
$pass = 'R02102002';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Activer le mode d'erreur PDO
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>