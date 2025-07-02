<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once('config.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login_etudiant.php");
    exit();
}



$active_year_result = mysqli_query($conn, "SELECT annee FROM annee_academique WHERE est_active = 1 LIMIT 1");
$annee_active = mysqli_fetch_assoc($active_year_result)['annee'] ?? 'Aucune';


if (!isset($conn) || $conn->connect_error) {
    die("<div style='padding:20px;background:#fff3e0;border:2px solid #ff9800;'>
        La connexion à la base de données a échoué. Contactez l'administrateur.
    </div>");
}

$user_id = $_SESSION['user_id'];
$niveau = $_SESSION["niveau"] ?? '';
$nom = $_SESSION["nom"] ?? '';
$prenom = $_SESSION["prenom"] ?? '';
$matricule = $_SESSION["matricule"] ?? '';
$photo = $_SESSION["photo"] ?? '';

// Récupérer l'année académique active
$current_year_result = $conn->query("SELECT id FROM annee_academique WHERE est_active = TRUE LIMIT 1");
$current_year_id = ($current_year_result && $current_year_result->num_rows > 0) ? $current_year_result->fetch_assoc()['id'] : null;

// Nouveaux cours par matière
$sql_nouveaux = "SELECT m.id, COUNT(c.id) as nouveaux 
                FROM matieres m
                LEFT JOIN cours c ON m.id = c.matiere_id
                LEFT JOIN cours_vus cv ON (c.id = cv.cours_id AND cv.etudiant_id = ?)
                WHERE m.niveau = ? 
                AND c.id IS NOT NULL
                AND (cv.id IS NULL OR c.date_envoi > cv.vu_date)
                GROUP BY m.id";
$stmt_nouveaux = $conn->prepare($sql_nouveaux);
$stmt_nouveaux->bind_param("is", $user_id, $niveau);
$stmt_nouveaux->execute();
$result_nouveaux = $stmt_nouveaux->get_result();
$nouveaux_cours = [];
while ($row = $result_nouveaux->fetch_assoc()) {
    $nouveaux_cours[$row['id']] = $row['nouveaux'];
}

// Liste des matières
$sql_matieres = "SELECT m.id, m.nom FROM matieres m WHERE m.niveau = ? ORDER BY m.nom";
$stmt_matieres = $conn->prepare($sql_matieres);
$stmt_matieres->bind_param("s", $niveau);
$stmt_matieres->execute();
$result_matieres = $stmt_matieres->get_result();



$query = $conn->prepare("SELECT lien FROM visio_sessions WHERE niveau = ? ORDER BY id DESC LIMIT 1");
$query->bind_param("s", $niveau);
$query->execute();
$result = $query->get_result();

if ($result && $visio = $result->fetch_assoc()) {
    $salle = htmlspecialchars($visio['lien']); // nom de la salle Jitsi

    echo "<div class='visio-box'>";
    echo "<p>Une visioconférence est en cours pour votre niveau ($niveau).</p>";
    echo "<div id='jitsi-meet'></div>"; // conteneur Jitsi
    echo "</div>";
    ?>
    <script src='https://meet.jit.si/external_api.js'></script>
    <script>
        const domain = "meet.jit.si";
        const options = {
            roomName: "<?= $salle ?>",
            width: "100%",
            height: 600,
            parentNode: document.querySelector("#jitsi-meet"),
            configOverwrite: {},
            interfaceConfigOverwrite: {}
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
    <?php
}



// Annonces
$sql_info = "SELECT * FROM informations WHERE niveau = ? ORDER BY date_envoi DESC";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("s", $niveau);
$stmt_info->execute();
$result_info = $stmt_info->get_result();
$stmt_info->close();

// Cours de l’année active
$sql_cours = "SELECT c.id, c.titre, c.description, c.fichier_nom, c.date_envoi, e.nom, e.prenom 
              FROM cours c
              JOIN enseignants e ON c.enseignant_id = e.id
              LEFT JOIN archives_cours a ON c.id = a.cours_id
              WHERE c.niveau = ? AND (a.id IS NULL OR a.annee_id = ?)
              ORDER BY c.date_envoi DESC";
$stmt_cours = $conn->prepare($sql_cours);
$stmt_cours->bind_param("si", $niveau, $current_year_id);
$stmt_cours->execute();
$result_cours = $stmt_cours->get_result();
$stmt_cours->close();

// Appel en cours
$sql_appel = "SELECT a.*, e.nom, e.prenom 
              FROM visio_sessions a
              JOIN enseignants e ON a.enseignant_id = e.id
              WHERE a.niveau = ? AND a.statut = 'en_cours'
              ORDER BY a.date_appel DESC LIMIT 1";
$stmt_appel = $conn->prepare($sql_appel);
$stmt_appel->bind_param("s", $niveau);
$stmt_appel->execute();
$result_appel = $stmt_appel->get_result();
$appel_en_cours = $result_appel->fetch_assoc();
$stmt_appel->close();
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard Étudiant - <?= htmlspecialchars($niveau) ?></title>
    <link rel="stylesheet" href="../css/dashboard_etudiant.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body>
<div class="dashboard-container">

<aside class="sidebar">
    <h2>🎓 Étudiant</h2>
    <div class="active-year">
    <?= htmlspecialchars($annee_active) ?>
</div>
    <nav>
        <ul>
            <?php
            $sql_matieres = "SELECT * FROM matieres WHERE niveau = ? ORDER BY nom";
            $stmt = $conn->prepare($sql_matieres);
            $stmt->bind_param("s", $niveau);
            $stmt->execute();
            $result_matieres = $stmt->get_result();
            
            while ($matiere = $result_matieres->fetch_assoc()) {
                $badge = isset($nouveaux_cours[$matiere['id']]) && $nouveaux_cours[$matiere['id']] > 0 
                       ? '<span class="nouveau-cours-badge">'.$nouveaux_cours[$matiere['id']].'</span>' 
                       : '';
                
                echo '<li>
                        <a href="cours_matiere.php?id='.$matiere['id'].'" class="matiere-item">
                            <span class="matiere-icon">📖</span>
                            <span class="matiere-nom">'.htmlspecialchars($matiere['nom']).'</span>
                            '.$badge.'
                        </a>
                      </li>';
            }
            ?>
           <a href="liste_matieres.php" class="btn-noble">Forum de discussion</a>
            <li>
                <a href="visioconference.php" class="visio-link" id="visioLink">
                    <span class="matiere-icon">🎥</span>
                    <span class="matiere-nom">Visioconférence</span>
                    <?php if($appel_en_cours): ?>
                        <span class="notification-badge">Nouvel appel!</span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="logout_etudiant.php" class="matiere-item">
                    <span class="matiere-icon">🚪</span>
                    <span class="matiere-nom">Déconnexion</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

    <!-- Contenu principal -->
    <main class="dashboard-main">
        <header>
            <h1>📚 Espace Étudiant - <?= htmlspecialchars($niveau) ?></h1>
        </header>

        <!-- Profil étudiant -->
        <section class="profile-card">
            <img src="<?= htmlspecialchars($photo) ?>" class="profile-photo">
            <h2><?= htmlspecialchars($nom . " " . $prenom) ?></h2></br>
             <p>Matricule : <?= htmlspecialchars($matricule) ?></p>
        </section>

        <!-- Annonces -->
        <section class="info-section">
            <h2>📢 Annonces du Responsable de Mention</h2>
            <?php if ($result_info->num_rows > 0): ?>
                <?php while ($info = $result_info->fetch_assoc()) : ?>
                    <div class="info-card">
                        <h3><?= htmlspecialchars($info["titre"]) ?></h3>
                        <p><?= nl2br(htmlspecialchars($info["contenu"])) ?></p>
                        <small> Envoyé le : <?= htmlspecialchars($info["date_envoi"]) ?></small>
                    </div>

                    <?php if (!empty($info['fichier'])): ?>
    <a href="telecharger_fichier_chef.php?fichier=<?= urlencode($info['fichier']) ?>" class="btn-telecharger">Télécharger le fichier</a>
<?php else: ?>
    <span class="no-file">Aucun fichier joint</span>
<?php endif; ?>

                    <?php endwhile; ?>
            <?php else: ?>
                <p>Aucune annonce pour le moment.</p>
            <?php endif; ?>
        </section>



   <!-- Cours -->
<section class="learning-materials">
    <div class="section-header">
        <h2><i class="icon-book"></i> Ressources pédagogiques</h2>
    </div>

    <?php if ($result_cours->num_rows > 0): ?>
        <div class="materials-grid">
            <?php while ($c = $result_cours->fetch_assoc()): ?>
                <article class="material-card">
                    <div class="material-header">
                        <div class="material-type pdf">
                            <i class="icon-file-text"></i>
                        </div>
                        <div class="material-meta">
                            <span class="material-date"><?= date('d/m/Y', strtotime($c['date_envoi'])) ?></span>
                            <span class="material-author"><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></span>
                        </div>
                    </div>
                    
                    <h3 class="material-title"><?= htmlspecialchars($c['titre']) ?></h3>
                    
                    <?php if (!empty($c['description'])): ?>
                        <p class="material-description">
                            <?= nl2br(htmlspecialchars(substr($c['description'], 0, 150).(strlen($c['description']) > 150 ? '...' : ''))) ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="material-footer">
                        <span class="file-info">
                            <i class="icon-download-cloud"></i>
                            <?= htmlspecialchars($c['fichier_nom']) ?>
                        </span>
                        <a href="telecharger.php?id=<?= intval($c['id']) ?>" class="download-action">
                            <i class="icon-arrow-down"></i> Télécharger
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="icon-folder-open"></i>
            <p>Aucune ressource disponible actuellement</p>
        </div>
    <?php endif; ?>
</section>
</main>
</div>

<script>
document.querySelectorAll('.matiere-item').forEach(item => {
    item.addEventListener('click', function(e) {
        const matiereLink = this.getAttribute('href');
        if (matiereLink.includes('cours_matiere.php')) {
            const matiereId = matiereLink.split('id=')[1];
            
            if (matiereId && !isNaN(matiereId)) {
                fetch('marquer_vus.php?matiere_id=' + matiereId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        etudiant_id: <?= $_SESSION['user_id'] ?>
                    })
                }).then(() => {
                    const badge = this.querySelector('.nouveau-cours-badge');
                    if (badge) badge.remove();
                });
            }
        }
    });
});


let peerConnection;
const config = {
    iceServers: [
        { urls: "stun:stun.l.google.com:19302" }
    ]
};

// Rejoindre la visioconférence
function joinVideoConference(niveau) {
    const wsUrl = "ws://votre-serveur:8080";
    const socket = new WebSocket(wsUrl);
    
    socket.onopen = () => {
        // Rejoindre la salle en tant qu'étudiant
        socket.send(JSON.stringify({
            type: 'join',
            niveau: niveau,
            role: 'etudiant'
        }));
        
        // Initialiser WebRTC
        initWebRTC(socket, niveau);
    };
    
    socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        if (data.type === 'signal') {
            if (data.sdp) {
                peerConnection.setRemoteDescription(new RTCSessionDescription(data.sdp))
                    .then(() => {
                        if (data.sdp.type === 'offer') {
                            return peerConnection.createAnswer();
                        }
                    })
                    .then(answer => {
                        return peerConnection.setLocalDescription(answer);
                    })
                    .then(() => {
                        socket.send(JSON.stringify({
                            type: 'signal',
                            target: data.from,
                            sdp: peerConnection.localDescription
                        }));
                    });
            } else if (data.candidate) {
                peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        }
    };
}

function initWebRTC(socket, niveau) {
    peerConnection = new RTCPeerConnection(config);
    
    // Configuration des flux média
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            document.getElementById('localVideo').srcObject = stream;
            stream.getTracks().forEach(track => {
                peerConnection.addTrack(track, stream);
            });
        });
    
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            socket.send(JSON.stringify({
                type: 'broadcast',
                niveau: niveau,
                candidate: event.candidate
            }));
        }
    };
    
    peerConnection.ontrack = (event) => {
        const remoteVideo = document.getElementById('remoteVideo');
        if (!remoteVideo.srcObject) {
            remoteVideo.srcObject = event.streams[0];
        }
    };
}

// Rejoindre automatiquement quand il y a un appel en cours
<?php if ($appel_en_cours): ?>
document.addEventListener('DOMContentLoaded', function() {
    joinVideoConference('<?= $niveau ?>');
});
<?php endif; ?>
</script>
</body>
</html>