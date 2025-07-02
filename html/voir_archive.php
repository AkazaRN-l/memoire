<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chef_mention') {
    die("❌ Accès refusé.");
}

$annee_id = $_GET['annee_id'] ?? die("ID d'année manquant");

// Fonction helper pour afficher les valeurs en toute sécurité
function safe_print($value, $default = '') {
    return isset($value) ? htmlspecialchars($value) : $default;
}

$year_id = intval($_GET['annee_id'] ?? 0);
if ($year_id <= 0) die("Année non spécifiée");

// Récupérer l'année académique
$stmt = $conn->prepare("SELECT * FROM annee_academique WHERE id = ?");
$stmt->bind_param("i", $year_id);
$stmt->execute();
$year = $stmt->get_result()->fetch_assoc();

if (!$year) die("Année invalide");

// Récupérer les cours archivés avec pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$courses_query = $conn->prepare("
    SELECT 
        a.id as archive_id,
        a.cours_id,
        a.annee_id,
        a.titre,
        a.description,
        a.date_creation,
        a.fichier_path,
        a.fichier_nom,
        a.matiere_id,
        a.enseignant_id,
        a.date_archivage,
        e.nom, 
        e.prenom, 
        m.nom as matiere
    FROM archives_cours a
    LEFT JOIN enseignants e ON a.enseignant_id = e.id
    LEFT JOIN matieres m ON a.matiere_id = m.id
    WHERE a.annee_id = ?
    ORDER BY a.date_archivage DESC
    LIMIT ? OFFSET ?
");

$courses_query->bind_param("iii", $year_id, $per_page, $offset);
$courses_query->execute();
$archived_courses = $courses_query->get_result();

// Compter le nombre total de cours
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM archives_cours WHERE annee_id = ?");
$count_query->bind_param("i", $year_id);
$count_query->execute();
$total_courses = $count_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $per_page);

// Récupérer 5 dernières informations archivées
$infos_query = $conn->prepare("
    SELECT i.* 
    FROM archives_informations a
    JOIN informations i ON a.info_id = i.id
    WHERE a.annee_id = ?
    ORDER BY i.date_envoi DESC
    LIMIT 5
");

$infos_query->bind_param("i", $year_id);
$infos_query->execute();
$archived_infos = $infos_query->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Archives <?= safe_print($year['annee']) ?></title>
    <link rel="stylesheet" href="../css/dashboard_chef.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-container { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-card { flex: 1; min-width: 200px; background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #2c3e50; margin: 10px 0; }
        .stat-label { color: #7f8c8d; font-size: 14px; }
        .search-box { margin: 20px 0; display: flex; gap: 10px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { padding: 10px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .pagination { display: flex; justify-content: center; margin: 20px 0; gap: 5px; }
        .pagination a { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #3498db; }
        .pagination a.active { background: #3498db; color: white; border-color: #3498db; }
        .export-options { margin: 20px 0; text-align: right; }
        .export-btn { padding: 8px 15px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .tabs { display: flex; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-bottom-color: #3498db; font-weight: bold; }
        .no-file { color: #95a5a6; font-style: italic; font-size: 0.9em; }
        .btn-view, .btn-restore { padding: 5px 8px; border-radius: 4px; margin: 0 3px; text-decoration: none; display: inline-block; }
        .btn-view { background: #3498db; color: white; }
        .btn-restore { background: #2ecc71; color: white; }
        .btn-view:hover { background: #2980b9; }
        .btn-restore:hover { background: #27ae60; }
        .empty-state { text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; color: #7f8c8d; }
        .empty-state i { font-size: 48px; margin-bottom: 15px; display: block; color: #bdc3c7; }
        .courses-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .courses-table th, .courses-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .courses-table th { background: #f8f9fa; font-weight: 600; }
        .courses-table tr:hover { background: #f5f5f5; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
    <img src="../photo/satellite-dish-310868_1920.png" alt="Logo Chef" style="width: 50px; display: block; margin: 0 auto 10px;">
        <h2>Responsable de Mention</h2>
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
                <li><a href="logout_chef.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <main class="dashboard-main">
        <header>
            <h1><i class="fas fa-archive"></i> Archives - Année <?= safe_print($year['annee']) ?></h1>
            <p>
                <i class="fas fa-calendar-alt"></i> 
                Période: <?= !empty($year['date_debut']) ? date('d/m/Y', strtotime($year['date_debut'])) : '' ?> - 
                <?= !empty($year['date_fin']) ? date('d/m/Y', strtotime($year['date_fin'])) : '' ?>
            </p>
        </header>

        <div class="content">
            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= $total_courses ?></div>
                    <div class="stat-label"><i class="fas fa-book"></i> Cours Archivés</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= $archived_infos->num_rows ?></div>
                    <div class="stat-label"><i class="fas fa-info-circle"></i> Informations</div>
                </div>
            </div>

            <!-- Options d'export -->
            <div class="export-options">
                <a href="export_archive.php?annee_id=<?= $year_id ?>&type=cours" class="export-btn">
                    <i class="fas fa-file-export"></i> Exporter les cours
                </a>
                <a href="export_archive.php?annee_id=<?= $year_id ?>&type=informations" class="export-btn">
                    <i class="fas fa-file-export"></i> Exporter les informations
                </a>
            </div>

            <!-- Onglets -->
            <div class="tabs">
                <div class="tab active" data-tab="cours">Cours</div>
                <div class="tab" data-tab="informations">Informations</div>
            </div>

            <!-- Recherche -->
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Rechercher dans les archives...">
                <button id="search-btn"><i class="fas fa-search"></i> Rechercher</button>
            </div>

            <!-- Contenu des onglets -->
            <div id="cours-tab" class="tab-content">
                <h2><i class="fas fa-book-open"></i> Cours Archivés <small>(<?= $total_courses ?> résultats)</small></h2>
                
                <?php if ($archived_courses->num_rows > 0): ?>
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Matière</th>
                                <th>Enseignant</th>
                                <th>Date</th>
                                <th>Fichier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $archived_courses->fetch_assoc()): ?>
                            <tr>
                            <td><?= safe_print($course['titre'] ?? 'Titre non disponible') ?></td>
<td><?= safe_print($course['matiere'] ?? 'Matière non spécifiée') ?></td>
<td><?= safe_print(($course['nom'] ?? '') . ' ' . ($course['prenom'] ?? '')) ?></td>
                                <td><?= !empty($course['date_envoi']) ? date('d/m/Y H:i', strtotime($course['date_envoi'])) : '' ?></td>
                                <td>
    <?php if (!empty($course['fichier_path'])): ?>
        <i class="fas fa-file-alt"></i> <?= safe_print($course['fichier_nom'] ?? 'Fichier joint') ?>
    <?php else: ?>
        <span class="no-file">Aucun fichier</span>
    <?php endif; ?>
</td>
<td>
    <?php if (!empty($course['fichier_path'])) { ?>
        <a href="../uploads/<?= safe_print($course['fichier_path']) ?>" 
           class="btn-view" download
           title="Télécharger le fichier">
            <i class="fas fa-download"></i>
        </a>
    <?php } else { ?>
        <span class="no-file" title="Fichier indisponible">
            <i class="fas fa-times-circle"></i>
        </span>
    <?php } ?>
    
    <a href="restaurer_cours.php?id=<?= $course['cours_id'] ?>&annee_id=<?= $year_id ?>" 
       class="btn-restore"
       title="Restaurer ce cours"
       onclick="return confirm('Êtes-vous sûr de vouloir restaurer ce cours ?')">
        <i class="fas fa-undo"></i>
    </a>
</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?annee_id=<?= $year_id ?>&page=<?= $page-1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?annee_id=<?= $year_id ?>&page=<?= $i ?>" 
                               class="<?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?annee_id=<?= $year_id ?>&page=<?= $page+1 ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <p>Aucun cours archivé pour cette année.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div id="informations-tab" class="tab-content" style="display:none;">
    <h2><i class="fas fa-info-circle"></i> Informations Archivées</h2>
    
    <?php if ($archived_infos->num_rows > 0): ?>
        <div class="info-grid">
            <?php while ($info = $archived_infos->fetch_assoc()): ?>
            <div class="info-card">
                <div class="info-header">
                    <h3><?= safe_print($info['titre']) ?></h3>
                    <div class="info-meta">
                        <span class="info-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?= !empty($info['date_envoi']) ? date('d/m/Y H:i', strtotime($info['date_envoi'])) : 'Date inconnue' ?>
                        </span>
                    </div>
                </div>
                <div class="info-content">
                    <?= nl2br(safe_print($info['contenu'])) ?>
                </div>
                <?php if (!empty($info['fichier'])): ?>
                <div class="info-footer">
                    <a href="../uploads/<?= safe_print($info['fichier']) ?>" class="info-file" download>
                        <i class="fas fa-paperclip"></i> <?= safe_print($info['fichier_nom'] ?? 'Fichier joint') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-info-circle"></i>
            <p>Aucune information archivée pour cette année.</p>
        </div>
    <?php endif; ?>
</div>
        </div>
    </main>
</div>

<script>
// Gestion des onglets
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Désactiver tous les onglets
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        
        // Activer l'onglet cliqué
        this.classList.add('active');
        const tabName = this.getAttribute('data-tab');
        document.getElementById(tabName + '-tab').style.display = 'block';
    });
});

// Recherche
document.getElementById('search-btn').addEventListener('click', function() {
    const query = document.getElementById('search-input').value.trim();
    if (query) {
        window.location.href = `recherche_archive.php?annee_id=<?= $year_id ?>&q=${encodeURIComponent(query)}`;
    }
});
</script>
</body>
</html>