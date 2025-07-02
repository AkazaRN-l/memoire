<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}

// Récupération du chef de mention
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'chef_mention' LIMIT 1");
$chef = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur | Panel de Contrôle</title>
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
            --sidebar-width: 250px;
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
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 1rem;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar h2 {
            color: white;
            font-size: 1.3rem;
            font-weight: 500;
        }

        .sidebar-menu {
            list-style: none;
            margin-top: 2rem;
        }

        .sidebar-menu li {
            margin-bottom: 0.8rem;
        }

        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.6rem 0.8rem;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            display: block;
            margin-top: 2rem;
            padding: 0.6rem 0.8rem;
            background-color: rgba(231, 76, 60, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: var(--danger-color);
        }

        .logout-btn i {
            margin-right: 8px;
        }

        .content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 1.8rem;
        }

        .welcome-message {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .section {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section h2 {
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .section h2 i {
            margin-right: 10px;
            color: var(--accent-color);
        }

        .chef-info {
            background-color: rgba(52, 152, 219, 0.1);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--accent-color);
        }

        .chef-info p {
            margin-bottom: 0.5rem;
        }

        .chef-info strong {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            border: none;
            font-size: 0.95rem;
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

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #219955;
            transform: translateY(-2px);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .card p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .card-icon {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }

            .sidebar-header h2, .sidebar-menu span {
                display: none;
            }

            .sidebar-menu a {
                justify-content: center;
            }

            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-user-shield"></i> Administration</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="gerer_proff.php"><i class="fas fa-chalkboard-teacher"></i> <span>Gérer Enseignants</span></a></li>
            <li><a href="gerer_etudiant.php"><i class="fas fa-user-graduate"></i> <span>Gérer Étudiants</span></a></li>
            <li><a href="#" class="active"><i class="fas fa-user-tie"></i> <span>Chef de Mention</span></a></li>
        </ul>
        <a href="logout.php?redirect=google" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a>
    </div>

    <div class="content">
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Tableau de Bord Administrateur</h1>
                <p class="welcome-message">Bienvenue, <?php echo htmlspecialchars($_SESSION['admin']['prenom'] ?? 'Admin'); ?></p>
            </div>
        </div>

        <section class="section" id="passassion">
            <h2><i class="fas fa-exchange-alt"></i> Passassion de Chef de Mention</h2>
            
            <?php if ($chef): ?>
                <div class="chef-info">
                    <p><strong>Chef actuel :</strong> <?php echo htmlspecialchars($chef['prenom'] . ' ' . $chef['nom']); ?></p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($chef['email']); ?></p>
                    <p><strong>Matricule :</strong> <?php echo htmlspecialchars($chef['numero_matricule'] ?? 'N/A'); ?></p>
                </div>
                
                <form method="POST" action="supprimer_chef.php" onsubmit="return confirm('Confirmez-vous la passassion ? Le chef de mention actuel sera retiré, et un message lui sera envoyé.')">
                    <input type="hidden" name="chef_id" value="<?php echo $chef['id']; ?>">
                    <input type="hidden" name="chef_email" value="<?php echo $chef['email']; ?>">
                    <input type="hidden" name="chef_nom" value="<?php echo $chef['nom']; ?>">
                    <input type="hidden" name="chef_prenom" value="<?php echo $chef['prenom']; ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-user-minus"></i> Effectuer la Passassion</button>
                </form>
            <?php else: ?>
                <div class="chef-info" style="border-left-color: var(--warning-color);">
                    <p><em>Aucun chef de mention actuellement enregistré.</em></p>
                </div>
                
                <h3><i class="fas fa-user-plus"></i> Ajouter un nouveau Chef</h3>
                <form action="ajouter_chef.php" method="POST">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Prénom" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="numero_matricule">Numéro Matricule</label>
                        <input type="text" id="numero_matricule" name="numero_matricule" class="form-control" placeholder="Numéro Matricule" required>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Ajouter Chef</button>
                </form>
            <?php endif; ?>
        </section>

        <div class="grid">
            <div class="card">
                <div class="card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3>Gestion des Enseignants</h3>
                <p>Superviser, corriger ou gérer les comptes et activités des enseignants.</p>
                <a href="gerer_proff.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Accéder</a>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
                <h3>Gestion des Étudiants</h3>
                <p>Contrôler l'activité des étudiants, vérifier les erreurs, et intervenir si nécessaire.</p>
                <a href="gerer_etudiant.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Accéder</a>
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