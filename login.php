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

                // Rediriger vers la page demandée ou vers home.php par défaut
                $redirect_page = isset($_GET['redirect']) ? $_GET['redirect'] : 'home.php';
                $redirect_url = strpos($redirect_page, '/') === 0 ? $redirect_page : "/$redirect_page";

                // Ajouter le paramètre bag_id s'il est présent dans l'URL de la demande
                if (isset($_GET['bag_id'])) {
                    $redirect_url .= "?bag_id=" . urlencode($_GET['bag_id']);
                }

                header("Location: $redirect_url");
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
        /* Ajoutez ici votre style */
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo et formulaire de connexion -->
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
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
