<?php
session_start();

// Vérification que l'utilisateur est bien administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";  
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $prenom = $_POST['first_name'];
        $nom = $_POST['last_name'];
        $role = $_POST['role'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $approved = isset($_POST['approved']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO users (username, email, first_name, last_name, role, password, approved) 
                                VALUES (:username, :email, :first_name, :last_name, :role, :password, :approved)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':first_name', first_name);
        $stmt->bindParam(':last_name', last_name);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':approved', $approved);
        $stmt->execute();

        header("Location: manage_users.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un utilisateur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="mt-5">Créer un nouvel utilisateur</h1>
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
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user">Utilisateur</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="approved" name="approved">
            <label class="form-check-label" for="approved">Approuvé</label>
        </div>
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
        <a href="manage_users.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
