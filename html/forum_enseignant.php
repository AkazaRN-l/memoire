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

$matieres = [];
if (!empty($niveau)) {
    $stmt = $conn->prepare("SELECT id, nom FROM matieres WHERE niveau = ?");
    $stmt->bind_param("s", $niveau);
    $stmt->execute();
    $matieres = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $niveau_post = $_POST['niveau'] ?? '';
    $matiere_id_post = $_POST['matiere_id'] ?? '';
    $fichier_nom = null;

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
    <link rel="stylesheet" href="../css/forum_enseignant.css">
    <script>
        function changerNiveau(select) {
            const niveau = select.value;
            if (niveau) {
                window.location.href = 'forum_enseignant.php?niveau=' + encodeURIComponent(niveau);
            }
        }

        function changerMatiere(select) {
            const matiere = select.value;
            const params = new URLSearchParams(window.location.search);
            params.set('matiere_id', matiere);
            window.location.search = params.toString();
        }

        function afficherNomFichier(input) {
            const nomFichier = input.files[0]?.name ?? "";
            document.getElementById('nom-fichier').textContent = nomFichier;
        }
    </script>
   
</head>
<body>
    <div class="container">
        <h2>Forum - Espace Enseignant</h2>

        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Niveau :</label>
            <select name="niveau" onchange="changerNiveau(this)">
                <option value="">-- Choisir --</option>
                <?php foreach ($niveaux as $niv): ?>
                    <option value="<?= $niv ?>" <?= ($niveau === $niv) ? 'selected' : '' ?>><?= $niv ?></option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($matieres)): ?>
                <label>Matière :</label>
                <select name="matiere_id" onchange="changerMatiere(this)">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?= $matiere['id'] ?>" <?= ($matiere_id == $matiere['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($matiere['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php if ($niveau && $matiere_id): ?>
                <label>Message :</label>
                <textarea name="message" rows="4" required></textarea>

                <label>Fichier (optionnel) :</label>
                <input type="file" name="fichier" onchange="afficherNomFichier(this)">
                <span id="nom-fichier"></span>

                <button type="submit">Envoyer</button>
            <?php endif; ?>
        </form>

        <?php if ($niveau && $matiere_id): ?>
            <div class="forum">
                <h3>Messages - <?= htmlspecialchars($niveau) ?> / <?= isset($messages[0]['matiere_nom']) ? htmlspecialchars($messages[0]['matiere_nom']) : '' ?></h3>

                <?php if ($messages): ?>
                    


                    <?php foreach ($messages as $msg): ?>
    <div class="message <?= $msg['type_auteur'] === 'enseignant' ? 'message-enseignant' : 'message-autre' ?>">
        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>

        <!-- Affichage du fichier en bas du texte -->
        <?php if (!empty($msg['fichier'])): ?>
            <div class="fichier-section">
                <?php
                    $ext = pathinfo($msg['fichier'], PATHINFO_EXTENSION);
                    $fichier_path = "../uploads/" . htmlspecialchars($msg['fichier']);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
                        echo '<a href="' . $fichier_path . '" target="_blank" class="fichier-lien">Voir le fichier</a>';
                    } else {
                        echo '<p><em>Fichier non supporté pour l\'affichage</em></p>';
                    }
                ?>
            </div>
        <?php endif; ?>

        <small>Posté le <?= $msg['date_message'] ?></small>
    </div>
<?php endforeach; ?>






                <?php else: ?>
                    <p>Aucun message pour ce niveau et cette matière.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
