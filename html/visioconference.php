<?php
session_start();
require_once('config.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login_etudiant.php");
    exit();
}

// Récupérer le dernier appel pour le niveau de l'étudiant
$sql = "SELECT * FROM visio_sessions WHERE niveau = ? AND statut = 'en_cours' ORDER BY date_appel DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['niveau']);
$stmt->execute();
$result = $stmt->get_result();
$appel = $result->fetch_assoc();
$stmt->close();

if (!$appel) {
  echo '
  <div style="
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 70vh;
      background-color: #f9f9f9;
      padding: 20px;
  ">
      <div style="
          text-align: center;
          max-width: 500px;
          padding: 40px;
          background: white;
          border-radius: 10px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      ">
          <div style="
              font-size: 60px;
              margin-bottom: 20px;
              color: #6c757d;
              animation: bounce 2s infinite;
          ">📞</div>
          <style>
              @keyframes bounce {
                  0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
                  40% {transform: translateY(-20px);}
                  60% {transform: translateY(-10px);}
              }
          </style>
          <h2 style="color: #343a40; margin-bottom: 15px;">Aucun appel en cours</h2>
          <p style="color: #6c757d; margin-bottom: 10px; line-height: 1.6;">
              Il n\'y a actuellement aucun appel de visioconférence pour votre niveau ('.htmlspecialchars($_SESSION['niveau']).').
          </p>
          <p style="color: #6c757d; margin-bottom: 20px; line-height: 1.6;">
              Veuillez vérifier ultérieurement ou contacter votre enseignant.
          </p>
          <a href="dashboard_etudiant.php" style="
              display: inline-block;
              padding: 10px 25px;
              background-color: #4e73df;
              color: white;
              text-decoration: none;
              border-radius: 5px;
              transition: background-color 0.3s;
          " onmouseover="this.style.backgroundColor=\'#3a5bd9\'" 
             onmouseout="this.style.backgroundColor=\'#4e73df\'">
              Retour au tableau de bord
          </a>
      </div>
  </div>
  ';
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rejoindre la visioconférence</title>
    <style>
        .call-container {
            text-align: center;
            margin-top: 50px;
        }
        .call-actions {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
        }
        .btn-join {
            background: #4CAF50;
            color: white;
        }
        .btn-decline {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="call-container">
        <h2>Appel entrant - <?= htmlspecialchars($appel['niveau']) ?></h2>
        <p>Enseignant: <?= htmlspecialchars($appel['nom'].' '.$appel['prenom']) ?></p>
        
        <div class="call-actions">
            <button class="btn btn-join" onclick="window.open('<?= $appel['lien'] ?>', '_blank')">
                Rejoindre l'appel
            </button>
            <button class="btn btn-decline" onclick="window.location.href='dashboard_etudiant.php'">
                Raccrocher
            </button>
        </div>
    </div>
</body>
</html>