<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['enseignants'])) {
    header('Location: login_enseignant.php');
    exit();
}

$enseignant = $_SESSION['enseignants'];

// Récupérer les activités récentes
try {
    $stmt = $pdo->prepare("SELECT * FROM activites WHERE enseignant_id = ? ORDER BY date_creation DESC LIMIT 5");
    $stmt->execute([$enseignant['id']]);
    $activites = $stmt->fetchAll();
} catch (PDOException $e) {
    $activites = [];
    $error_message = "Erreur de chargement des activités";
}

// Récupérer les statistiques
$stats = [
    'cours' => 0,
    'eleves' => 0,
    'conferences' => 0
];

// Vérifiez que la requête des activités correspond à votre structure
try {
    $stmt = $pdo->prepare("SELECT a.*, e.nom, e.prenom 
                          FROM activites a
                          JOIN enseignants e ON a.enseignant_id = e.id
                          WHERE a.enseignant_id = ? 
                          ORDER BY a.date_creation DESC LIMIT 5");
    $stmt->execute([$enseignant['id']]);
    $activites = $stmt->fetchAll();
} catch (PDOException $e) {
    $activites = [];
    error_log("Erreur activités: " . $e->getMessage());
}

// Traitement des messages de succès/erreur
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= htmlspecialchars($enseignant['prenom']) ?></title>
    <link rel="stylesheet" href="../css/dashboard_enseignant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <main class="dashboard-main">
            <header class="dashboard-header">
                <div class="profile-info">
                    <div class="avatar">
                        <?= strtoupper(substr($enseignant['prenom'], 0, 1) . substr($enseignant['nom'], 0, 1)) ?>
                    </div>
                    <div class="profile-text">
                        <h1><?= htmlspecialchars($enseignant['prenom']) ?> <?= htmlspecialchars($enseignant['nom']) ?></h1>
                        <p><?= htmlspecialchars($enseignant['email']) ?></p>
                    </div>
                </div>
                <form action="logout_enseignant.php">
                    <button class="btn btn-logout" id="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Le cours a été publié avec succès!
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> Erreur lors de la publication du cours: <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="card-grid">
                <!-- Card Visioconférence -->
                <div class="card card-video">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h2 class="card-title">Nouvelle Visioconférence</h2>
                    </div>
                    
                    <div class="card-body">
                        <form id="video-form" action="creer_conference.php" method="POST">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-layer-group"></i> Choisir un niveau
                                </label>
                                <div class="niveaux-radio">
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="L1" required>
                                        <span class="niveau-badge">Licence 1</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="L2">
                                        <span class="niveau-badge">Licence 2</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="L3">
                                        <span class="niveau-badge">Licence 3</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="M1">
                                        <span class="niveau-badge">Master 1</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="M2">
                                        <span class="niveau-badge">Master 2</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" id="start-video">
                                    <i class="fas fa-play"></i> Démarrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card Envoi de cours -->
                <div class="card card-upload">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h2 class="card-title">Publier un cours</h2>
                    </div>
                    
                    <div class="card-body">
                        <form id="upload-form" action="uploade_cours.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="cours-titre" class="form-label">
                                    <i class="fas fa-heading"></i> Titre du cours
                                </label>
                                <input type="text" id="cours-titre" name="titre" class="form-control" placeholder="Introduction à..." required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-layer-group"></i> Niveau destinataire
                                </label>
                                <div class="niveaux-radio">
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="Licence I" required>
                                        <span class="niveau-badge">Licence 1</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="Licence II">
                                        <span class="niveau-badge">Licence 2</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="Licence III">
                                        <span class="niveau-badge">Licence 3</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="Master I">
                                        <span class="niveau-badge">Master 1</span>
                                    </label>
                                    <label class="niveau-option">
                                        <input type="radio" name="niveau" value="Master II">
                                        <span class="niveau-badge">Master 2</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-file-alt"></i> Fichier à envoyer
                                </label>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="cours-   " name="fichier" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp3,.mp4,.avi,.mov" required>
                                    <label for="cours-fichier" class="file-label">
                                        <div class="file-icon-text">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>cliquez pour sélectionner</span>
                                        </div>
                                        <span class="file-format-info">Formats acceptés : PDF, DOC, PPT, Images, MP3, MP4</span>
                                    </label>
                                    <div id="file-details" class="file-details">
                                        <div class="file-preview">
                                            <i class="fas fa-file-alt file-icon" id="file-icon"></i>
                                            <div class="file-info">
                                                <span class="file-name" id="display-filename">Aucun fichier sélectionné</span>
                                                <span class="file-size" id="display-filesize"></span>
                                            </div>
                                            <button type="button" class="file-clear" id="file-clear-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" id="upload-cours">
                                <i class="fas fa-paper-plane"></i> Publier
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Activités récentes -->
            <section class="activities-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i> Activités récentes
                    </h2>
                    <button class="btn btn-icon" id="refresh-activities">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="activities-timeline" id="activities-list">
                    <?php if (empty($activites)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Aucune activité récente</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activites as $activite): ?>
                        <div class="activity-item">
                            <div class="activity-marker <?= $activite['type'] === 'video' ? 'video' : 'upload' ?>">
                                <i class="fas fa-<?= $activite['type'] === 'video' ? 'video' : 'file-upload' ?>"></i>
                            </div>
                            <div class="activity-card">
                                <div class="activity-header">
                                    <span class="activity-type <?= $activite['type'] === 'video' ? 'video' : 'upload' ?>">
                                        <?= $activite['type'] === 'video' ? 'Visioconférence' : 'Publication' ?>
                                    </span>
                                    <span class="activity-time">
                                        <i class="far fa-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($activite['date_creation'])) ?>
                                    </span>
                                </div>
                                <div class="activity-content">
                                    <p><?= htmlspecialchars($activite['description']) ?></p>
                                    <?php if ($activite['type'] === 'file'): ?>
                                    <div class="activity-file">
                                        <i class="fas fa-paperclip"></i>
                                        <span><?= htmlspecialchars($activite['fichier_nom']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="../js/dashboard_enseignant.js"></script>
    <script>
        // Gestion de l'affichage du nom du fichier sélectionné
        document.getElementById('cours-fichier').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('display-filename').textContent = file.name;
                document.getElementById('display-filesize').textContent = formatFileSize(file.size);
                document.getElementById('file-details').style.display = 'block';
                
                // Changer l'icône selon le type de fichier
                const icon = document.getElementById('file-icon');
                if (file.type.includes('image')) {
                    icon.className = 'fas fa-file-image file-icon';
                } else if (file.type.includes('pdf')) {
                    icon.className = 'fas fa-file-pdf file-icon';
                } else if (file.type.includes('word')) {
                    icon.className = 'fas fa-file-word file-icon';
                } else if (file.type.includes('powerpoint')) {
                    icon.className = 'fas fa-file-powerpoint file-icon';
                }
            }
        });

        // Suite du script dans dashboard_enseignant.php
document.getElementById('cours-fichier').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('display-filename').textContent = file.name;
        document.getElementById('display-filesize').textContent = formatFileSize(file.size);
        document.getElementById('file-details').style.display = 'block';
        
        // Changer l'icône selon l'extension du fichier (plus robuste que le type MIME)
        const icon = document.getElementById('file-icon');
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        // Réinitialiser les classes
        icon.className = 'fas file-icon';
        
        // Déterminer l'icône en fonction de l'extension
        switch(fileExtension) {
            case 'pdf':
                icon.classList.add('fa-file-pdf');
                icon.style.color = '#e74c3c'; // Rouge pour PDF
                break;
            case 'doc':
            case 'docx':
                icon.classList.add('fa-file-word');
                icon.style.color = '#2c3e50'; // Bleu foncé pour Word
                break;
            case 'ppt':
            case 'pptx':
                icon.classList.add('fa-file-powerpoint');
                icon.style.color = '#e67e22'; // Orange pour PowerPoint
                break;
            case 'xls':
            case 'xlsx':
                icon.classList.add('fa-file-excel');
                icon.style.color = '#27ae60'; // Vert pour Excel
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                icon.classList.add('fa-file-image');
                icon.style.color = '#9b59b6'; // Violet pour images
                break;
            case 'mp3':
            case 'wav':
                icon.classList.add('fa-file-audio');
                icon.style.color = '#3498db'; // Bleu pour audio
                break;
            case 'mp4':
            case 'avi':
            case 'mov':
                icon.classList.add('fa-file-video');
                icon.style.color = '#e74c3c'; // Rouge pour vidéo
                break;
            case 'zip':
            case 'rar':
            case '7z':
                icon.classList.add('fa-file-archive');
                icon.style.color = '#f39c12'; // Jaune pour archives
                break;
            default:
                icon.classList.add('fa-file-alt');
                icon.style.color = '#7f8c8d'; // Gris par défaut
        }
    }
});

// Fonction pour formater la taille du fichier
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Bouton pour effacer la sélection
document.getElementById('file-clear-btn').addEventListener('click', function() {
    document.getElementById('cours-fichier').value = '';
    document.getElementById('display-filename').textContent = 'Aucun fichier sélectionné';
    document.getElementById('display-filesize').textContent = '';
    document.getElementById('file-details').style.display = 'none';
    
    // Réinitialiser l'icône
    const icon = document.getElementById('file-icon');
    icon.className = 'fas fa-file-alt file-icon';
    icon.style.color = '';
});

// Validation du formulaire avant soumission
document.getElementById('upload-form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('cours-fichier');
    const titreInput = document.getElementById('cours-titre');
    const niveauSelected = document.querySelector('input[name="niveau"]:checked');
    
    // Vérifier que tous les champs sont remplis
    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Veuillez sélectionner un fichier à uploader');
        return;
    }
    
    if (!titreInput.value.trim()) {
        e.preventDefault();
        alert('Veuillez entrer un titre pour le cours');
        return;
    }
    
    if (!niveauSelected) {
        e.preventDefault();
        alert('Veuillez sélectionner un niveau destinataire');
        return;
    }
    
    // Vérifier la taille du fichier (max 20MB)
    const maxSize = 20 * 1024 * 1024; // 20MB
    if (fileInput.files[0].size > maxSize) {
        e.preventDefault();
        alert('Le fichier est trop volumineux (max 20MB)');
        return;
    }
    
    // Afficher un indicateur de chargement
    const submitBtn = document.getElementById('upload-cours');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication en cours...';
    submitBtn.disabled = true;
});

// Gestion du drag and drop
const fileUploadWrapper = document.querySelector('.file-upload-wrapper');
const fileLabel = document.querySelector('.file-label');

fileUploadWrapper.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileLabel.classList.add('dragover');
});

fileUploadWrapper.addEventListener('dragleave', () => {
    fileLabel.classList.remove('dragover');
});

fileUploadWrapper.addEventListener('drop', (e) => {
    e.preventDefault();
    fileLabel.classList.remove('dragover');
    
    if (e.dataTransfer.files.length) {
        document.getElementById('cours-fichier').files = e.dataTransfer.files;
        // Déclencher manuellement l'événement change
        const event = new Event('change');
        document.getElementById('cours-fichier').dispatchEvent(event);
    }
});

// Mise à jour dynamique du compteur de cours
function updateCourseCount() {
    fetch('get_course_count.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.stats-courses .stat-value').textContent = data.count;
        })
        .catch(error => console.error('Erreur:', error));
}

// Actualiser toutes les 30 secondes
setInterval(updateCourseCount, 30000);

// Initialisation
updateCourseCount();
