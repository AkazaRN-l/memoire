<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("Accès refusé.");
}

// Gérer la validation ou le refus
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['action'])) {
    $id = $_POST['id'];
    $statut = ($_POST['action'] == 'valider') ? 'validé' : 'refusé';

    $sql = "UPDATE etudiants SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $statut, $id);
    if ($stmt->execute()) {
        echo "Statut mis à jour.";
    } else {
        echo "Erreur : " . $stmt->error;
    }
    $stmt->close();
}

$sql = "SELECT * FROM etudiants WHERE statut = 'en attente'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation des Inscriptions</title>
    <link rel="stylesheet" href="../css/admin_validation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Validation des inscriptions</h1>
            <p>Interface administrateur</p>
        </header>

        <main class="admin-main">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($row['photo']) ?>" alt="Photo de profil" class="user-photo"></td>
                        <td><?= htmlspecialchars($row['nom']) ?></td>
                        <td><?= htmlspecialchars($row['prenom']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['niveau']) ?></td>
                        <td class="actions">
                            <form method="post">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="valider" class="btn btn-validate"><i class="fas fa-check"></i> Valider</button>
                                <button type="submit" name="action" value="refuser" class="btn btn-reject"><i class="fas fa-times"></i> Refuser</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </main> 

        <footer class="admin-footer">
            <p>&copy; 2025 Télécommunications. Tous droits réservés.</p>
        </footer>
    </div>
</body>
</html>

<?php $conn->close(); ?>