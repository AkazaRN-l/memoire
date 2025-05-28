<?php
session_start();
include 'config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Vérification de session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

// Récupération du niveau actif (sécurisé même sans POST)
$niveau_actuel = $_GET['niveau'] ?? $_POST['niveau'] ?? 'Licence I';

// --- Gestion des matières ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_matiere'])) {
        $matiere = $_POST['nom_matiere'];
        $niveau = $_POST['niveau_matiere'];

        $sql = "INSERT INTO matieres (nom, niveau) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $matiere, $niveau);
        $stmt->execute();
    } elseif (isset($_POST['supprimer_matiere'])) {
        $id_matiere = $_POST['id_matiere'];

        $sql = "DELETE FROM matieres WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_matiere);
        $stmt->execute();
    }
}

// Validation d’un étudiant + envoi email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etudiant_id'])) {
    $etudiant_id = intval($_POST['etudiant_id']);

    //  Mettre à jour le statut dans la bonne table : "etudiants"
    $sql = "UPDATE etudiants SET statut = 'validé' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();

    //  Récupérer les infos de l’étudiant à partir de "etudiants"
    $sql = "SELECT nom, prenom, email FROM etudiants WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $etudiant = $result->fetch_assoc();

    // Envoi email si email valide
    if ($etudiant && !empty($etudiant['email'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rahaja.ranjanirina@gmail.com';         // Ton adresse Gmail
            $mail->Password = 'lmgf mzzx nyut xseq';       // Mot de passe d'application
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rahaja.ranjanirina@gmail.com', 'Université de Vakinankaratra');
            $mail->addAddress($etudiant['email'], $etudiant['prenom'] . ' ' . $etudiant['nom']);

            $mail->isHTML(false);
            $mail->Subject = 'Validation de votre inscription';
            $mail->Body = "Bonjour {$etudiant['prenom']} {$etudiant['nom']},\n\nVotre inscription a été validée par le chef de mention. Vous pouvez maintenant accéder à la plateforme.\n\nCordialement,\nMention Télécommunication";

            $mail->send();
            $_SESSION['success'] = "✅ Étudiant validé avec succès, email envoyé à {$etudiant['email']}";
        } catch (Exception $e) {
            $_SESSION['error'] = "❌ L'email n'a pas pu être envoyé. Erreur : " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = "❌ Étudiant introuvable ou email manquant.";
    }

    header("Location: dashboard_chef.php?niveau=" . urlencode($_POST['niveau']));
    exit();
}


// --- Récupérer les matières par niveau ---
$stmt = $conn->prepare("SELECT * FROM matieres WHERE niveau = ? ORDER BY nom");
$stmt->bind_param("s", $niveau_actuel);
$stmt->execute();
$result_matieres = $stmt->get_result();

// --- Étudiants en attente et validés ---
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE niveau = ? AND statut = 'en attente'");
$stmt->bind_param("s", $niveau_actuel);
$stmt->execute();
$result_attente = $stmt->get_result();

$stmt = $conn->prepare("SELECT * FROM etudiants WHERE niveau = ? AND statut = 'validé'");
$stmt->bind_param("s", $niveau_actuel);
$stmt->execute();
$result_valides = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Chef de Mention</title>
    <link rel="stylesheet" href="../css/dashboard_chef.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">

    <img src="../photo/satellite-dish-310868_1920.png" alt="Logo Chef" style="width: 50px; display: block; margin: 0 auto 10px;">
        <h2>Chef de Mention</h2>
        <nav>
            <ul>
                <li><a href="dashboard_chef.php?niveau=Licence I">Licence I</a></li>
                <li><a href="dashboard_chef.php?niveau=Licence II">Licence II</a></li>
                <li><a href="dashboard_chef.php?niveau=Licence III">Licence III</a></li>
                <li><a href="dashboard_chef.php?niveau=Master I">Master I</a></li>
                <li><a href="dashboard_chef.php?niveau=Master II">Master II</a></li>
                <li><a href="gerer_enseignants.php">🏫 Gérer Enseignants</a></li>
                <li><a href="envoyer_information.php">📢 Envoyer Information</a></li>
                <li><a href="gerer_annee_academique.php">📅 Gérer Année Académique</a></li>
                <li><a href="mon_compte.php">⚙️ Mon compte</a></li>
                <li><a href="logout_chef.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <main class="dashboard-main">
        <header>
            <h1>👨‍🏫 Gestion - <?= htmlspecialchars($niveau_actuel) ?></h1>
        </header>


        <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>





        <div class="content">
            <!-- Gestion des matières -->
            <section class="matieres-section">
                <h2>📚 Gestion des Matières</h2>
                <form method="POST" class="form-ajout-matiere">
                    <input type="text" name="nom_matiere" placeholder="Nom de la matière" required>
                    <input type="hidden" name="niveau_matiere" value="<?= $niveau_actuel ?>">
                    <button type="submit" name="ajouter_matiere" class="btn-ajouter">➕ Ajouter</button>
                </form>
                
                <h3> Liste des matières</h3>
                <table class="matieres-table">
                    <tr>
                        <th>Matière</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($matiere = $result_matieres->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($matiere['nom']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id_matiere" value="<?= $matiere['id'] ?>">
                                <button type="submit" name="supprimer_matiere" class="btn-supprimer">❌ Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </section>

            <section class="student-list">
                <h2>⏳ Étudiants en attente</h2>
                <table>
    <tr>
        <th>Photo</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>N° Inscription</th>
        <th>Email</th>
        <th>Téléphone</th> 
        <th>Sexe</th>
        <th>Date de Naissance</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result_attente->fetch_assoc()) { ?>
    <tr>
        <td><img src="<?= htmlspecialchars($row['photo']) ?>" class="photo-profil"></td>
        <td><?= htmlspecialchars($row['nom']) ?></td>
        <td><?= htmlspecialchars($row['prenom']) ?></td>
        <td><?= htmlspecialchars($row['numero_inscription']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['telephone']) ?></td> 
        <td><?= htmlspecialchars($row['sexe']) ?></td>
        <td><?= htmlspecialchars($row['date_naissance']) ?></td>
        <td>
    <form method="POST" action="dashboard_chef.php?niveau=<?= urlencode($niveau_actuel) ?>" style="display:inline;">
        <input type="hidden" name="etudiant_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="niveau" value="<?= $niveau_actuel ?>">
        <button type="submit" class="btn-valider">✅ Valider</button> <br>
    </form>

    <a href="supprimer_etudiant.php?id=<?= $row['id'] ?>" class="btn-supprimer" 
       onclick="return confirm('Supprimer cet étudiant ?')">❌ Supprimer</a>
</td>

    </tr>
    <?php } ?>
</table>
            </section>

            <section class="student-list">
                <h2>✔ Étudiants validés</h2>
                <table>
    <tr>
        <th>Photo</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>N° Inscription</th>
        <th>Email</th>
        <th>Téléphone</th>
        <th>Âge</th>
        <th>Sexe</th>
        <th>Actions</th>
        <th>Décisions</th>
    </tr>
    <?php while ($row = $result_valides->fetch_assoc()) { ?>
    <tr>
        <td><img src="<?= htmlspecialchars($row['photo']) ?>" class="photo-profil"></td>
        <td><?= htmlspecialchars($row['nom']) ?></td>
        <td><?= htmlspecialchars($row['prenom']) ?></td>
        <td><?= htmlspecialchars($row['numero_inscription']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['telephone']) ?></td>
        <td><?= htmlspecialchars($row['age']) ?></td>
        <td><?= htmlspecialchars($row['sexe']) ?></td>
        <td>
            <a href="supprimer_etudiant.php?id=<?= $row['id'] ?>" class="btn-supprimer">❌ Supprimer</a>
        </td>
        <td>
            <a href="retrograder_etudiant.php?id=<?= $row['id'] ?>&niveau=<?= urlencode($niveau_actuel) ?>" class="btn-retour">⬆ Retour</a><br>
            <a href="promouvoir_etudiants.php?id=<?= $row['id'] ?>&niveau=<?= urlencode($niveau_actuel) ?>" class="btn-valider">⬇ Passer</a>
        </td>
    </tr>
    <?php } ?>
</table>

            </section>
        </div>
    </main>
</div>

<script>
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
}, 5000);
</script>

</body>
</html>
