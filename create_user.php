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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 30px;
            text-align: center;
        }
        .btn {
            border-radius: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        /* Styles pour la responsivité */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }
            .btn {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<!-- Inclusion du menu -->
<?php include 'menu.php'; ?>

<div class="container">
    <h1>Ajouter un utilisateur</h1>

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
            <label>Applications assignées</label><br>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="gestion_pharmacie" name="applications[]" value="gestion_pharmacie">
                <label class="form-check-label" for="gestion_pharmacie">Gestion Pharmacie</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="notes_de_frais" name="applications[]" value="notes_de_frais">
                <label class="form-check-label" for="notes_de_frais">Notes de Frais</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="autre_application" name="applications[]" value="autre_application">
                <label class="form-check-label" for="autre_application">Autre Application</label>
            </div>
            <small class="form-text text-muted">Sélectionnez les applications auxquelles l'utilisateur aura accès.</small>
        </div>

        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="manage_users.php" class="btn btn-secondary">Retour</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
