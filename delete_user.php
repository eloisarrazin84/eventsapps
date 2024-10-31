<?php
session_start();

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

        // Démarrer une transaction pour garantir que toutes les suppressions sont faites ensemble
        $conn->beginTransaction();

        // Supprimer les enregistrements dans `notifications` associés à l'utilisateur
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Supprimer les enregistrements dans `user_event_data` associés à l'utilisateur
        $stmt = $conn->prepare("DELETE FROM user_event_data WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Supprimer l'utilisateur dans `users`
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        // Valider la transaction
        $conn->commit();

        // Redirige vers la page de gestion des utilisateurs après la suppression
        header("Location: manage_users.php");
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur, annule la transaction et affiche le message d'erreur
        $conn->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
} else {
    // Si aucun ID n'est passé, un message d'erreur est affiché.
    echo "Aucun utilisateur sélectionné.";
    exit();
}
?>
