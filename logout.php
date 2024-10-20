<?php
session_start();

// Vérifiez si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    session_unset();  // Libérer les variables de session
    session_destroy();  // Détruire la session
    header("Location: login.php?message=deconnexion");  // Redirection avec message
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
