<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Vérifier que les données nécessaires ont été passées
if (!isset($_POST['user_id']) || !isset($_POST['action'])) {
    header("Location: manage_user.php");
    exit();
}

$userId = $_POST['user_id'];
$action = $_POST['action'];

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($action === 'approve') {
        // Valider l'utilisateur
        $stmt = $conn->prepare("UPDATE users SET is_approved = TRUE WHERE id = :id");
    } elseif ($action === 'reject') {
        // Rejeter l'utilisateur
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    }

    $stmt->bindParam(':id', $userId);
    $stmt->execute();

    header("Location: manage_user.php");
    exit();

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
