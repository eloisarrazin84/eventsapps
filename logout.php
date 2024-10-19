<?php
session_start();
session_unset();  // Libérer toutes les variables de session
session_destroy();  // Détruire la session

// Rediriger l'utilisateur vers la page de connexion
header("Location: login.php");
exit();
?>
