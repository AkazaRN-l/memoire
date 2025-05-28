<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['enseignants'])) {
    header('Location: login_enseignant.php');
    exit();
}


// Récupérer l’année académique active avec PDO
$stmt = $pdo->prepare("SELECT annee FROM annee_academique WHERE est_active = 1 LIMIT 1");
$stmt->execute();
$annee_active = $stmt->fetchColumn() ?: 'Aucune';

// Données de session enseignant
$enseignant = $_SESSION['enseignants'];
$_SESSION['enseignant_id'] = $enseignant['id'];

// Niveau par défaut
$niveau_par_defaut = 'Licence I';
$niveau_selectionne = $_POST['niveau'] ?? $niveau_par_defaut;

// Récupérer l'année académique active
try {
    $stmtAnnee = $pdo->prepare("SELECT id FROM annee_academique WHERE est_active = 1 LIMIT 1");
    $stmtAnnee->execute();
    $anneeActive = $stmtAnnee->fetch();

    if (!$anneeActive) {
        die("Aucune année académique active.");
    }
    $annee_id = $anneeActive['id'];
} catch (PDOException $e) {
    die("Erreur récupération année : " . $e->getMessage());
}

// Démarrer une visioconférence
if (isset($_POST['demarrer_visio']) && !empty($_POST['niveau'])) {
    $niveau = $_POST['niveau'];
    $roomName = "visio_sessions" . strtolower(str_replace(' ', '_', $niveau));
    $link = "https://meet.jit.si/" . $roomName;

    try {
        if (empty($_SESSION['enseignant_id'])) {
            throw new Exception("ID enseignant non trouvé en session");
        }

        // Vérifie que la session pour l'année existe
        if (empty($annee_id)) {
            throw new Exception("ID de l'année non trouvé");
        }

        $stmt = $pdo->prepare("INSERT INTO visio_sessions (niveau, lien, statut, enseignant_id, annee_id) 
                              VALUES (:niveau, :lien, 'en_cours', :enseignant_id, :annee_id)");
        $stmt->execute([
            ':niveau' => $niveau,
            ':lien' => $link,
            ':enseignant_id' => $_SESSION['enseignant_id'],
            ':annee_id' => $annee_id
        ]);

        // Redirection vers le lien de la visioconférence
        header("Location: $link");
        exit();
    } catch (PDOException $e) {
        die("Erreur création appel: " . $e->getMessage());
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

// Récupérer les visios en cours pour l'année active
$appels_en_cours = [];
try {
    $stmt = $pdo->prepare("SELECT id, niveau, date_appel 
                          FROM visio_sessions 
                          WHERE enseignant_id = :enseignant_id 
                          AND statut = 'en_cours' 
                          AND annee_id = :annee_id
                          ORDER BY date_appel DESC");
    $stmt->execute([
        ':enseignant_id' => $_SESSION['enseignant_id'],
        ':annee_id' => $annee_id
    ]);
    $appels_en_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $appels_en_cours = [];
}

// Mettre à jour le statut de la visioconférence
if (isset($_POST['arreter_visio']) && isset($_POST['visio_id'])) {
    $visio_id = $_POST['visio_id'];
    
    try {
        // Assurer que le statut est correct
        $statut = 'termine';  // Nous supposons ici que nous voulons changer le statut à "terminé"
        
        $stmt = $pdo->prepare("UPDATE visio_sessions SET statut = :statut WHERE id = :visio_id");
        $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        $stmt->bindParam(':visio_id', $visio_id, PDO::PARAM_INT);

        // Exécuter la mise à jour
        if ($stmt->execute()) {
            echo "Statut de la visioconférence mis à jour avec succès.";
        } else {
            echo "Erreur lors de la mise à jour du statut.";
            print_r($stmt->errorInfo()); // Afficher l'erreur SQL si ça échoue
        }
    } catch (PDOException $e) {
        die("Erreur mise à jour statut visio: " . $e->getMessage());
    }
}




// Récupération des activités de l’enseignant
$activites = [];
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

// Statistiques placeholders (à personnaliser si besoin)
$stats = [
    'cours' => 0,
    'eleves' => 0,
    'conferences' => 0
];

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= htmlspecialchars($enseignant['prenom']) ?></title>
    <link rel="stylesheet" href="../css/dashboard_enseignant.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                <div class="active-year">
    <?= htmlspecialchars($annee_active) ?>
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
                <!-- Card Visioconférence -->
<div class="card card-video">
    <div class="card-header">
        <div class="card-icon">
            <i class="fas fa-video"></i>
        </div>
        <h2 class="card-title">Nouvelle Visioconférence</h2>
    </div>
    
    <div class="card-body">
        <form id="video-form" action="" method="POST">                     
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-layer-group"></i> Choisir un niveau
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
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" name="demarrer_visio" id="start-video">
                    <i class="fas fa-play"></i> Démarrer
                </button>
            </div>
            <a href="forum_enseignant.php" class="btn-forum">
    Accéder au forum des étudiants
</a>
        </form>
        <?php if (!empty($appels_en_cours)) : ?>
    <div class="visio-stop-container">
        <form method="POST" action="">
            <input type="hidden" name="visio_id" value="<?= $appels_en_cours[0]['id'] ?>">
            <button type="submit" class="btn btn-danger" name="arreter_visio">
                <i class="fas fa-stop"></i> Arrêter la visioconférence en cours
            </button>
        </form>
    </div>
<?php endif; ?>

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
                <i class="fas fa-layer-group"></i> Description de cours <br>
                <textarea name="description" rows="10" class="description" cols="70" required></textarea>
            </div>

            <div class="form-group">
    <label class="form-label">
        <i class="fas fa-layer-group"></i> Niveau destinataire
    </label>
    <div class="niveaux-radio">
        <label class="niveau-option">
            <input type="radio" name="niveau" value="Licence I" required 
                   <?php if(!isset($_POST['niveau']) || (isset($_POST['niveau']) && $_POST['niveau'] === 'Licence I')) echo 'checked'; ?>>
            <span class="niveau-badge">Licence 1</span>
        </label>
        <label class="niveau-option">
            <input type="radio" name="niveau" value="Licence II"
                   <?php if(isset($_POST['niveau']) && $_POST['niveau'] === 'Licence II') echo 'checked'; ?>>
            <span class="niveau-badge">Licence 2</span>
        </label>
        <label class="niveau-option">
            <input type="radio" name="niveau" value="Licence III"
                   <?php if(isset($_POST['niveau']) && $_POST['niveau'] === 'Licence III') echo 'checked'; ?>>
            <span class="niveau-badge">Licence 3</span>
        </label>
        <label class="niveau-option">
            <input type="radio" name="niveau" value="Master I"
                   <?php if(isset($_POST['niveau']) && $_POST['niveau'] === 'Master I') echo 'checked'; ?>>
            <span class="niveau-badge">Master 1</span>
        </label>
        <label class="niveau-option">
            <input type="radio" name="niveau" value="Master II"
                   <?php if(isset($_POST['niveau']) && $_POST['niveau'] === 'Master II') echo 'checked'; ?>>
            <span class="niveau-badge">Master 2</span>
        </label>
    </div>
</div>

<div class="form-group">
    <label class="form-label">
        <i class="fas fa-book"></i> Matière
    </label>
    <select name="matiere_id" class="form-control" required id="select-matiere">
        <option value="">-- Sélectionner une matière --</option>
        <?php
        // Définir le niveau sélectionné
        $niveau_selectionne = isset($_POST['niveau']) ? $_POST['niveau'] : 'Licence I';
        
        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("SELECT id, nom FROM matieres WHERE niveau = ? ORDER BY nom");
                $stmt->execute([$niveau_selectionne]);
                
                while ($matiere = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = (isset($_POST['matiere_id']) && $_POST['matiere_id'] == $matiere['id']) ? 'selected' : '';
                    echo '<option value="'.htmlspecialchars($matiere['id']).'" '.$selected.'>'
                        .htmlspecialchars($matiere['nom'])
                        .'</option>';
                }
            } catch (PDOException $e) {
                echo '<option value="">Erreur de chargement</option>';
            }
        }
        ?>
    </select>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="niveau"]');
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const niveau = this.value;
            const select = document.getElementById('select-matiere');
            
            select.innerHTML = '<option value="">Chargement...</option>';
            
            fetch('get_matieres.php?niveau=' + encodeURIComponent(niveau))
                .then(response => response.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- Sélectionner une matière --</option>';
                    data.forEach(matiere => {
                        const option = new Option(matiere.nom, matiere.id);
                        select.add(option);
                    });
                })
                .catch(error => {
                    select.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        });
    });
});
</script>

                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-file-alt"></i> Fichier à envoyer
                                </label>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="cours-fichier" name="fichier" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp3,.mp4,.avi,.mov" required>
                                    <label for="cours-fichier" class="file-label">
                                        <div class="file-icon-text">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Glissez-déposez ou cliquez pour sélectionner</span>
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



            <section class="sent-courses-section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-book-open"></i> Cours envoyés
        </h2>
    </div>
    
    <div class="courses-list">
        <?php
        // Récupérer tous les cours envoyés par cet enseignant
        try {
            $stmt = $pdo->prepare("SELECT c.id, c.titre, c.description, c.fichier_nom, c.date_envoi, m.nom AS matiere_nom, m.niveau 
            FROM cours c
            JOIN matieres m ON c.matiere_id = m.id
            WHERE c.enseignant_id = ?
            ORDER BY c.date_envoi DESC");
            $stmt->execute([$enseignant['id']]);
            $coursEnvoyes = $stmt->fetchAll();
            
            if (count($coursEnvoyes) > 0): ?>
              <table class="courses-table">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Matière</th>
            <th>Niveau</th>
            <th>Fichier</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($coursEnvoyes as $cours): ?>
        <tr>
            <td><?= htmlspecialchars($cours['titre']) ?></td>
            <td><?= htmlspecialchars($cours['matiere_nom']) ?></td>
            <td><?= htmlspecialchars($cours['niveau']) ?></td>
            <td><?= htmlspecialchars($cours['fichier_nom']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($cours['date_envoi'])) ?></td>
            <td class="actions-cell">
            <a href="modifier_cours.php?id=<?= $cours['id'] ?>" class="action-btn edit-btn">
        <i class="fas fa-edit"></i>
        <span>Modifier</span>
    </a>
    
    <a href="supprimer_cours.php?id=<?= $cours['id'] ?>" class="action-btn delete-btn" 
       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')">
        <i class="fas fa-trash-alt"></i>
        <span>Supprimer</span>
    </a>
            <td class="actions-cell">

</td>
        <?php endforeach; ?>
    </tbody>
</table>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-book"></i>
                    <p>Aucun cours envoyé pour le moment</p>
                </div>
            <?php endif;
        } catch (PDOException $e) {
            echo '<div class="error-msg">Erreur de chargement des cours</div>';
        }
        ?>
    </div>
</section>





<script>
// Actualisation automatique
function refreshActivities() {
    fetch('get_activities.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('activities-list').innerHTML = html;
        });
}


// Rafraîchir quand on clique le bouton
document.getElementById('refresh-activities').addEventListener('click', refreshActivities);


</script>

<script>
// Mise à jour dynamique des matières quand le niveau change
document.querySelectorAll('input[name="niveau"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const niveau = this.value;
        fetch('get_matieres.php?niveau=' + encodeURIComponent(niveau))
            .then(response => response.json())
            .then(matieres => {
                const select = document.querySelector('select[name="matiere_id"]');
                select.innerHTML = '<option value="">-- Sélectionner une matière --</option>';
                
                matieres.forEach(matiere => {
                    const option = document.createElement('option');
                    option.value = matiere.id;
                    option.textContent = matiere.nom;
                    select.appendChild(option);
                });
            });
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('cours-fichier');
    const displayFilename = document.getElementById('display-filename');
    const displayFilesize = document.getElementById('display-filesize');
    const fileIcon = document.getElementById('file-icon');
    const fileDetails = document.getElementById('file-details');
    const clearBtn = document.getElementById('file-clear-btn');
    const uploadForm = document.getElementById('upload-form');

    // Affichage des détails du fichier
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            const file = this.files[0];
            displayFilename.textContent = file.name;
            displayFilesize.textContent = formatFileSize(file.size);
            fileDetails.style.display = 'block';
            
            // Changer l'icône selon le type de fichier
            updateFileIcon(fileIcon, file.name);
        }
    });

    // Bouton de suppression
    clearBtn.addEventListener('click', function() {
        fileInput.value = '';
        fileDetails.style.display = 'none';
    });

    // Validation du formulaire
    uploadForm.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        if (!file) {
            e.preventDefault();
            alert('Veuillez sélectionner un fichier');
            return;
        }

        // Désactiver le bouton pendant l'upload
        const submitBtn = document.getElementById('upload-cours');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication en cours...';
    });

    // Fonction pour formater la taille
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Fonction pour mettre à jour l'icône
    function updateFileIcon(iconElement, filename) {
        const ext = filename.split('.').pop().toLowerCase();
        iconElement.className = 'fas file-icon';
        
        const icons = {
            pdf: 'fa-file-pdf',
            doc: 'fa-file-word',
            docx: 'fa-file-word',
            ppt: 'fa-file-powerpoint',
            pptx: 'fa-file-powerpoint',
            jpg: 'fa-file-image',
            jpeg: 'fa-file-image',
            png: 'fa-file-image',
            gif: 'fa-file-image',
            mp3: 'fa-file-audio',
            mp4: 'fa-file-video'
        };
        
        iconElement.classList.add(icons[ext] || 'fa-file-alt');
    }
});
</script>


<script>
// Mise à jour dynamique des matières
document.querySelectorAll('input[name="niveau"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const niveau = this.value;
        fetch('get_matieres.php?niveau=' + encodeURIComponent(niveau))
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(matieres => {
                const select = document.getElementById('select-matiere');
                select.innerHTML = '<option value="">-- Sélectionner une matière --</option>';
                
                matieres.forEach(matiere => {
                    const option = new Option(matiere.nom, matiere.id);
                    select.add(option);
                });
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Impossible de charger les matières');
            });
    });
});
</script>


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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



updateCourseCount();
