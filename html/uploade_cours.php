<?php
// 1. Initialisation
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

// 2. Vérifications
if (!isset($_SESSION['enseignants'])) {
    die("Accès non autorisé");
}

if (!isset($_POST['titre'], $_POST['description'], $_POST['niveau'], $_POST['matiere_id'], $_FILES['fichier'])) {
    die("Données manquantes");
}

// 3. Vérification de la matière
$matiere_id = (int)$_POST['matiere_id'];
$stmt = $pdo->prepare("SELECT id FROM matieres WHERE id = ?");
$stmt->execute([$matiere_id]);
if (!$stmt->fetch()) {
    die("Matière invalide");
}

// 4. Traitement du fichier
$dossierUpload = realpath(__DIR__ . '/../uploads/cours/');
if (!$dossierUpload || !is_dir($dossierUpload)) {
    die("Dossier d'upload inaccessible");
}

$extension = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
$nomFichier = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $_FILES['fichier']['name']);
$cheminComplet = $dossierUpload . DIRECTORY_SEPARATOR . $nomFichier;

// Liste des extensions autorisées
$extensionsAutorisees = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'mp4'];
if (!in_array($extension, $extensionsAutorisees)) {
    die("Type de fichier non autorisé");
}

// Upload du fichier
if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $cheminComplet)) {
    die("Erreur lors de l'upload du fichier. Chemin utilisé : " . $cheminComplet);
}

// 5. Enregistrement en base avec annee_id
try {
    // Récupérer l'année académique active
    $stmt = $pdo->prepare("SELECT id FROM annee_academique WHERE est_active = 1 LIMIT 1");
    $stmt->execute();
    $annee = $stmt->fetch();
    if (!$annee) {
        throw new Exception("Aucune année académique active trouvée.");
    }
    $annee_id = $annee['id'];

    // Insertion dans la table cours
    $stmt = $pdo->prepare("INSERT INTO cours 
        (enseignant_id, titre, description, fichier_nom, chemin_fichier, niveau, matiere_id, annee_id, date_envoi) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $_SESSION['enseignants']['id'],
        htmlspecialchars($_POST['titre']),
        htmlspecialchars($_POST['description']),
        $nomFichier,
        $cheminComplet,
        $_POST['niveau'],
        $matiere_id,
        $annee_id
    ]);

    header("Location: dashboard_enseignant.php?success=1");
    exit;

} catch (Exception $e) {
    // Supprimer le fichier uploadé en cas d'erreur
    if (file_exists($cheminComplet)) {
        unlink($cheminComplet);
    }
    die("Erreur : " . $e->getMessage());
}