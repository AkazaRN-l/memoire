<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si tous les champs sont bien envoyés
    $champs_requis = ['numero_inscription', 'nom', 'prenom', 'email', 'telephone', 'mot_de_passe', 'confirmer_mot_de_passe', 'date_naissance', 'age', 'sexe', 'niveau'];

    foreach ($champs_requis as $champ) {
        if (!isset($_POST[$champ]) || trim($_POST[$champ]) == '') {
            die("❌ Erreur : Le champ " . htmlspecialchars($champ) . " est vide.");
        }
    }

    // Récupérer les données
    $numero_inscription = trim($_POST["numero_inscription"]);
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);
    $telephone = trim($_POST["telephone"]);
    $mot_de_passe = $_POST["mot_de_passe"];
    $confirm_mot_de_passe = $_POST["confirmer_mot_de_passe"];
    $date_naissance = $_POST["date_naissance"];
    $age = $_POST["age"];
    $sexe = $_POST["sexe"];
    $niveau = $_POST["niveau"];
    $statut = "en attente";

    // Vérifier si les mots de passe correspondent
    if ($mot_de_passe !== $confirm_mot_de_passe) {
        die("❌ Erreur : Les mots de passe ne correspondent pas.");
    }


    // Vérifier si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("❌ Erreur : L'email n'est pas valide.");
    }

    // Vérifier si le téléphone est valide (10 chiffres)
    if (!preg_match('/^[0-9]{10}$/', $telephone)) {
        die("❌ Erreur : Le numéro de téléphone doit contenir 10 chiffres.");
    }

    // Hasher le mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    // Gérer l'upload de la photo
    $photo = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $dossier = "uploads/";
        if (!is_dir($dossier)) mkdir($dossier, 0777, true);

        $nom_fichier = time() . "_" . basename($_FILES["photo"]["name"]);
        $chemin_fichier = $dossier . $nom_fichier;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $chemin_fichier)) {
            $photo = $chemin_fichier;
        } else {
            die("❌ Erreur : Problème lors du téléchargement de la photo.");
        }
    }

    // Insérer dans la base de données
    $sql = "INSERT INTO etudiants (numero_inscription, nom, prenom, email, mot_de_passe, date_naissance, age, sexe, niveau, photo, statut, telephone) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("❌ Erreur SQL : " . $conn->error);
    }

    $stmt->bind_param("ssssssssssss", $numero_inscription, $nom, $prenom, $email, $mot_de_passe_hash, $date_naissance, $age, $sexe, $niveau, $photo, $statut, $telephone);

    if ($stmt->execute()) {
        header("Location: succes.php");
        exit();
    } else {
        die("❌ Erreur lors de l'inscription : " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>