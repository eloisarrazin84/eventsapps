<?php
session_start();  // Démarre une session pour gérer les utilisateurs connectés.

// Vérifie si l'utilisateur est un administrateur. Si ce n'est pas le cas, il est redirigé vers la page de connexion.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

// Vérifie si un ID d'utilisateur est passé dans l'URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];  // Récupère l'ID de l'utilisateur à supprimer.

    try {
        // Création d'une connexion à la base de données avec PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prépare la requête SQL pour supprimer l'utilisateur avec l'ID spécifié
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);  // Lie l'ID de l'utilisateur à la requête.
        $stmt->execute();  // Exécute la requête de suppression.

        // Redirige vers la page de gestion des utilisateurs après la suppression
        header("Location: manage_users.php");
        exit();
    } catch (PDOException $e) {
        // Gère les erreurs de connexion ou d'exécution de la requête
        echo "Erreur : " . $e->getMessage();
    }
} else {
    // Si aucun ID n'est passé, un message d'erreur est affiché.
    echo "Aucun utilisateur sélectionné.";
    exit();
}
?>
