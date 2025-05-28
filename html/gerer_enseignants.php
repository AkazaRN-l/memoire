<?php
include 'config.php';
session_start();

// Vérifier si l'utilisateur est bien le chef de mention
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

// Ajouter un enseignant
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST["matricule"];
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $specialite = $_POST["specialite"];

    $sql = "INSERT INTO enseignants (numero_matricule, nom, prenom, email, specialite) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $matricule, $nom, $prenom, $email, $specialite);

    if ($stmt->execute()) {
        header("Location: gerer_enseignants.php?success=1");
        exit();
    } else {
        die("❌ Erreur lors de l'ajout.");
    }

    $stmt->close();
}

// Récupérer les enseignants
$sql_enseignants = "SELECT * FROM enseignants";
$result_enseignants = $conn->query($sql_enseignants);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Enseignants</title>
    <link rel="stylesheet" href="../css/gerer_enseignant.css">
</head>
<body>
    <div class="main-container">
        <!-- Carte Ajout Enseignant -->
        <div class="premium-card">
            <div class="section-header">
                <h2>👨‍🏫 Ajouter un Enseignant</h2>
            </div>

            <?php if (isset($_GET['success'])) : ?>
                <div class="success-message">
                    ✅ Enseignant ajouté avec succès !
                </div>
            <?php endif; ?>

            <form class="teacher-form" action="gerer_enseignants.php" method="POST">
                <div class="form-group">
                    <label>Numéro Matricule :</label>
                    <input type="text" name="matricule" class="form-control" required>
                    <label>Nom :</label>
                    <input type="text" name="nom" class="form-control" required>
                    <label>Prénom :</label>
                    <input type="text" name="prenom" class="form-control" required>
                    <label>Email :</label>
                    <input type="email" name="email" class="form-control" required>
                    <label>Spécialité :</label>
                    <input type="text" name="specialite" class="form-control" required>
                </div>
                <br> <br> <br>
                <button type="submit" class="btn-premium">➕ Ajouter</button>
            </form>
        </div>

        <!-- Carte Liste Enseignants -->
        <div class="premium-card">
            <div class="section-header">
                <h2>📜 Liste des Enseignants</h2>
            </div>

            <div class="table-container">
                <table class="teacher-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Numéro Matricule</th>
                            <th>Spécialité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_enseignants->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nom']) ?></td>
                            <td><?= htmlspecialchars($row['prenom']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['numero_matricule']) ?></td>
                            <td><?= htmlspecialchars($row['specialite']) ?></td>
                            <td>
                              <a href="supprimer_enseignant.php?id=<?= $row['id'] ?>" class="btn-action btn-delete">
                                 <span class="btn-icon">🗑️</span>
                                 <span class="btn-text">Supprimer</span>
                               </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="dashboard_chef.php" class="back-btn">🔙 Retour au tableau de bord</a>
    </div>

    <script src="../js/gerer_enseignant.js"></script>
</body>
</html>