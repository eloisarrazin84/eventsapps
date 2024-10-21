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
    $email = $_POST['email'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $approved = isset($_POST['approved']) ? 1 : 0;

    // Hachage du mot de passe pour plus de sécurité
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Connexion à la base de données avec PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insertion du nouvel utilisateur avec les informations complètes
        $stmt = $conn->prepare("INSERT INTO users (username, email, prenom, nom, password, role, approved) VALUES (:username, :email, :prenom, :nom, :password, :role, :approved)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':approved', $approved);
        $stmt->execute();

        // Redirection vers la gestion des utilisateurs après l'ajout
        header("Location: manage_users.php");
        exit();

    } catch(PDOException $e) {
        // Affichage de l'erreur en cas de problème de base de données
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

    <!-- Formulaire d'ajout d'utilisateur -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="prenom">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" required>
        </div>
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user">Utilisateur standard</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="approved" name="approved">
            <label class="form-check-label" for="approved">Approuvé</label>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="manage_users.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>
