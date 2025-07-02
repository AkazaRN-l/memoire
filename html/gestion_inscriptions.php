<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("Accès refusé. Vous n'êtes pas autorisé à voir cette page.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['action'])) {
    $id = $_POST['id'];
    $statut = ($_POST['action'] == 'valider') ? 'validé' : 'refusé';

    $sql = "UPDATE etudiants SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $statut, $id);
    $stmt->execute();
    $stmt->close();
}

$sql = "SELECT * FROM etudiants WHERE statut = 'en attente'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Inscriptions</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <div class="container">
        <h2>Gestion des Inscriptions</h2>
        <table border="1">
            <tr>
                <th>Photo</th>
                <th>Numéro</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><img src="<?= htmlspecialchars($row['photo']) ?>" width="50"></td>
                <td><?= htmlspecialchars($row['numero_inscription']) ?></td>
                <td><?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['prenom']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="valider">✅ Valider</button>
                        <button type="submit" name="action" value="refuser">❌ Refuser</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>