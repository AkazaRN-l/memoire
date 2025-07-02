<?php
session_start();

// Détruire toutes les données de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, effacez également
// le cookie de session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalement, détruire la session
session_destroy();

// Redirection vers Google si le paramètre est présent
if (isset($_GET['redirect']) && $_GET['redirect'] === 'google') {
    header("Location: https://www.google.com");
    exit;
}

// Redirection par défaut vers la page de login
header("Location: login_admin.php");
exit;
?>