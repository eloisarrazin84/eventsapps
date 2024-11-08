<?php
session_start();

// Vérifier si l'utilisateur est déjà connecté et s'il est administrateur
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    // Rediriger directement vers la procédure de retour si l'utilisateur est admin
    header("Location: return_procedure.php?bag_id=" . urlencode($_GET['bag_id']));
    exit();
} else {
    // Stocker l'URL de redirection pour la rediriger après connexion
    $_SESSION['redirect_to'] = "return_procedure.php?bag_id=" . urlencode($_GET['bag_id']);
    
    // Rediriger vers la page de connexion avec une URL de redirection
    header("Location: https://event.outdoorsecours.fr/login.php?redirect=return_procedure.php&bag_id=" . urlencode($_GET['bag_id']));
exit();

}
?>
