<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM etudiants ORDER BY nom ASC");
$etudiants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Étudiants | Administration</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap">
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

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .admin-title {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 1.8rem;
        }

        .admin-logout {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .admin-logout:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .students-table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .students-table th {
            padding: 15px;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .students-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .students-table tbody tr:last-child td {
            border-bottom: none;
        }

        .students-table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .status-active {
            color: var(--success-color);
            font-weight: 500;
        }

        .status-inactive {
            color: var(--warning-color);
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-edit {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-edit:hover {
            background-color: #2980b9;
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        @media (max-width: 768px) {
            .students-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Gestion des Étudiants</h1>
            <a href="logout.php" class="admin-logout">Déconnexion</a>
        </div>

        <table class="students-table">
            <thead>
                <tr>
                    <th>Numéro Inscription</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Sexe</th>
                    <th>Niveau</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etd): ?>
                    <tr>
                        <td><?= htmlspecialchars($etd['numero_inscription']) ?></td>
                        <td><?= htmlspecialchars($etd['nom']) ?></td>
                        <td><?= htmlspecialchars($etd['prenom']) ?></td>
                        <td><?= htmlspecialchars($etd['email']) ?></td>
                        <td><?= htmlspecialchars($etd['sexe']) ?></td>
                        <td><?= htmlspecialchars($etd['niveau']) ?></td>
                        <td class="<?= $etd['statut'] === 'actif' ? 'status-active' : 'status-inactive' ?>">
                            <?= htmlspecialchars($etd['statut']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>