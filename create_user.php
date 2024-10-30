<?php
session_start();

// Vérification que l'utilisateur est bien un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Informations de connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

// Gestion du formulaire d'ajout d'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $approved = isset($_POST['approved']) ? 1 : 0;
    $applications = isset($_POST['applications']) ? $_POST['applications'] : []; // Applications assignées

    // Hachage du mot de passe pour plus de sécurité
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Connexion à la base de données avec PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", 'root', 'Lipton2019!');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insertion du nouvel utilisateur avec mot de passe haché
        $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, role, approved) VALUES (:username, :password, :first_name, :last_name, :email, :role, :approved)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':approved', $approved);
        $stmt->execute();

        // Récupérer l'ID de l'utilisateur nouvellement créé
        $userId = $conn->lastInsertId();

        // Assigner des applications à l'utilisateur
        foreach ($applications as $app) {
            $stmt = $conn->prepare("INSERT INTO user_applications (user_id, application_name) VALUES (:user_id, :application_name)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':application_name', $app);
            $stmt->execute();
        }

        header("Location: manage_users.php");
        exit();

    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="mt-5">Ajouter un utilisateur</h1>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user">Utilisateur standard</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>
        <div class="form-group">
            <label for="approved">Approuvé</label>
            <input type="checkbox" id="approved" name="approved">
        </div>
        
        <div class="form-group">
            <label for="applications">Applications assignées</label>
            <select multiple class="form-control" id="applications" name="applications[]">
                <option value="gestion_pharmacie">Gestion Pharmacie</option>
                <option value="notes_de_frais">Notes de Frais</option>
                <option value="autre_application">Autre Application</option>
            </select>
            <small class="form-text text-muted">Sélectionnez les applications auxquelles l'utilisateur aura accès.</small>
        </div>

        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="manage_users.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>
