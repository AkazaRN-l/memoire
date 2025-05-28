<?php
session_start();
if (!isset($_SESSION['jitsi_link'])) {
    die("Lien de visioconférence non trouvé");
}

$link = $_SESSION['jitsi_link'];
unset($_SESSION['jitsi_link']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirection vers Jitsi</title>
    <script>
    window.onload = function() {
        // Cette ouverture ne sera pas bloquée
        window.open("<?= htmlspecialchars($link) ?>", '_blank');
        
        // Retour à la page précédente après un court délai
        setTimeout(function() {
            window.location.href = "dashboard_enseignant.php";
        }, 500);
    };
    </script>
</head>
<body>
    <p>Redirection vers la visioconférence en cours...</p>
</body>
</html>