<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['enseignants'])) {
    header('Location: login_enseignant.php');
    exit();
}

// Vérifier si l'ID du cours est présent dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de cours invalide";
    header('Location: dashboard_enseignant.php');
    exit();
}

$cours_id = intval($_GET['id']);
$enseignant_id = $_SESSION['enseignants']['id'];

require_once __DIR__ . '/db.php';

try {
    // Vérifier que le cours appartient bien à cet enseignant avant suppression
    $stmt = $pdo->prepare("SELECT fichier_nom FROM cours WHERE id = ? AND enseignant_id = ?");
    $stmt->execute([$cours_id, $enseignant_id]);
    $cours = $stmt->fetch();

    if (!$cours) {
        $_SESSION['error'] = "Cours non trouvé ou vous n'avez pas les droits pour le supprimer";
        header('Location: dashboard_enseignant.php');
        exit();
    }

    // Supprimer le fichier physique si il existe
    if (!empty($cours['fichier_nom'])) {
        $file_path = __DIR__ . '/uploads/' . $cours['fichier_nom'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Supprimer le cours de la base de données
    $stmt = $pdo->prepare("DELETE FROM cours WHERE id = ? AND enseignant_id = ?");
    $stmt->execute([$cours_id, $enseignant_id]);

    // Vérifier si la suppression a réussi
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Le cours a été supprimé avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du cours";
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
}

// Rediriger vers le dashboard
header('Location: dashboard_enseignant.php');
exit();
?>