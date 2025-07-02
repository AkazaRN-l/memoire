<?php
session_start();
require 'config.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

$success = '';
$error = '';

// Supprimer enseignant
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $stmt = $conn->prepare("DELETE FROM enseignants WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Enseignant supprimé avec succès.";
    } else {
        $error = "Erreur lors de la suppression.";
    }
}

// Ajouter un enseignant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $matricule = trim($_POST['numero_matricule']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $specialite = trim($_POST['specialite']);
    $motdepasse = bin2hex(random_bytes(4)); // Génère un mot de passe simple (8 caractères)
    $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

    // Insertion
    $stmt = $conn->prepare("INSERT INTO enseignants (numero_matricule, nom, prenom, email, specialite, motdepasse) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $matricule, $nom, $prenom, $email, $specialite, $hash);

    if ($stmt->execute()) {
        $success = "Enseignant ajouté. Mot de passe temporaire : <strong>$motdepasse</strong>";
    } else {
        $error = "Erreur lors de l'ajout : " . $conn->error;
    }
}

// Récupérer tous les enseignants
$enseignants = $conn->query("SELECT * FROM enseignants ORDER BY nom ASC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Enseignants | Administration</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 12px;
            color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .table tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 0 15px;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .table th, .table td {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 10px 18px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="dashboard_admin.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>

        <div class="card">
            <h2 class="card-title"><i class="fas fa-chalkboard-teacher"></i> Gestion des enseignants</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <h3 class="card-title" style="font-size: 1.2rem;"><i class="fas fa-user-plus"></i> Ajouter un enseignant</h3>
                
                <div class="form-group">
                    <label for="numero_matricule" class="form-label">Numéro matricule</label>
                    <input type="text" id="numero_matricule" name="numero_matricule" class="form-control" placeholder="Entrez le numéro matricule" required>
                </div>
                
                <div class="form-group">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-control" placeholder="Entrez le nom" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Entrez le prénom" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Entrez l'email" required>
                </div>
                
                <div class="form-group">
                    <label for="specialite" class="form-label">Spécialité</label>
                    <input type="text" id="specialite" name="specialite" class="form-control" placeholder="Entrez la spécialité" required>
                </div>
                
                <button type="submit" name="ajouter" class="btn btn-primary">
                    <i class="fas fa-save"></i> Ajouter l'enseignant
                </button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title"><i class="fas fa-list"></i> Liste des enseignants</h2>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Spécialité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $enseignants->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['numero_matricule']) ?></td>
                            <td><?= htmlspecialchars($row['nom']) ?></td>
                            <td><?= htmlspecialchars($row['prenom']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['specialite']) ?></td>
                            <td class="actions">
                                <a href="?supprimer=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?')">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Animation pour les boutons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mousedown', () => {
                btn.style.transform = 'translateY(0)';
            });
            btn.addEventListener('mouseup', () => {
                btn.style.transform = 'translateY(-2px)';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>