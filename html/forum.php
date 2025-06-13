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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - <?= htmlspecialchars($matiere_nom) ?></title>
    <link rel="stylesheet" href="../css/forum.css">
</head>
<body>
    <div class="forum-container">
        <header class="forum-header">
            <h1 class="forum-title">Forum : <?= htmlspecialchars($matiere_nom) ?></h1>
            <div class="user-info">
                <p class="user-details">Connecté : <span class="user-name"><?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?></span> <span class="user-role">(<?= htmlspecialchars($role) ?>)</span></p>
                <a href="<?= $role === 'etudiant' ? 'dashboard_etudiant.php' : 'dashboard_enseignant.php' ?>" class="btn btn-back">Retour au tableau de bord</a>
            </div>
        </header>

        <main class="forum-content">
            <form method="POST" enctype="multipart/form-data" class="message-form">
                <div class="form-group">
                    <textarea name="message" placeholder="Votre message" class="message-input" required></textarea>
                </div>
                <div class="form-group">
                <div class="file-upload">
    <input type="file" id="fichier" name="fichier" style="display: none;">
    <label for="fichier" class="file-label">Ajouter un fichier</label>
</div>

                </div>
                <button type="submit" class="btn btn-send">Envoyer</button>
            </form>

            <section class="messages-section">
                <h2 class="section-title">Messages</h2>
                <?php if (!empty($messages)): ?>
                    <div class="messages-list">
                        <?php foreach ($messages as $msg): ?>
                            <article class="message-card">
                                <header class="message-header">
                                    <h3 class="message-author"><?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?> <span class="author-role">(<?= $msg['type_auteur'] ?>)</span></h3>
                                    <time class="message-date"><?= $msg['date_message'] ?></time>
                                </header>
                                <div class="message-content">
                                    <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                </div>
                                <?php if (!empty($msg['fichier'])): ?>
                                    <footer class="message-footer">
                                        <?php
                                            $ext = pathinfo($msg['fichier'], PATHINFO_EXTENSION);
                                            $fichier_path = "../uploads/" . htmlspecialchars($msg['fichier']);
                                            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
                                                echo '<a href="' . $fichier_path . '" target="_blank" class="file-link">Voir le fichier</a>';
                                            } else {
                                                echo '<p class="file-notice">Fichier non supporté pour l\'affichage</p>';
                                            }
                                        ?>
                                    </footer>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-message">Aucun message pour cette matière.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>



    <script>
    // Auto-scroll en bas
    window.addEventListener("load", () => {
        const section = document.querySelector(".messages-section");
        section.scrollTop = section.scrollHeight;
    });

    // Affichage du nom de fichier
    document.getElementById("fichier").addEventListener("change", function () {
        const label = document.querySelector(".file-label");
        const fileName = this.files[0] ? this.files[0].name : "Ajouter un fichier";
        label.textContent = fileName.length > 30 ? fileName.slice(0, 27) + "..." : fileName;
    });
</script>

</body>
</html>