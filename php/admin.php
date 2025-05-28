<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'username', 'password', 'database');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les inscriptions en attente
$sql = "SELECT * FROM utilisateurs WHERE statut = 'en_attente'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des inscriptions</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="background"></div>
    <div class="container">
        <h1>Validation des inscriptions</h1>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Niveau</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- Afficher la photo de profil -->
                        <td>
                            <img src="<?= $row['photo'] ?>" alt="Photo de profil" width="50" height="50" style="border-radius: 50%;">
                        </td>
                        <!-- Afficher les informations de l'utilisateur -->
                        <td><?= $row['nom'] ?></td>
                        <td><?= $row['prenom'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['niveau'] ?></td>
                        <!-- Boutons pour valider ou refuser -->
                        <td>
                            <a href="valider_inscription.php?id=<?= $row['id'] ?>&action=valider" class="btn">Valider</a>
                            <a href="valider_inscription.php?id=<?= $row['id'] ?>&action=refuser" class="btn btn-danger">Refuser</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>