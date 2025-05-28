<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['enseignant_id'])) {
    header("Location: login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];
$niveau = $_GET['niveau'] ?? '';
$matiere_id = $_GET['matiere_id'] ?? '';
$message = '';
$messages = [];
$success = '';
$error = '';

$niveaux = ["licence I", "licence II", "licence III", "master I", "master II"];

// Récupérer les matières
$matieres = [];
if (!empty($niveau)) {
    $stmt = $conn->prepare("SELECT id, nom FROM matieres WHERE niveau = ?");
    $stmt->bind_param("s", $niveau);
    $stmt->execute();
    $matieres = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Traitement du message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $niveau_post = $_POST['niveau'] ?? '';
    $matiere_id_post = $_POST['matiere_id'] ?? '';
    $fichier_nom = null;

    // Upload du fichier
    if (!empty($_FILES['fichier']['name'])) {
        $fichier_nom = basename($_FILES['fichier']['name']);
        move_uploaded_file($_FILES['fichier']['tmp_name'], "../uploads/" . $fichier_nom);
    }

    if ($niveau_post && $matiere_id_post && $message) {
        $stmt = $conn->prepare("INSERT INTO forum_messages (enseignant_id, niveau, matiere_id, message, fichier, type_auteur)
                                VALUES (?, ?, ?, ?, ?, 'enseignant')");
        $stmt->bind_param("isiss", $enseignant_id, $niveau_post, $matiere_id_post, $message, $fichier_nom);
        if ($stmt->execute()) {
            header("Location: forum_enseignant.php?niveau=" . urlencode($niveau_post) . "&matiere_id=" . urlencode($matiere_id_post));
            exit();
        } else {
            $error = "Erreur lors de l'envoi.";
        }
        $stmt->close();
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

// Récupération des messages
if ($niveau && $matiere_id) {
    $stmt = $conn->prepare("SELECT fm.*, m.nom AS matiere_nom FROM forum_messages fm
                            JOIN matieres m ON fm.matiere_id = m.id
                            WHERE fm.niveau = ? AND fm.matiere_id = ?
                            ORDER BY fm.date_message DESC");
    $stmt->bind_param("si", $niveau, $matiere_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum Enseignant</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        form, .forum { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .message { margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .success { color: green; }
        .error { color: red; }
        iframe, img { max-width: 100%; height: auto; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Forum - Enseignant</h2>

    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Niveau:</label>
        <select name="niveau" onchange="window.location.href='forum_enseignant.php?niveau=' + encodeURIComponent(this.value)">
            <option value="">-- Choisir --</option>
            <?php foreach ($niveaux as $niv): ?>
                <option value="<?= $niv ?>" <?= ($niveau === $niv) ? 'selected' : '' ?>><?= $niv ?></option>
            <?php endforeach; ?>
        </select>

        <?php if (!empty($matieres)): ?>
            <label>Matière:</label>
            <select name="matiere_id" onchange="window.location.href='forum_enseignant.php?niveau=<?= urlencode($niveau) ?>&matiere_id=' + this.value">
                <option value="">-- Choisir --</option>
                <?php foreach ($matieres as $matiere): ?>
                    <option value="<?= $matiere['id'] ?>" <?= ($matiere_id == $matiere['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($matiere['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($niveau && $matiere_id): ?>
            <br><br>
            <label>Message:</label><br>
            <textarea name="message" rows="4" cols="50" required></textarea><br><br>

            <label>Fichier (optionnel):</label>
            <input type="file" name="fichier"><br><br>

            <button type="submit">Envoyer</button>
        <?php endif; ?>
    </form>

    <?php if ($niveau && $matiere_id): ?>
        <div class="forum">
            <h3>Messages - <?= htmlspecialchars($niveau) ?> / 
                <?= isset($messages[0]['matiere_nom']) ? htmlspecialchars($messages[0]['matiere_nom']) : '' ?></h3>

            <?php if ($messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message">
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

                        <small><br>Posté le <?= $msg['date_message'] ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun message pour ce niveau et cette matière.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>