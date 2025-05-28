<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['niveau'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$niveau = $_SESSION['niveau'];
$user_nom = $_SESSION['nom'];
$user_prenom = $_SESSION['prenom'];
$matiere_id = isset($_GET['matiere_id']) ? intval($_GET['matiere_id']) : 0;

if ($matiere_id === 0) {
    echo "Matière non spécifiée.";
    exit();
}

// Nom de la matière
$matiere_nom = "Inconnue";
$stmt = $conn->prepare("SELECT nom FROM matieres WHERE id = ?");
$stmt->bind_param("i", $matiere_id);
$stmt->execute();
$stmt->bind_result($matiere_nom);
$stmt->fetch();
$stmt->close();

// Envoi de message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $type_auteur = $role;
    $etudiant_id = $role === 'etudiant' ? $user_id : null;
    $enseignant_id = $role === 'enseignant' ? $user_id : null;

    $fichier = "";
    if (!empty($_FILES['fichier']['name'])) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = basename($_FILES['fichier']['name']);
        $targetFile = $uploadDir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $targetFile)) {
            $fichier = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO forum_messages (niveau, type_auteur, enseignant_id, etudiant_id, matiere_id, message, fichier, date_message)
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssiiiss", $niveau, $type_auteur, $enseignant_id, $etudiant_id, $matiere_id, $message, $fichier);
    $stmt->execute();
    header("Location: forum.php?matiere_id=" . urlencode($matiere_id));
    exit();
}

// Récupération des messages
$query = "
    SELECT fm.*, 
        CASE 
            WHEN fm.type_auteur = 'etudiant' THEN e.nom 
            WHEN fm.type_auteur = 'enseignant' THEN ens.nom 
        END AS nom,
        CASE 
            WHEN fm.type_auteur = 'etudiant' THEN e.prenom 
            WHEN fm.type_auteur = 'enseignant' THEN ens.prenom 
        END AS prenom
    FROM forum_messages fm
    LEFT JOIN etudiants e ON fm.etudiant_id = e.id
    LEFT JOIN enseignants ens ON fm.enseignant_id = ens.id
    WHERE fm.matiere_id = ?
    ORDER BY fm.date_message DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $matiere_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum - <?= htmlspecialchars($matiere_nom) ?></title>
</head>
<body>
    <h1>Forum : <?= htmlspecialchars($matiere_nom) ?></h1>
    <p>Connecté : <?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?> (<?= htmlspecialchars($role) ?>)</p>
    <a href="<?= $role === 'etudiant' ? 'dashboard_etudiant.php' : 'dashboard_prof.php' ?>">Retour au tableau de bord</a>

    <form method="POST" enctype="multipart/form-data">
        <textarea name="message" placeholder="Votre message" required></textarea><br>
        <input type="file" name="fichier"><br>
        <button type="submit">Envoyer</button>
    </form>

    <h2>Messages</h2>
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div style="border: 1px solid #ccc; margin: 10px; padding: 10px;">
                <strong><?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?> (<?= $msg['type_auteur'] ?>)</strong><br>
                <small><?= $msg['date_message'] ?></small><br>
                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <?php if (!empty($msg['fichier'])): ?>

                    <?php
        $ext = pathinfo($msg['fichier'], PATHINFO_EXTENSION);
        $fichier_path = "../uploads/" . htmlspecialchars($msg['fichier']);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            echo '<a href="' . $fichier_path . '" target="_blank" style="color: blue; text-decoration: underline;">Voir le fichier</a>';
        } else {
            echo '<p><em>Fichier non supporté pour l\'affichage</em></p>';
        }
    ?>

                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun message pour cette matière.</p>
    <?php endif; ?>
</body>
</html>