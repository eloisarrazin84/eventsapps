<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Connexion à la base de données
    $servername = "localhost";
    $username_db = "root";  
    $password_db = "Lipton2019!";
    $dbname = "outdoorsec";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer l'utilisateur
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_approved']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirection conditionnelle
                if (isset($_SESSION['redirect_to'])) {
                    $redirectUrl = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']); // Supprimer la variable de session après redirection
                    header("Location: $redirectUrl");
                } else {
                    header("Location: home.php");
                }
                exit();
            } else {
                $error = "Votre compte n'a pas encore été validé par un administrateur.";
            }
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }

    } catch (PDOException $e) {
        $error = "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Connexion</title>
    <style>
        body {
            background-image: url('https://www.odsradio.com/media/news/haute-savoie-deux-morts-en-montagne-et-une-fuite-de-gaz_65f9df9a57367.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            backdrop-filter: blur(10px);
        }

        .login-container img {
            width: 80px;
            margin-bottom: 15px;
        }

        .error {
            color: #dc3545;
            margin-bottom: 15px;
        }

       .btn-block {
    margin-bottom: 15px; /* Adds space between buttons */
    padding: 10px;
}

.social-icons {
    margin: 20px 0; /* Adds space above and below the social icons */
}

.social-icons a {
    margin: 0 10px;
}

.btn-help {
    margin-top: 30px; /* Adds more space above the 'Besoin d'aide' link */
}

    </style>
</head>

<body>

    <div class="login-container">
        <!-- Logo -->
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1.png" alt="Logo Outdoor Secours">

        <h2 class="text-center">Connexion</h2>
        <p>Bienvenue chez Outdoor Secours. Connectez-vous pour accéder à vos événements et services.</p>

        <!-- Message d'erreur -->
        <?php if (!empty($error)): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>

        <!-- Bouton S'inscrire -->
        <div class="text-center">
            <a href="register.php" class="btn btn-success btn-block">S'inscrire</a>
        </div>

        <!-- Réseaux sociaux -->
        <div class="social-icons">
            <a href="#"><img src="https://cdn-icons-png.flaticon.com/256/124/124010.png" alt="Facebook"></a>
            <a href="#"><img src="https://cdn-icons-png.freepik.com/256/15707/15707869.png?semt=ais_hybrid" alt="Instagram"></a>
        </div>

        <!-- Bouton Aide -->
        <a href="contact.php" class="btn btn-link btn-help">Besoin d'aide ?</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
