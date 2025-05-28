<?php
$host = '127.0.0.1';
$dbname = 'telecommunication_db';
$username = 'root';
$password = 'R02102002';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Log l'erreur dans un fichier en production
    error_log("Erreur DB: " . $e->getMessage());
    
    // Message générique pour l'utilisateur
    die("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
}